<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Services\GradingService;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $teacherId = auth()->user()->teacher?->id;

        $results = ExamResult::whereHas('examSchedule.exam', fn ($q) => $q->where('teacher_id', $teacherId))
            ->when($request->schedule, fn ($q, $id) => $q->where('exam_schedule_id', $id))
            ->with(['student', 'examSchedule.exam.subject'])
            ->latest('submitted_at')->paginate(20)->withQueryString();

        $schedules = ExamSchedule::whereHas('exam', fn ($q) => $q->where('teacher_id', $teacherId))
            ->with('exam')->orderByDesc('start_time')->get();

        return view('guru.results.index', compact('results', 'schedules'));
    }

    public function show(ExamResult $result)
    {
        $this->authorizeResult($result);
        $result->load('student', 'examSchedule.exam', 'answers.question');

        return view('guru.results.show', compact('result'));
    }

    public function grade(Request $request, ExamResult $result)
    {
        $this->authorizeResult($result);

        $scores = $request->validate(['scores' => ['array']])['scores'] ?? [];

        foreach ($scores as $answerId => $score) {
            $answer = Answer::where('id', $answerId)->where('exam_result_id', $result->id)->first();
            if ($answer && $answer->question && ! $answer->question->isAutoGradable()) {
                $max = $answer->question->score;
                $answer->update([
                    'score' => min(max((float) $score, 0), $max),
                    'is_correct' => (float) $score > 0,
                    'graded' => true,
                ]);
            }
        }

        app(GradingService::class)->recalculate($result);

        return back()->with('success', 'Nilai essay berhasil disimpan.');
    }

    public function analysis(Exam $exam)
    {
        $this->authorizeExam($exam);

        $results = ExamResult::whereHas('examSchedule', fn ($q) => $q->where('exam_id', $exam->id))
            ->whereIn('status', ['submitted', 'graded'])
            ->with('answers.question')->get();

        $participants = $results->count();
        $avg = round($results->avg('total_score') ?? 0, 1);
        $passed = $results->where('is_passed', true)->count();
        $highest = $results->max('total_score') ?? 0;
        $lowest = $results->min('total_score') ?? 0;

        // Per-question item analysis
        $itemStats = [];
        $allAnswers = $results->flatMap->answers->groupBy('question_id');
        foreach ($allAnswers as $qid => $answers) {
            $question = $answers->first()->question;
            if (! $question) {
                continue;
            }
            $total = $answers->count();
            $correct = $answers->where('is_correct', true)->count();
            $pct = $total ? round($correct / $total * 100, 1) : 0;
            $itemStats[] = [
                'text' => \Illuminate\Support\Str::limit($question->question_text, 60),
                'type' => $question->type_label,
                'difficulty' => $question->difficulty,
                'correct_pct' => $pct,
                'level' => $pct >= 70 ? 'Mudah' : ($pct >= 40 ? 'Sedang' : 'Sulit'),
            ];
        }

        // Ranking
        $ranking = $results->sortByDesc('total_score')->values();

        return view('guru.results.analysis', compact('exam', 'participants', 'avg', 'passed', 'highest', 'lowest', 'itemStats', 'ranking'));
    }

    private function authorizeResult(ExamResult $result): void
    {
        abort_unless($result->examSchedule->exam->teacher_id === auth()->user()->teacher?->id, 403);
    }

    private function authorizeExam(Exam $exam): void
    {
        abort_unless($exam->teacher_id === auth()->user()->teacher?->id, 403);
    }
}

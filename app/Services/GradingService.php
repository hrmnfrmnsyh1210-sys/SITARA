<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\ExamResult;
use App\Models\Question;

class GradingService
{
    /**
     * Auto-grade every auto-gradable answer in a result, then (re)compute totals.
     */
    public function grade(ExamResult $result): ExamResult
    {
        $result->loadMissing('answers.question', 'examSchedule.exam');

        foreach ($result->answers as $answer) {
            if ($answer->question && $answer->question->isAutoGradable()) {
                $this->scoreAnswer($answer);
            }
        }

        return $this->recalculate($result);
    }

    /**
     * Score one auto-gradable answer in place (does not persist totals).
     */
    public function scoreAnswer(Answer $answer): void
    {
        $question = $answer->question;
        $given = $answer->answer; // array|null
        $correctAnswer = $question->correct_answer ?? [];
        $isCorrect = false;

        switch ($question->type) {
            case 'multiple_choice':
            case 'true_false':
                $isCorrect = ! empty($given) && in_array($given[0] ?? null, $correctAnswer, true);
                break;

            case 'short_answer':
                $normalized = strtolower(trim((string) ($given[0] ?? '')));
                $isCorrect = $normalized !== '' && in_array($normalized, array_map(
                    fn ($a) => strtolower(trim((string) $a)),
                    $correctAnswer
                ), true);
                break;

            case 'matching':
                // given is an ordered array of chosen "right" values
                $isCorrect = ! empty($given) && $given === $correctAnswer;
                break;
        }

        $answer->is_correct = $isCorrect;
        $answer->score = $isCorrect ? $question->score : 0;
        $answer->graded = true;
        $answer->save();
    }

    /**
     * Recompute the result totals from its answers + the exam's question set.
     */
    public function recalculate(ExamResult $result): ExamResult
    {
        $result->loadMissing('answers.question', 'examSchedule.exam');

        $questionIds = $result->question_order ?: $result->answers->pluck('question_id')->all();
        $possible = Question::whereIn('id', $questionIds)->sum('score');
        $possible = max((float) $possible, 0.0001);

        $earned = (float) $result->answers->sum(fn ($a) => (float) ($a->score ?? 0));

        $correct = $result->answers->where('is_correct', true)->count();
        $wrong = $result->answers->where('is_correct', false)->whereNotNull('answer')->count();
        $empty = max(count($questionIds) - $result->answers->whereNotNull('answer')->count(), 0);

        $score = round($earned / $possible * 100, 2);

        $hasUngraded = $result->answers->contains(fn ($a) => ! $a->graded && $a->question && ! $a->question->isAutoGradable());

        $passingScore = $result->examSchedule->exam->passing_score ?? 70;

        $result->update([
            'total_score' => $score,
            'correct_count' => $correct,
            'wrong_count' => $wrong,
            'empty_count' => $empty,
            'is_passed' => $score >= $passingScore,
            'status' => $hasUngraded ? 'submitted' : 'graded',
        ]);

        return $result;
    }
}

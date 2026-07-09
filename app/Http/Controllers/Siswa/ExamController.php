<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Models\Question;
use App\Services\GradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    public function index()
    {
        $student = $this->student();

        $schedules = ExamSchedule::where('classroom_id', $student?->classroom_id)
            ->where('is_active', true)
            ->whereHas('exam', fn ($q) => $q->where('status', 'published'))
            ->with(['exam.subject', 'room'])
            ->orderByDesc('start_time')->get();

        $resultsBySchedule = ExamResult::where('student_id', $student?->id)
            ->pluck('status', 'exam_schedule_id');

        return view('siswa.exams.index', compact('schedules', 'resultsBySchedule'));
    }

    public function confirm(ExamSchedule $schedule)
    {
        $student = $this->student();
        $this->guardAccess($schedule, $student);

        $result = ExamResult::where('exam_schedule_id', $schedule->id)
            ->where('student_id', $student->id)->first();

        if ($result && in_array($result->status, ['submitted', 'graded'])) {
            return redirect()->route('siswa.exams.index')->with('swal', [
                'icon' => 'info',
                'title' => 'Ujian Sudah Selesai',
                'text' => 'Anda sudah menyelesaikan ujian ini. Silakan lihat hasilnya pada menu Hasil.',
            ]);
        }

        $schedule->load('exam.examPackage');
        $questionCount = $schedule->exam->examPackage->questions()->count();

        return view('siswa.exams.confirm', compact('schedule', 'questionCount', 'result'));
    }

    public function start(Request $request, ExamSchedule $schedule)
    {
        $student = $this->student();
        $this->guardAccess($schedule, $student);

        $exam = $schedule->exam()->with('examPackage.questions')->first();

        $existing = ExamResult::where('exam_schedule_id', $schedule->id)
            ->where('student_id', $student->id)->first();

        // Lokasi hanya diminta sekali: saat attempt pertama, atau saat melanjutkan
        // attempt lama yang belum sempat merekam lokasi.
        $location = null;
        if ($this->needsLocation($schedule, $existing)) {
            $location = $this->validateLocation($request);

            // Tanpa lokasi yang valid, ujian tidak boleh dimulai.
            if (! $location) {
                return redirect()->route('siswa.exams.confirm', $schedule)->with('swal', [
                    'icon' => 'warning',
                    'title' => 'Lokasi Harus Dikirim',
                    'text' => 'Anda wajib mengirimkan lokasi terlebih dahulu sebelum memulai ujian ini. Aktifkan GPS dan izinkan akses lokasi pada browser Anda.',
                ]);
            }
        }

        DB::transaction(function () use ($schedule, $student, $exam, $location) {
            $result = ExamResult::firstOrNew([
                'exam_schedule_id' => $schedule->id,
                'student_id' => $student->id,
            ]);

            if (! $result->exists) {
                $questionIds = $exam->examPackage->questions->pluck('id')->all();
                if ($exam->randomize_questions) {
                    shuffle($questionIds);
                }
                $result->fill([
                    'started_at' => now(),
                    'status' => 'in_progress',
                    'question_order' => $questionIds,
                    'remaining_seconds' => $exam->duration_minutes * 60,
                    'ip_address' => request()->ip(),
                ]);
            }

            if ($location) {
                $result->fill($location + ['location_captured_at' => now()]);
            }

            $result->save();
        });

        return redirect()->route('siswa.exams.take', $schedule);
    }

    public function take(ExamSchedule $schedule)
    {
        $student = $this->student();
        $this->guardAccess($schedule, $student);

        $result = ExamResult::where('exam_schedule_id', $schedule->id)
            ->where('student_id', $student->id)->firstOrFail();

        if (in_array($result->status, ['submitted', 'graded'])) {
            return redirect()->route('siswa.results.show', $result);
        }

        // Attempt lama yang belum punya lokasi (mis. guru baru mengaktifkan syarat ini
        // setelah ujian dimulai) dipaksa kembali ke halaman konfirmasi.
        if ($this->needsLocation($schedule, $result)) {
            return redirect()->route('siswa.exams.confirm', $schedule)->with('swal', [
                'icon' => 'warning',
                'title' => 'Lokasi Belum Dikirim',
                'text' => 'Ujian ini mewajibkan pengiriman lokasi. Kirim lokasi Anda terlebih dahulu untuk melanjutkan.',
            ]);
        }

        $exam = $schedule->exam;
        $questions = Question::whereIn('id', $result->question_order ?? [])
            ->get()->keyBy('id');

        // Preserve the randomized order stored for this student.
        $ordered = collect($result->question_order)->map(fn ($id) => $questions->get($id))->filter()->values();

        // Optionally shuffle options deterministically per result+question.
        if ($exam->randomize_options) {
            $ordered->each(function ($q) use ($result) {
                if (in_array($q->type, ['multiple_choice']) && $q->options) {
                    $opts = $q->options;
                    mt_srand($result->id * 1000 + $q->id);
                    shuffle($opts);
                    mt_srand();
                    $q->setAttribute('options', $opts);
                }
            });
        }

        $answers = $result->answers()->get()->keyBy('question_id');

        // Compute live remaining time based on started_at + duration.
        // Carbon 3's diffInSeconds() returns a float — cast to int so the timer never shows fractional seconds.
        $elapsed = (int) now()->diffInSeconds($result->started_at);
        $remaining = max(($exam->duration_minutes * 60) - $elapsed, 0);

        if ($remaining <= 0) {
            return $this->finalize($result);
        }

        return view('siswa.exams.take', compact('schedule', 'exam', 'result', 'ordered', 'answers', 'remaining'));
    }

    public function saveAnswer(Request $request, ExamSchedule $schedule)
    {
        $student = $this->student();
        $result = ExamResult::where('exam_schedule_id', $schedule->id)
            ->where('student_id', $student->id)->firstOrFail();

        abort_if($result->status !== 'in_progress', 403);

        $data = $request->validate([
            'question_id' => ['required', 'exists:questions,id'],
            'answer' => ['nullable'],
            'is_flagged' => ['nullable', 'boolean'],
        ]);

        $answer = Answer::firstOrNew([
            'exam_result_id' => $result->id,
            'question_id' => $data['question_id'],
        ]);

        if ($request->has('answer')) {
            $value = $data['answer'];
            $answer->answer = is_array($value) ? array_values($value) : [$value];
        }
        if ($request->has('is_flagged')) {
            $answer->is_flagged = $request->boolean('is_flagged');
        }
        $answer->save();

        // Persist remaining time so a refresh/reconnect resumes correctly.
        if ($request->filled('remaining_seconds')) {
            $result->update(['remaining_seconds' => (int) $request->remaining_seconds]);
        }

        return response()->json(['ok' => true, 'answered' => $answer->answer !== null, 'flagged' => $answer->is_flagged]);
    }

    public function recordViolation(ExamSchedule $schedule)
    {
        $student = $this->student();
        $result = ExamResult::where('exam_schedule_id', $schedule->id)
            ->where('student_id', $student->id)->firstOrFail();

        // Only count while the exam is still running.
        if ($result->status === 'in_progress') {
            $result->increment('violation_count');
        }

        return response()->json(['ok' => true, 'violations' => $result->violation_count]);
    }

    public function submit(ExamSchedule $schedule)
    {
        $student = $this->student();
        $result = ExamResult::where('exam_schedule_id', $schedule->id)
            ->where('student_id', $student->id)->firstOrFail();

        return $this->finalize($result);
    }

    private function finalize(ExamResult $result)
    {
        if ($result->status === 'in_progress') {
            $result->update(['submitted_at' => now(), 'status' => 'submitted']);
            app(GradingService::class)->grade($result);
        }

        return redirect()->route('siswa.results.show', $result)->with('success', 'Ujian berhasil dikumpulkan.');
    }

    private function student()
    {
        return auth()->user()->student;
    }

    /** Jadwal mewajibkan lokasi dan attempt ini belum merekamnya. */
    private function needsLocation(ExamSchedule $schedule, ?ExamResult $result): bool
    {
        return $schedule->requires_location && ! ($result?->hasLocation() ?? false);
    }

    /**
     * Koordinat datang dari browser siswa, jadi tidak bisa dipercaya sepenuhnya —
     * yang dijamin di sini hanya bentuknya valid dan benar-benar terkirim.
     * Mengembalikan null kalau lokasi tidak ada / tidak valid.
     */
    private function validateLocation(Request $request): ?array
    {
        $validator = validator($request->all(), [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'location_accuracy' => ['nullable', 'numeric', 'min:0', 'max:100000'],
        ]);

        if ($validator->fails()) {
            return null;
        }

        $data = $validator->validated();

        return [
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'location_accuracy' => isset($data['location_accuracy']) ? (int) round($data['location_accuracy']) : null,
        ];
    }

    private function guardAccess(ExamSchedule $schedule, $student): void
    {
        abort_unless($student, 403, 'Akun belum terhubung ke data siswa.');
        abort_unless($schedule->classroom_id === $student->classroom_id, 403, 'Ujian ini bukan untuk kelas Anda.');
        abort_unless($schedule->is_active && now()->between($schedule->start_time, $schedule->end_time), 403, 'Ujian belum dibuka atau sudah ditutup.');
    }
}

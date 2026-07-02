<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Concerns\ExportsReports;
use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Services\GradingService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ResultController extends Controller
{
    use ExportsReports;

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

        $data = $request->validate([
            'scores' => ['array'],
            'pass_override' => ['nullable', 'in:auto,pass,fail'],
        ]);
        $scores = $data['scores'] ?? [];

        // Manual pass/fail decision (only offered when the student has violations).
        if ($request->has('pass_override')) {
            $result->pass_override = match ($request->input('pass_override')) {
                'pass' => true,
                'fail' => false,
                default => null,   // 'auto' → follow the score
            };
            $result->save();
        }

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

        return view('guru.results.analysis', $this->analysisData($exam));
    }

    public function analysisExcel(Exam $exam)
    {
        $this->authorizeExam($exam);
        $data = $this->analysisData($exam);
        $school = $exam->school ?? auth()->user()->school;

        $ss = $this->newSpreadsheet('Analisis Ujian');
        $sheet = $ss->getActiveSheet();

        $meta = [
            'Ujian: ' . $exam->title,
            'Mata Pelajaran: ' . ($exam->subject->name ?? '-'),
            'Peserta: ' . $data['participants'] . ' · Rata-rata: ' . $data['avg'] . ' · Lulus: ' . $data['passed'],
            'Dicetak: ' . now()->translatedFormat('d F Y H:i'),
        ];
        $start = $this->writeSheetHeading($sheet, $school, 'ANALISIS HASIL UJIAN', $meta, 4);

        // Bagian peringkat
        $sheet->setCellValue("A{$start}", 'PERINGKAT SISWA');
        $sheet->getStyle("A{$start}")->getFont()->setBold(true);
        $start++;
        $rankHead = ['Peringkat', 'Nama Siswa', 'Nilai', 'Status'];
        $sheet->fromArray($rankHead, null, "A{$start}");
        $this->styleHeaderRow($sheet, "A{$start}:D{$start}");
        $rankStart = $start;
        $row = $start + 1;
        foreach ($data['ranking'] as $i => $r) {
            $sheet->fromArray([
                $i + 1,
                $r->student->name ?? '-',
                (float) $r->total_score,
                $r->is_passed ? 'Lulus' : 'Tidak Lulus',
            ], null, "A{$row}");
            $row++;
        }
        if ($data['ranking']->isNotEmpty()) {
            $this->borderRange($sheet, "A{$rankStart}:D" . ($row - 1));
        }

        // Bagian analisis butir soal
        $row += 1;
        $sheet->setCellValue("A{$row}", 'ANALISIS BUTIR SOAL');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;
        $itemHead = ['No', 'Butir Soal', 'Tipe', '% Benar', 'Tingkat Kesukaran'];
        $sheet->fromArray($itemHead, null, "A{$row}");
        $this->styleHeaderRow($sheet, "A{$row}:E{$row}");
        $itemStart = $row;
        $row++;
        foreach ($data['itemStats'] as $i => $stat) {
            $sheet->fromArray([
                $i + 1,
                $stat['text'],
                $stat['type'],
                $stat['correct_pct'] . '%',
                $stat['level'],
            ], null, "A{$row}");
            $row++;
        }
        if (! empty($data['itemStats'])) {
            $this->borderRange($sheet, "A{$itemStart}:E" . ($row - 1));
        }

        $this->autoSizeColumns($sheet, 5);
        $sheet->getColumnDimension('B')->setAutoSize(false)->setWidth(55);

        return $this->xlsxDownload($ss, 'analisis-' . \Illuminate\Support\Str::slug($exam->title));
    }

    public function analysisPdf(Exam $exam)
    {
        $this->authorizeExam($exam);
        $data = $this->analysisData($exam);
        $school = $exam->school ?? auth()->user()->school;

        return $this->pdfDownload('reports.pdf.analysis', array_merge($data, [
            'school' => $school,
            'logoPath' => $this->schoolLogoPath($school),
            'title' => 'Analisis Hasil Ujian',
            'meta' => [
                'Ujian' => $exam->title,
                'Mata Pelajaran' => $exam->subject->name ?? '-',
                'Tanggal Cetak' => now()->translatedFormat('d F Y'),
            ],
        ]), 'analisis-' . \Illuminate\Support\Str::slug($exam->title), 'portrait');
    }

    /**
     * Hitung ringkasan, peringkat, dan analisis butir soal sebuah ujian.
     */
    private function analysisData(Exam $exam): array
    {
        $results = ExamResult::whereHas('examSchedule', fn ($q) => $q->where('exam_id', $exam->id))
            ->whereIn('status', ['submitted', 'graded'])
            ->with(['answers.question', 'student'])->get();

        $participants = $results->count();
        $avg = round($results->avg('total_score') ?? 0, 1);
        $passed = $results->where('is_passed', true)->count();
        $highest = $results->max('total_score') ?? 0;
        $lowest = $results->min('total_score') ?? 0;

        // Per-question item analysis
        $itemStats = [];
        $allAnswers = $results->flatMap->answers->groupBy('question_id');
        foreach ($allAnswers as $answers) {
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

        $ranking = $results->sortByDesc('total_score')->values();

        return compact('exam', 'participants', 'avg', 'passed', 'highest', 'lowest', 'itemStats', 'ranking');
    }

    // ================================================================
    // Daftar Nilai per jadwal (Excel & PDF)
    // ================================================================

    public function scoresExcel(Request $request)
    {
        [$results, $schedule, $meta] = $this->scoresExport($request);
        $school = auth()->user()->school;

        $ss = $this->newSpreadsheet('Daftar Nilai');
        $sheet = $ss->getActiveSheet();

        $headers = ['No', 'NIS', 'Nama Siswa', 'Ujian', 'Dikumpulkan', 'Nilai', 'Benar', 'Salah', 'Kosong', 'Pelanggaran', 'Status'];
        $colCount = count($headers);
        $start = $this->writeSheetHeading($sheet, $school, 'DAFTAR NILAI UJIAN', $meta, $colCount);

        $sheet->fromArray($headers, null, "A{$start}");
        $this->styleHeaderRow($sheet, "A{$start}:" . Coordinate::stringFromColumnIndex($colCount) . $start);

        $row = $start + 1;
        foreach ($results as $i => $r) {
            $sheet->fromArray([
                $i + 1,
                $r->student->nis ?? '-',
                $r->student->name ?? '-',
                $r->examSchedule->exam->title ?? '-',
                $r->submitted_at?->format('d-m-Y H:i') ?? '-',
                (float) $r->total_score,
                $r->correct_count,
                $r->wrong_count,
                $r->empty_count,
                $r->violation_count ?? 0,
                $r->status === 'graded' ? 'Selesai' : 'Perlu Koreksi',
            ], null, "A{$row}");
            $row++;
        }

        $lastCol = Coordinate::stringFromColumnIndex($colCount);
        if ($results->isNotEmpty()) {
            $this->borderRange($sheet, "A{$start}:{$lastCol}" . ($row - 1));
        }
        $this->autoSizeColumns($sheet, $colCount);

        return $this->xlsxDownload($ss, 'daftar-nilai');
    }

    public function scoresPdf(Request $request)
    {
        [$results, $schedule, $meta] = $this->scoresExport($request);
        $school = auth()->user()->school;

        return $this->pdfDownload('reports.pdf.exam-scores', [
            'school' => $school,
            'logoPath' => $this->schoolLogoPath($school),
            'title' => 'Daftar Nilai Ujian',
            'meta' => collect($meta)->mapWithKeys(fn ($v) => [\Illuminate\Support\Str::before($v, ':') => trim(\Illuminate\Support\Str::after($v, ':'))])->all(),
            'results' => $results,
            'schedule' => $schedule,
        ], 'daftar-nilai', 'landscape');
    }

    /**
     * Ambil hasil untuk ekspor daftar nilai, mengikuti filter jadwal pada halaman hasil.
     *
     * @return array{0: \Illuminate\Support\Collection, 1: ?ExamSchedule, 2: list<string>}
     */
    private function scoresExport(Request $request): array
    {
        $teacherId = auth()->user()->teacher?->id;

        $results = ExamResult::whereHas('examSchedule.exam', fn ($q) => $q->where('teacher_id', $teacherId))
            ->when($request->schedule, fn ($q, $id) => $q->where('exam_schedule_id', $id))
            ->with(['student', 'examSchedule.exam.subject', 'examSchedule.classroom'])
            ->orderByDesc('total_score')->get();

        $schedule = $request->schedule
            ? ExamSchedule::with(['exam.subject', 'classroom'])->find($request->schedule)
            : null;

        if ($schedule) {
            abort_unless($schedule->exam->teacher_id === $teacherId, 403);
        }

        $meta = [];
        if ($schedule) {
            $meta[] = 'Ujian: ' . ($schedule->exam->title ?? '-');
            $meta[] = 'Mata Pelajaran: ' . ($schedule->exam->subject->name ?? '-');
            $meta[] = 'Kelas: ' . ($schedule->classroom->name ?? '-');
        } else {
            $meta[] = 'Cakupan: Semua Jadwal';
        }
        $meta[] = 'Dicetak: ' . now()->translatedFormat('d F Y H:i');

        return [$results, $schedule, $meta];
    }

    // ================================================================
    // Berita Acara / Daftar Hadir Ujian (PDF)
    // ================================================================

    public function attendancePdf(ExamSchedule $schedule)
    {
        $teacherId = auth()->user()->teacher?->id;
        $schedule->load(['exam.subject', 'exam.teacher', 'classroom', 'room', 'results']);
        abort_unless($schedule->exam->teacher_id === $teacherId, 403);

        $resultsByStudent = $schedule->results->keyBy('student_id');
        $students = $schedule->classroom
            ? \App\Models\Student::where('classroom_id', $schedule->classroom_id)->orderBy('name')->get()
            : collect();

        $school = auth()->user()->school;

        return $this->pdfDownload('reports.pdf.attendance', [
            'school' => $school,
            'logoPath' => $this->schoolLogoPath($school),
            'title' => 'Daftar Hadir & Berita Acara Ujian',
            'meta' => [
                'Ujian' => $schedule->exam->title ?? '-',
                'Mata Pelajaran' => $schedule->exam->subject->name ?? '-',
                'Kelas' => $schedule->classroom->name ?? '-',
                'Ruang' => $schedule->room->name ?? '-',
                'Waktu' => $schedule->start_time->translatedFormat('d F Y, H:i') . ' — ' . $schedule->end_time->format('H:i'),
            ],
            'schedule' => $schedule,
            'students' => $students,
            'resultsByStudent' => $resultsByStudent,
        ], 'daftar-hadir-' . \Illuminate\Support\Str::slug($schedule->exam->title ?? 'ujian'), 'portrait');
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

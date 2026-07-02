<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Concerns\ExportsReports;
use App\Http\Controllers\Controller;
use App\Models\ExamResult;

class ResultController extends Controller
{
    use ExportsReports;

    public function index()
    {
        $student = auth()->user()->student;

        $results = ExamResult::where('student_id', $student?->id)
            ->whereIn('status', ['submitted', 'graded'])
            ->with('examSchedule.exam.subject')
            ->latest('submitted_at')->paginate(12);

        return view('siswa.results.index', compact('results'));
    }

    public function show(ExamResult $result)
    {
        abort_unless($result->student_id === auth()->user()->student?->id, 403);

        $result->load('examSchedule.exam.subject', 'answers.question');

        return view('siswa.results.show', compact('result'));
    }

    /**
     * Unduh kartu hasil ujian (PDF). Hanya bila nilai sudah dipublikasikan.
     */
    public function pdf(ExamResult $result)
    {
        $student = auth()->user()->student;
        abort_unless($result->student_id === $student?->id, 403);

        $result->load('examSchedule.exam.subject', 'student.classroom');

        $exam = $result->examSchedule->exam;
        abort_unless($exam->show_result && $result->status === 'graded', 403, 'Nilai belum dipublikasikan.');

        $school = auth()->user()->school;

        return $this->pdfDownload('reports.pdf.result-card', [
            'school' => $school,
            'logoPath' => $this->schoolLogoPath($school),
            'title' => 'Kartu Hasil Ujian',
            'result' => $result,
            'student' => $result->student,
            'exam' => $exam,
        ], 'kartu-hasil-' . \Illuminate\Support\Str::slug($exam->title), 'portrait');
    }
}

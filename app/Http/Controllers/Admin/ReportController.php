<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ExportsReports;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ReportController extends Controller
{
    use ExportsReports;

    // ================================================================
    // Rekap Nilai Siswa
    // ================================================================

    public function scores(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $results = $this->scoresQuery($request, $schoolId)
            ->with(['student.classroom', 'examSchedule.exam.subject'])
            ->latest('submitted_at')
            ->paginate(20)->withQueryString();

        [$classrooms, $exams] = $this->scoreFilters($schoolId);

        return view('admin.reports.scores', compact('results', 'classrooms', 'exams'));
    }

    public function scoresExcel(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $school = auth()->user()->school;
        $results = $this->scoresQuery($request, $schoolId)
            ->with(['student.classroom', 'examSchedule.exam.subject'])
            ->latest('submitted_at')->get();

        $ss = $this->newSpreadsheet('Rekap Nilai');
        $sheet = $ss->getActiveSheet();

        $headers = ['No', 'NIS', 'Nama Siswa', 'Kelas', 'Ujian', 'Mata Pelajaran', 'Nilai', 'Benar', 'Salah', 'Kosong', 'Status'];
        $colCount = count($headers);
        $start = $this->writeSheetHeading($sheet, $school, 'REKAP NILAI SISWA', $this->scoreMeta($request, $schoolId), $colCount);

        $sheet->fromArray($headers, null, "A{$start}");
        $this->styleHeaderRow($sheet, "A{$start}:" . Coordinate::stringFromColumnIndex($colCount) . $start);

        $row = $start + 1;
        foreach ($results as $i => $r) {
            $sheet->fromArray([
                $i + 1,
                $r->student->nis ?? '-',
                $r->student->name ?? '-',
                $r->student->classroom->name ?? '-',
                $r->examSchedule->exam->title ?? '-',
                $r->examSchedule->exam->subject->name ?? '-',
                (float) $r->total_score,
                $r->correct_count,
                $r->wrong_count,
                $r->empty_count,
                $r->is_passed ? 'Lulus' : 'Tidak Lulus',
            ], null, "A{$row}");
            $row++;
        }

        $lastCol = Coordinate::stringFromColumnIndex($colCount);
        if ($results->isNotEmpty()) {
            $this->borderRange($sheet, "A{$start}:{$lastCol}" . ($row - 1));
        }
        $this->autoSizeColumns($sheet, $colCount);

        return $this->xlsxDownload($ss, 'rekap-nilai-siswa');
    }

    public function scoresPdf(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $school = auth()->user()->school;
        $results = $this->scoresQuery($request, $schoolId)
            ->with(['student.classroom', 'examSchedule.exam.subject'])
            ->latest('submitted_at')->get();

        return $this->pdfDownload('reports.pdf.scores', [
            'school' => $school,
            'logoPath' => $this->schoolLogoPath($school),
            'title' => 'Rekap Nilai Siswa',
            'meta' => $this->scoreMetaPairs($request, $schoolId),
            'results' => $results,
        ], 'rekap-nilai-siswa', 'landscape');
    }

    private function scoresQuery(Request $request, int $schoolId): Builder
    {
        return ExamResult::query()
            ->whereHas('examSchedule.exam', fn ($q) => $q->where('school_id', $schoolId))
            ->whereIn('status', ['submitted', 'graded'])
            ->when($request->classroom_id, fn ($q, $id) => $q->whereHas('student', fn ($s) => $s->where('classroom_id', $id)))
            ->when($request->exam_id, fn ($q, $id) => $q->whereHas('examSchedule', fn ($s) => $s->where('exam_id', $id)));
    }

    /** @return array{0: \Illuminate\Support\Collection, 1: \Illuminate\Support\Collection} */
    private function scoreFilters(int $schoolId): array
    {
        return [
            Classroom::where('school_id', $schoolId)->orderBy('name')->get(),
            Exam::where('school_id', $schoolId)->orderBy('title')->get(),
        ];
    }

    /** @return list<string> */
    private function scoreMeta(Request $request, int $schoolId): array
    {
        $lines = [];
        if ($request->classroom_id) {
            $lines[] = 'Kelas: ' . (Classroom::find($request->classroom_id)?->name ?? '-');
        }
        if ($request->exam_id) {
            $lines[] = 'Ujian: ' . (Exam::find($request->exam_id)?->title ?? '-');
        }
        $lines[] = 'Dicetak: ' . now()->translatedFormat('d F Y H:i');

        return $lines;
    }

    /** @return array<string, string> */
    private function scoreMetaPairs(Request $request, int $schoolId): array
    {
        $meta = [];
        $meta['Kelas'] = $request->classroom_id ? (Classroom::find($request->classroom_id)?->name ?? '-') : 'Semua Kelas';
        $meta['Ujian'] = $request->exam_id ? (Exam::find($request->exam_id)?->title ?? '-') : 'Semua Ujian';
        $meta['Tanggal Cetak'] = now()->translatedFormat('d F Y');

        return $meta;
    }

    // ================================================================
    // Data Siswa
    // ================================================================

    public function studentsExcel(Request $request)
    {
        $school = auth()->user()->school;
        $students = $this->studentsQuery($request)->get();

        $ss = $this->newSpreadsheet('Data Siswa');
        $sheet = $ss->getActiveSheet();

        $headers = ['No', 'NIS', 'NISN', 'Nama', 'JK', 'Tempat Lahir', 'Tanggal Lahir', 'Kelas', 'No. HP', 'Alamat'];
        $colCount = count($headers);
        $meta = ['Kelas: ' . ($request->classroom_id ? (Classroom::find($request->classroom_id)?->name ?? '-') : 'Semua Kelas'), 'Dicetak: ' . now()->translatedFormat('d F Y H:i')];
        $start = $this->writeSheetHeading($sheet, $school, 'DATA SISWA', $meta, $colCount);

        $sheet->fromArray($headers, null, "A{$start}");
        $this->styleHeaderRow($sheet, "A{$start}:" . Coordinate::stringFromColumnIndex($colCount) . $start);

        $row = $start + 1;
        foreach ($students as $i => $s) {
            $sheet->fromArray([
                $i + 1,
                $s->nis,
                $s->nisn ?? '-',
                $s->name,
                $s->gender ?? '-',
                $s->birth_place ?? '-',
                $s->birth_date?->format('d-m-Y') ?? '-',
                $s->classroom->name ?? '-',
                $s->phone ?? '-',
                $s->address ?? '-',
            ], null, "A{$row}");
            $sheet->getCell("B{$row}")->setValueExplicit((string) $s->nis, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $row++;
        }

        $lastCol = Coordinate::stringFromColumnIndex($colCount);
        if ($students->isNotEmpty()) {
            $this->borderRange($sheet, "A{$start}:{$lastCol}" . ($row - 1));
        }
        $this->autoSizeColumns($sheet, $colCount);

        return $this->xlsxDownload($ss, 'data-siswa');
    }

    public function studentsPdf(Request $request)
    {
        $school = auth()->user()->school;
        $students = $this->studentsQuery($request)->get();

        return $this->pdfDownload('reports.pdf.students', [
            'school' => $school,
            'logoPath' => $this->schoolLogoPath($school),
            'title' => 'Data Siswa',
            'meta' => [
                'Kelas' => $request->classroom_id ? (Classroom::find($request->classroom_id)?->name ?? '-') : 'Semua Kelas',
                'Jumlah' => $students->count() . ' siswa',
                'Tanggal Cetak' => now()->translatedFormat('d F Y'),
            ],
            'students' => $students,
        ], 'data-siswa', 'portrait');
    }

    private function studentsQuery(Request $request): Builder
    {
        $schoolId = auth()->user()->school_id;

        return Student::where('school_id', $schoolId)
            ->with('classroom')
            ->when($request->search, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('nis', 'like', "%$s%")))
            ->when($request->classroom_id, fn ($q, $id) => $q->where('classroom_id', $id))
            ->orderBy('classroom_id')->orderBy('name');
    }

    // ================================================================
    // Data Guru
    // ================================================================

    public function teachersExcel(Request $request)
    {
        $school = auth()->user()->school;
        $teachers = $this->teachersQuery($request)->get();

        $ss = $this->newSpreadsheet('Data Guru');
        $sheet = $ss->getActiveSheet();

        $headers = ['No', 'NIP', 'Nama', 'JK', 'Email', 'No. HP', 'Alamat'];
        $colCount = count($headers);
        $start = $this->writeSheetHeading($sheet, $school, 'DATA GURU', ['Dicetak: ' . now()->translatedFormat('d F Y H:i')], $colCount);

        $sheet->fromArray($headers, null, "A{$start}");
        $this->styleHeaderRow($sheet, "A{$start}:" . Coordinate::stringFromColumnIndex($colCount) . $start);

        $row = $start + 1;
        foreach ($teachers as $i => $t) {
            $sheet->fromArray([
                $i + 1,
                $t->nip ?? '-',
                $t->name,
                $t->gender ?? '-',
                $t->user->email ?? '-',
                $t->phone ?? '-',
                $t->address ?? '-',
            ], null, "A{$row}");
            $row++;
        }

        $lastCol = Coordinate::stringFromColumnIndex($colCount);
        if ($teachers->isNotEmpty()) {
            $this->borderRange($sheet, "A{$start}:{$lastCol}" . ($row - 1));
        }
        $this->autoSizeColumns($sheet, $colCount);

        return $this->xlsxDownload($ss, 'data-guru');
    }

    public function teachersPdf(Request $request)
    {
        $school = auth()->user()->school;
        $teachers = $this->teachersQuery($request)->get();

        return $this->pdfDownload('reports.pdf.teachers', [
            'school' => $school,
            'logoPath' => $this->schoolLogoPath($school),
            'title' => 'Data Guru',
            'meta' => [
                'Jumlah' => $teachers->count() . ' guru',
                'Tanggal Cetak' => now()->translatedFormat('d F Y'),
            ],
            'teachers' => $teachers,
        ], 'data-guru', 'portrait');
    }

    private function teachersQuery(Request $request): Builder
    {
        $schoolId = auth()->user()->school_id;

        return Teacher::where('school_id', $schoolId)
            ->with('user')
            ->when($request->search, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('nip', 'like', "%$s%")))
            ->orderBy('name');
    }
}

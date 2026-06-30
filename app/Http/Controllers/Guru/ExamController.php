<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamPackage;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $exams = Exam::where('teacher_id', auth()->user()->teacher?->id)
            ->with(['subject', 'examPackage'])->withCount('schedules')
            ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%$s%"))
            ->latest()->paginate(10)->withQueryString();

        return view('guru.exams.index', compact('exams'));
    }

    public function create()
    {
        return view('guru.exams.form', ['exam' => new Exam] + $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $package = ExamPackage::findOrFail($data['exam_package_id']);

        Exam::create($data + [
            'school_id' => auth()->user()->school_id,
            'teacher_id' => auth()->user()->teacher?->id,
            'subject_id' => $package->subject_id,
        ]);

        return redirect()->route('guru.exams.index')->with('success', 'Ujian dibuat.');
    }

    public function edit(Exam $exam)
    {
        $this->authorizeExam($exam);

        return view('guru.exams.form', ['exam' => $exam] + $this->formData());
    }

    public function update(Request $request, Exam $exam)
    {
        $this->authorizeExam($exam);
        $data = $this->validateData($request);
        $package = ExamPackage::findOrFail($data['exam_package_id']);
        $exam->update($data + ['subject_id' => $package->subject_id]);

        return redirect()->route('guru.exams.index')->with('success', 'Ujian diperbarui.');
    }

    public function destroy(Exam $exam)
    {
        $this->authorizeExam($exam);
        $exam->delete();

        return back()->with('success', 'Ujian dihapus.');
    }

    private function formData(): array
    {
        return [
            'packages' => ExamPackage::where('teacher_id', auth()->user()->teacher?->id)->withCount('questions')->get(),
            'academicYears' => AcademicYear::where('school_id', auth()->user()->school_id)->with('semesters')->orderByDesc('name')->get(),
        ];
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'exam_package_id' => ['required', 'exists:exam_packages,id'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'semester_id' => ['nullable', 'exists:semesters,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'passing_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'in:draft,published,closed'],
            'randomize_questions' => ['nullable'],
            'randomize_options' => ['nullable'],
            'show_result' => ['nullable'],
        ]) + [
            'randomize_questions' => $request->boolean('randomize_questions'),
            'randomize_options' => $request->boolean('randomize_options'),
            'show_result' => $request->boolean('show_result'),
        ];
    }

    private function authorizeExam(Exam $exam): void
    {
        abort_unless($exam->teacher_id === auth()->user()->teacher?->id, 403);
    }
}

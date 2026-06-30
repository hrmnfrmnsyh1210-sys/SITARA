<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\QuestionBank;
use App\Models\Subject;
use Illuminate\Http\Request;

class QuestionBankController extends Controller
{
    public function index(Request $request)
    {
        $teacher = auth()->user()->teacher;

        $banks = QuestionBank::where('teacher_id', $teacher?->id)
            ->with('subject')->withCount('questions')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%$s%"))
            ->latest()->paginate(10)->withQueryString();

        return view('guru.question_banks.index', compact('banks'));
    }

    public function create()
    {
        return view('guru.question_banks.form', ['bank' => new QuestionBank, 'subjects' => $this->subjects()]);
    }

    public function store(Request $request)
    {
        QuestionBank::create($this->validateData($request) + [
            'school_id' => auth()->user()->school_id,
            'teacher_id' => auth()->user()->teacher?->id,
        ]);

        return redirect()->route('guru.question-banks.index')->with('success', 'Bank soal dibuat.');
    }

    public function show(QuestionBank $questionBank)
    {
        // Redirect to questions list (nested resource handles display)
        return redirect()->route('guru.question-banks.questions.index', $questionBank);
    }

    public function edit(QuestionBank $questionBank)
    {
        $this->authorize($questionBank);

        return view('guru.question_banks.form', ['bank' => $questionBank, 'subjects' => $this->subjects()]);
    }

    public function update(Request $request, QuestionBank $questionBank)
    {
        $this->authorize($questionBank);
        $questionBank->update($this->validateData($request));

        return redirect()->route('guru.question-banks.index')->with('success', 'Bank soal diperbarui.');
    }

    public function destroy(QuestionBank $questionBank)
    {
        $this->authorize($questionBank);
        $questionBank->delete();

        return back()->with('success', 'Bank soal dihapus.');
    }

    private function subjects()
    {
        return Subject::where('school_id', auth()->user()->school_id)->orderBy('name')->get();
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
    }

    private function authorize(QuestionBank $bank): void
    {
        abort_unless($bank->teacher_id === auth()->user()->teacher?->id, 403);
    }
}

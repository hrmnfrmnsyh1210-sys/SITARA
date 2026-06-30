<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $subjects = Subject::where('school_id', auth()->user()->school_id)
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('code', 'like', "%$s%"))
            ->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('admin.subjects.form', ['subject' => new Subject]);
    }

    public function store(Request $request)
    {
        Subject::create($this->validateData($request) + ['school_id' => auth()->user()->school_id]);

        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran ditambahkan.');
    }

    public function edit(Subject $subject)
    {
        $this->authorizeSchool($subject);

        return view('admin.subjects.form', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $this->authorizeSchool($subject);
        $subject->update($this->validateData($request));

        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran diperbarui.');
    }

    public function destroy(Subject $subject)
    {
        $this->authorizeSchool($subject);
        $subject->delete();

        return back()->with('success', 'Mata pelajaran dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:30'],
            'color' => ['nullable', 'string', 'max:9'],
        ]);
    }

    private function authorizeSchool(Subject $subject): void
    {
        abort_unless($subject->school_id === auth()->user()->school_id, 403);
    }
}

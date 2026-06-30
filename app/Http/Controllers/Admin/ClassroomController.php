<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Major;
use App\Models\Teacher;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index(Request $request)
    {
        $classrooms = Classroom::where('school_id', auth()->user()->school_id)
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%$s%"))
            ->with(['major', 'homeroomTeacher'])->withCount('students')
            ->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.classrooms.index', compact('classrooms'));
    }

    public function create()
    {
        return view('admin.classrooms.form', $this->formData(new Classroom));
    }

    public function store(Request $request)
    {
        Classroom::create($this->validateData($request) + ['school_id' => auth()->user()->school_id]);

        return redirect()->route('admin.classrooms.index')->with('success', 'Kelas ditambahkan.');
    }

    public function edit(Classroom $classroom)
    {
        abort_unless($classroom->school_id === auth()->user()->school_id, 403);

        return view('admin.classrooms.form', $this->formData($classroom));
    }

    public function update(Request $request, Classroom $classroom)
    {
        abort_unless($classroom->school_id === auth()->user()->school_id, 403);
        $classroom->update($this->validateData($request));

        return redirect()->route('admin.classrooms.index')->with('success', 'Kelas diperbarui.');
    }

    public function destroy(Classroom $classroom)
    {
        abort_unless($classroom->school_id === auth()->user()->school_id, 403);
        $classroom->delete();

        return back()->with('success', 'Kelas dihapus.');
    }

    private function formData(Classroom $classroom): array
    {
        $schoolId = auth()->user()->school_id;

        return [
            'classroom' => $classroom,
            'majors' => Major::where('school_id', $schoolId)->orderBy('name')->get(),
            'teachers' => Teacher::where('school_id', $schoolId)->orderBy('name')->get(),
        ];
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'grade_level' => ['nullable', 'string', 'max:10'],
            'major_id' => ['nullable', 'exists:majors,id'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);
    }
}

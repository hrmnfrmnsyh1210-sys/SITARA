<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Major;
use Illuminate\Http\Request;

class MajorController extends Controller
{
    public function index(Request $request)
    {
        $majors = Major::where('school_id', auth()->user()->school_id)
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%$s%"))
            ->withCount('classrooms')->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.majors.index', compact('majors'));
    }

    public function create()
    {
        return view('admin.majors.form', ['major' => new Major]);
    }

    public function store(Request $request)
    {
        Major::create($this->validateData($request) + ['school_id' => auth()->user()->school_id]);

        return redirect()->route('admin.majors.index')->with('success', 'Jurusan ditambahkan.');
    }

    public function edit(Major $major)
    {
        abort_unless($major->school_id === auth()->user()->school_id, 403);

        return view('admin.majors.form', compact('major'));
    }

    public function update(Request $request, Major $major)
    {
        abort_unless($major->school_id === auth()->user()->school_id, 403);
        $major->update($this->validateData($request));

        return redirect()->route('admin.majors.index')->with('success', 'Jurusan diperbarui.');
    }

    public function destroy(Major $major)
    {
        abort_unless($major->school_id === auth()->user()->school_id, 403);
        $major->delete();

        return back()->with('success', 'Jurusan dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
        ]);
    }
}

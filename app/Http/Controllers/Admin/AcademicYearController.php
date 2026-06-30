<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicYearController extends Controller
{
    public function index()
    {
        $years = AcademicYear::where('school_id', auth()->user()->school_id)
            ->with('semesters')->orderByDesc('name')->paginate(10);

        return view('admin.academic_years.index', compact('years'));
    }

    public function create()
    {
        return view('admin.academic_years.form', ['year' => new AcademicYear]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        DB::transaction(function () use ($data, $request) {
            if ($request->boolean('is_active')) {
                AcademicYear::where('school_id', auth()->user()->school_id)->update(['is_active' => false]);
            }
            $year = AcademicYear::create($data + [
                'school_id' => auth()->user()->school_id,
                'is_active' => $request->boolean('is_active'),
            ]);
            // Auto-create the two standard Indonesian semesters.
            $year->semesters()->createMany([
                ['name' => 'Ganjil', 'is_active' => true],
                ['name' => 'Genap', 'is_active' => false],
            ]);
        });

        return redirect()->route('admin.academic-years.index')->with('success', 'Tahun ajaran ditambahkan.');
    }

    public function edit(AcademicYear $academicYear)
    {
        abort_unless($academicYear->school_id === auth()->user()->school_id, 403);

        return view('admin.academic_years.form', ['year' => $academicYear]);
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        abort_unless($academicYear->school_id === auth()->user()->school_id, 403);
        $data = $this->validateData($request);

        DB::transaction(function () use ($data, $request, $academicYear) {
            if ($request->boolean('is_active')) {
                AcademicYear::where('school_id', auth()->user()->school_id)->update(['is_active' => false]);
            }
            $academicYear->update($data + ['is_active' => $request->boolean('is_active')]);
        });

        return redirect()->route('admin.academic-years.index')->with('success', 'Tahun ajaran diperbarui.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        abort_unless($academicYear->school_id === auth()->user()->school_id, 403);
        $academicYear->delete();

        return back()->with('success', 'Tahun ajaran dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:20'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
    }
}

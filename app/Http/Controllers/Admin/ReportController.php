<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ExamResult;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function scores(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $results = ExamResult::query()
            ->whereHas('examSchedule.exam', fn ($q) => $q->where('school_id', $schoolId))
            ->whereIn('status', ['submitted', 'graded'])
            ->when($request->classroom_id, fn ($q, $id) => $q->whereHas('student', fn ($s) => $s->where('classroom_id', $id)))
            ->with(['student.classroom', 'examSchedule.exam.subject'])
            ->latest('submitted_at')
            ->paginate(20)->withQueryString();

        $classrooms = Classroom::where('school_id', $schoolId)->orderBy('name')->get();

        return view('admin.reports.scores', compact('results', 'classrooms'));
    }
}

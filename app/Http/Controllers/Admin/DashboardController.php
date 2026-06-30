<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Models\Question;
use App\Models\Student;
use App\Models\Teacher;

class DashboardController extends Controller
{
    public function index()
    {
        $schoolId = auth()->user()->school_id;

        $stats = [
            'teachers' => Teacher::where('school_id', $schoolId)->count(),
            'students' => Student::where('school_id', $schoolId)->count(),
            'classrooms' => Classroom::where('school_id', $schoolId)->count(),
            'questions' => Question::whereHas('questionBank', fn ($q) => $q->where('school_id', $schoolId))->count(),
            'exams' => Exam::where('school_id', $schoolId)->count(),
        ];

        $examsToday = ExamSchedule::whereHas('exam', fn ($q) => $q->where('school_id', $schoolId))
            ->whereDate('start_time', today())
            ->with(['exam.subject', 'classroom'])
            ->get();

        // Average score per subject (last 50 graded results)
        $scoreChart = ExamResult::where('status', 'graded')
            ->whereHas('examSchedule.exam', fn ($q) => $q->where('school_id', $schoolId))
            ->with('examSchedule.exam.subject')
            ->latest()->take(100)->get()
            ->groupBy(fn ($r) => $r->examSchedule->exam->subject->name ?? '-')
            ->map(fn ($g) => round($g->avg('total_score'), 1));

        return view('admin.dashboard', compact('stats', 'examsToday', 'scoreChart'));
    }
}

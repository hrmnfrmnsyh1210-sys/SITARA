<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\ExamResult;
use App\Models\ExamSchedule;

class DashboardController extends Controller
{
    public function index()
    {
        $student = auth()->user()->student;

        $upcoming = collect();
        $results = collect();

        if ($student) {
            $upcoming = ExamSchedule::where('classroom_id', $student->classroom_id)
                ->where('is_active', true)
                ->where('end_time', '>=', now())
                ->with(['exam.subject', 'room'])
                ->orderBy('start_time')->take(5)->get();

            $results = ExamResult::where('student_id', $student->id)
                ->whereIn('status', ['submitted', 'graded'])
                ->with('examSchedule.exam.subject')
                ->latest()->take(5)->get();
        }

        $announcements = Announcement::where('school_id', auth()->user()->school_id)
            ->where('is_published', true)
            ->whereIn('target', ['all', 'students'])
            ->latest()->take(5)->get();

        $stats = [
            'upcoming' => $upcoming->count(),
            'completed' => $student ? ExamResult::where('student_id', $student->id)->whereIn('status', ['submitted', 'graded'])->count() : 0,
            'avg' => $student ? round(ExamResult::where('student_id', $student->id)->where('status', 'graded')->avg('total_score') ?? 0, 1) : 0,
        ];

        return view('siswa.dashboard', compact('student', 'upcoming', 'results', 'announcements', 'stats'));
    }
}

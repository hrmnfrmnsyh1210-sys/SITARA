<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamPackage;
use App\Models\ExamSchedule;
use App\Models\Question;
use App\Models\Teacher;

class DashboardController extends Controller
{
    public function index()
    {
        $teacher = $this->teacher();

        $stats = [
            'questions' => Question::whereHas('questionBank', fn ($q) => $q->where('teacher_id', $teacher?->id))->count(),
            'packages' => ExamPackage::where('teacher_id', $teacher?->id)->count(),
            'exams' => Exam::where('teacher_id', $teacher?->id)->count(),
            'schedules' => ExamSchedule::whereHas('exam', fn ($q) => $q->where('teacher_id', $teacher?->id))->count(),
        ];

        $upcoming = ExamSchedule::whereHas('exam', fn ($q) => $q->where('teacher_id', $teacher?->id))
            ->where('end_time', '>=', now())
            ->with(['exam.subject', 'classroom'])
            ->orderBy('start_time')->take(5)->get();

        return view('guru.dashboard', compact('stats', 'upcoming', 'teacher'));
    }

    private function teacher(): ?Teacher
    {
        return auth()->user()->teacher;
    }
}

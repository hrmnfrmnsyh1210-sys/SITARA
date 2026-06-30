<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Exam;
use App\Models\LoginHistory;
use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'schools' => School::count(),
            'admins' => User::where('role', 'admin')->count(),
            'teachers' => Teacher::count(),
            'students' => Student::count(),
            'exams' => Exam::count(),
        ];

        $schools = School::withCount(['teachers', 'students'])->latest()->take(6)->get();

        // Logins last 7 days
        $loginChart = collect(range(6, 0))->map(function ($d) {
            $date = now()->subDays($d);
            return [
                'label' => $date->translatedFormat('D'),
                'count' => LoginHistory::where('successful', true)->whereDate('logged_in_at', $date)->count(),
            ];
        });

        return view('superadmin.dashboard', compact('stats', 'schools', 'loginChart'));
    }

    public function activityLogs()
    {
        $logs = ActivityLog::with('user')->latest()->paginate(25);

        return view('superadmin.activity-logs', compact('logs'));
    }
}

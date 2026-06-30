<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\School;
use App\Models\Student;
use App\Models\Subscription;
use App\Models\Teacher;

class LandingController extends Controller
{
    public function index()
    {
        $stats = [
            'schools' => School::count(),
            'teachers' => Teacher::count(),
            'students' => Student::count(),
            'exams' => Exam::count(),
        ];

        $price = Subscription::monthlyPrice();
        $currency = config('sitara.subscription.currency');

        return view('landing', compact('stats', 'price', 'currency'));
    }
}

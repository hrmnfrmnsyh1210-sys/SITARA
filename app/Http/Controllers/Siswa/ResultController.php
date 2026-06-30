<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\ExamResult;

class ResultController extends Controller
{
    public function index()
    {
        $student = auth()->user()->student;

        $results = ExamResult::where('student_id', $student?->id)
            ->whereIn('status', ['submitted', 'graded'])
            ->with('examSchedule.exam.subject')
            ->latest('submitted_at')->paginate(12);

        return view('siswa.results.index', compact('results'));
    }

    public function show(ExamResult $result)
    {
        abort_unless($result->student_id === auth()->user()->student?->id, 403);

        $result->load('examSchedule.exam.subject', 'answers.question');

        return view('siswa.results.show', compact('result'));
    }
}

<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExamScheduleController extends Controller
{
    public function index()
    {
        $schedules = ExamSchedule::whereHas('exam', fn ($q) => $q->where('teacher_id', auth()->user()->teacher?->id))
            ->with(['exam.subject', 'classroom', 'room'])->withCount('results')
            ->orderByDesc('start_time')->paginate(12);

        return view('guru.schedules.index', compact('schedules'));
    }

    public function create(Request $request)
    {
        return view('guru.schedules.form', ['schedule' => new ExamSchedule, 'selectedExam' => $request->exam_id] + $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['token'] = strtoupper(Str::random(6));
        $data['is_active'] = true;
        ExamSchedule::create($data);

        return redirect()->route('guru.schedules.index')->with('success', 'Jadwal ujian dibuat. Token: ' . $data['token']);
    }

    public function edit(ExamSchedule $schedule)
    {
        $this->authorizeSchedule($schedule);

        return view('guru.schedules.form', ['schedule' => $schedule, 'selectedExam' => $schedule->exam_id] + $this->formData());
    }

    public function update(Request $request, ExamSchedule $schedule)
    {
        $this->authorizeSchedule($schedule);
        $schedule->update($this->validateData($request) + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('guru.schedules.index')->with('success', 'Jadwal diperbarui.');
    }

    public function destroy(ExamSchedule $schedule)
    {
        $this->authorizeSchedule($schedule);
        $schedule->delete();

        return back()->with('success', 'Jadwal dihapus.');
    }

    private function formData(): array
    {
        $schoolId = auth()->user()->school_id;

        return [
            'exams' => Exam::where('teacher_id', auth()->user()->teacher?->id)->get(),
            'classrooms' => Classroom::where('school_id', $schoolId)->orderBy('name')->get(),
            'rooms' => Room::where('school_id', $schoolId)->orderBy('name')->get(),
        ];
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'exam_id' => ['required', 'exists:exams,id'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
        ]);
    }

    private function authorizeSchedule(ExamSchedule $schedule): void
    {
        abort_unless($schedule->exam->teacher_id === auth()->user()->teacher?->id, 403);
    }
}

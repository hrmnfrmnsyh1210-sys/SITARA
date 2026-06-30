<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ExamPackage;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Http\Request;

class ExamPackageController extends Controller
{
    public function index(Request $request)
    {
        $packages = ExamPackage::where('teacher_id', auth()->user()->teacher?->id)
            ->with('subject')->withCount('questions')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%$s%"))
            ->latest()->paginate(10)->withQueryString();

        return view('guru.packages.index', compact('packages'));
    }

    public function create()
    {
        return view('guru.packages.form', ['package' => new ExamPackage, 'subjects' => $this->subjects()]);
    }

    public function store(Request $request)
    {
        $package = ExamPackage::create($this->validateData($request) + [
            'school_id' => auth()->user()->school_id,
            'teacher_id' => auth()->user()->teacher?->id,
        ]);

        return redirect()->route('guru.packages.edit', $package)->with('success', 'Paket dibuat. Sekarang pilih soal.');
    }

    public function edit(ExamPackage $package)
    {
        $this->authorizePackage($package);

        // Available questions = questions in this teacher's banks for the package subject.
        $available = Question::whereHas('questionBank', fn ($q) => $q
            ->where('teacher_id', auth()->user()->teacher?->id)
            ->where('subject_id', $package->subject_id))
            ->get();

        $selected = $package->questions->pluck('id')->all();

        return view('guru.packages.form', [
            'package' => $package,
            'subjects' => $this->subjects(),
            'available' => $available,
            'selected' => $selected,
        ]);
    }

    public function update(Request $request, ExamPackage $package)
    {
        $this->authorizePackage($package);
        $package->update($this->validateData($request));

        if ($request->has('questions')) {
            $sync = [];
            foreach ((array) $request->input('questions', []) as $order => $qid) {
                $sync[$qid] = ['order' => $order];
            }
            $package->questions()->sync($sync);
        }

        return redirect()->route('guru.packages.index')->with('success', 'Paket soal diperbarui.');
    }

    public function destroy(ExamPackage $package)
    {
        $this->authorizePackage($package);
        $package->delete();

        return back()->with('success', 'Paket dihapus.');
    }

    private function subjects()
    {
        return Subject::where('school_id', auth()->user()->school_id)->orderBy('name')->get();
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'randomize_questions' => ['nullable'],
            'randomize_options' => ['nullable'],
        ]) + [
            'randomize_questions' => $request->boolean('randomize_questions'),
            'randomize_options' => $request->boolean('randomize_options'),
        ];
    }

    private function authorizePackage(ExamPackage $package): void
    {
        abort_unless($package->teacher_id === auth()->user()->teacher?->id, 403);
    }
}

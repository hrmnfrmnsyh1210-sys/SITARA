<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $teachers = Teacher::where('school_id', auth()->user()->school_id)
            ->with('user')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('nip', 'like', "%$s%"))
            ->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        return view('admin.teachers.form', ['teacher' => new Teacher]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:30'],
            'gender' => ['nullable', 'in:L,P'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $user = User::create([
                'school_id' => auth()->user()->school_id,
                'role' => User::ROLE_GURU,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
            ]);

            Teacher::create([
                'user_id' => $user->id,
                'school_id' => auth()->user()->school_id,
                'name' => $data['name'],
                'nip' => $data['nip'] ?? null,
                'gender' => $data['gender'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'photo' => $request->hasFile('photo') ? $request->file('photo')->store('photos') : null,
            ]);
        });

        return redirect()->route('admin.teachers.index')->with('success', 'Guru berhasil ditambahkan.');
    }

    public function edit(Teacher $teacher)
    {
        abort_unless($teacher->school_id === auth()->user()->school_id, 403);

        return view('admin.teachers.form', compact('teacher'));
    }

    public function update(Request $request, Teacher $teacher)
    {
        abort_unless($teacher->school_id === auth()->user()->school_id, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:30'],
            'gender' => ['nullable', 'in:L,P'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($teacher->user_id)],
            'password' => ['nullable', 'min:6'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        DB::transaction(function () use ($request, $data, $teacher) {
            $teacher->user->update(array_filter([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => ! empty($data['password']) ? Hash::make($data['password']) : null,
            ], fn ($v) => $v !== null));

            if ($request->hasFile('photo')) {
                if ($teacher->photo) {
                    Storage::delete($teacher->photo);
                }
                $data['photo'] = $request->file('photo')->store('photos');
            } else {
                unset($data['photo']);
            }
            unset($data['email'], $data['password']);
            $teacher->update($data);
        });

        return redirect()->route('admin.teachers.index')->with('success', 'Guru berhasil diperbarui.');
    }

    public function destroy(Teacher $teacher)
    {
        abort_unless($teacher->school_id === auth()->user()->school_id, 403);
        $teacher->user?->delete(); // cascades teacher row
        $teacher->delete();

        return back()->with('success', 'Guru berhasil dihapus.');
    }
}

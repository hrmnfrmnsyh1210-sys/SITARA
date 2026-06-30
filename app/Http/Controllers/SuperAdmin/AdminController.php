<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $admins = User::where('role', 'admin')
            ->with('school')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"))
            ->when($request->school_id, fn ($q, $id) => $q->where('school_id', $id))
            ->latest()->paginate(10)->withQueryString();

        $schools = School::orderBy('name')->get();

        return view('superadmin.admins.index', compact('admins', 'schools'));
    }

    public function create()
    {
        return view('superadmin.admins.form', ['admin' => new User, 'schools' => School::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['role'] = User::ROLE_ADMIN;
        User::create($data);

        return redirect()->route('superadmin.admins.index')->with('success', 'Admin sekolah berhasil ditambahkan.');
    }

    public function edit(User $admin)
    {
        abort_unless($admin->role === 'admin', 404);

        return view('superadmin.admins.form', ['admin' => $admin, 'schools' => School::orderBy('name')->get()]);
    }

    public function update(Request $request, User $admin)
    {
        $data = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($admin->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'min:6', 'confirmed'],
            'is_active' => ['nullable'],
        ]);
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $data['is_active'] = $request->boolean('is_active');
        $admin->update($data);

        return redirect()->route('superadmin.admins.index')->with('success', 'Admin berhasil diperbarui.');
    }

    public function destroy(User $admin)
    {
        $admin->delete();

        return back()->with('success', 'Admin berhasil dihapus.');
    }
}

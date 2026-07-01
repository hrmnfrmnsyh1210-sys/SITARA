<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars');
        }

        $user->update($data);

        // `name`/`phone`/photo are duplicated on the linked student/teacher record
        // (admin screens read from there), so keep them in sync.
        $profile = $user->student ?? $user->teacher;
        if ($profile) {
            $sync = [
                'name' => $data['name'],
                'phone' => $data['phone'] ?? $profile->phone,
            ];
            // Admin uses students.photo / teachers.photo; profile uploads to users.avatar.
            if (isset($data['avatar'])) {
                $sync['photo'] = $data['avatar'];
            }
            $profile->update($sync);
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $request->user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Kata sandi berhasil diubah.');
    }
}

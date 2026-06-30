<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordChangeController extends Controller
{
    /**
     * Show the forced "change your default password" screen.
     */
    public function edit()
    {
        // If they no longer need to change it, send them on their way.
        if (! auth()->user()->must_change_password) {
            return redirect()->route(auth()->user()->dashboardRoute());
        }

        return view('auth.force-password');
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::min(6)],
        ], [
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        // Don't let them reuse the default password.
        if (Hash::check($data['password'], $user->password)) {
            return back()->withErrors([
                'password' => 'Kata sandi baru tidak boleh sama dengan kata sandi default.',
            ]);
        }

        $user->update([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
        ]);

        return redirect()->route($user->dashboardRoute())
            ->with('success', 'Kata sandi berhasil diperbarui. Selamat datang!');
    }
}

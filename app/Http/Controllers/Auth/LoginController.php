<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->dashboardRoute());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [], ['login' => 'Email / Username / NIS']);

        $login = trim($data['login']);

        // Resolve the user: staff login by email/username, students by NIS.
        $user = User::where('email', $login)
            ->orWhere('username', $login)
            ->first();

        if (! $user) {
            // Try NIS -> student -> user
            $student = Student::where('nis', $login)->first();
            $user = $student?->user;
        }

        if (! $user || ! Auth::attempt(['id' => $user->id, 'password' => $data['password']], $request->boolean('remember'))) {
            LoginHistory::create([
                'user_id' => $user?->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'successful' => false,
                'logged_in_at' => now(),
            ]);

            throw ValidationException::withMessages([
                'login' => 'Kredensial yang Anda masukkan tidak cocok.',
            ]);
        }

        if (! $user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'login' => 'Akun Anda dinonaktifkan. Hubungi administrator.',
            ]);
        }

        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();

        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'successful' => true,
            'logged_in_at' => now(),
        ]);

        return redirect()->intended(route($user->dashboardRoute()));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

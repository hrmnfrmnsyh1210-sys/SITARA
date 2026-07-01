<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolSubscribed
{
    /**
     * Blokir akses guru & siswa ketika sekolah mereka tidak punya langganan aktif.
     * Super admin tidak terpengaruh (tidak memakai middleware ini).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Halaman info "langganan ditangguhkan" harus tetap bisa diakses agar tidak loop.
        if (! $user || $request->routeIs('subscription.suspended')) {
            return $next($request);
        }

        $school = $user->school;

        // Sekolah nonaktif = langganan sudah habis / belum ada, jadi semua guru &
        // siswa di sekolah itu langsung kehilangan akses meski masih ada baris
        // langganan aktif di database.
        if (! $school || ! $school->is_active || ! $school->hasActiveSubscription()) {
            return redirect()->route('subscription.suspended');
        }

        return $next($request);
    }
}

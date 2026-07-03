<?php

namespace App\Providers;

use App\Support\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Tampilkan nama hari/bulan dalam Bahasa Indonesia (mis. translatedFormat -> "Juli").
        // Zona waktu aplikasi diatur ke Asia/Jakarta (WIB) di config/app.php.
        Carbon::setLocale('id');

        // Catat setiap aktivitas (tambah/ubah/hapus data) ke Log Aktivitas.
        ActivityLogger::register();
    }
}

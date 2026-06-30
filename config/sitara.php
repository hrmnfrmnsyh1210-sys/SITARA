<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Langganan Sekolah
    |--------------------------------------------------------------------------
    | Konfigurasi paket langganan bulanan (flat) per sekolah.
    */
    'subscription' => [
        'monthly_price' => (int) env('SITARA_MONTHLY_PRICE', 150000),
        'currency' => env('SITARA_CURRENCY', 'Rp'),

        // Toleransi hari setelah ends_at sebelum akses guru/siswa diblokir.
        'grace_days' => (int) env('SITARA_GRACE_DAYS', 0),

        // Mulai tampilkan peringatan "akan berakhir" berapa hari sebelum jatuh tempo.
        'warning_days' => (int) env('SITARA_WARNING_DAYS', 7),
    ],
];

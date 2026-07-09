<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            // Kalau aktif, siswa wajib mengirim lokasi sebelum bisa memulai ujian.
            $table->boolean('requires_location')->default(false)->after('token');
        });

        Schema::table('exam_results', function (Blueprint $table) {
            // Lokasi siswa saat memulai ujian (audit trail, tidak dipakai memblokir berdasarkan jarak).
            $table->decimal('latitude', 10, 7)->nullable()->after('ip_address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->unsignedInteger('location_accuracy')->nullable()->comment('Radius akurasi GPS dalam meter')->after('longitude');
            $table->timestamp('location_captured_at')->nullable()->after('location_accuracy');
        });
    }

    public function down(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->dropColumn('requires_location');
        });

        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'location_accuracy', 'location_captured_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            // Berapa kali siswa terdeteksi keluar dari halaman ujian (indikasi mencontek)
            $table->unsignedInteger('violation_count')->default(0)->after('remaining_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropColumn('violation_count');
        });
    }
};

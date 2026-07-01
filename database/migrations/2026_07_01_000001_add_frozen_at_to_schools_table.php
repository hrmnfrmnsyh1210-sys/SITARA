<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            // Diisi saat sekolah dinonaktifkan manual selagi langganan masih aktif.
            // Sisa masa langganan "dibekukan" pada tanggal ini dan dilanjutkan kembali
            // saat sekolah diaktifkan lagi. NULL = tidak sedang dibekukan.
            $table->timestamp('frozen_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('frozen_at');
        });
    }
};

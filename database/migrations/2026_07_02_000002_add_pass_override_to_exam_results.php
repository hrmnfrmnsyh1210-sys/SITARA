<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            // Keputusan kelulusan manual oleh guru:
            // null = otomatis (ikut nilai), true = diluluskan, false = tidak diluluskan.
            $table->boolean('pass_override')->nullable()->after('violation_count');
        });
    }

    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropColumn('pass_override');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Paket Soal
        Schema::create('exam_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('randomize_questions')->default(true);
            $table->boolean('randomize_options')->default(true);
            $table->timestamps();
        });

        // Pivot soal dalam paket
        Schema::create('exam_package_question', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('order')->default(0);
            $table->decimal('score', 6, 2)->nullable();
            $table->timestamps();

            $table->unique(['exam_package_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_package_question');
        Schema::dropIfExists('exam_packages');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu baris = satu attempt/sesi ujian seorang siswa
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->enum('status', ['in_progress', 'submitted', 'graded'])->default('in_progress');
            $table->decimal('total_score', 6, 2)->default(0);
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('wrong_count')->default(0);
            $table->unsignedInteger('empty_count')->default(0);
            $table->boolean('is_passed')->default(false);
            // Urutan soal yang dirandom untuk siswa ini (array question_id)
            $table->json('question_order')->nullable();
            $table->integer('remaining_seconds')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->unique(['exam_schedule_id', 'student_id']);
        });

        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_result_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->json('answer')->nullable();
            $table->string('file_path')->nullable()->comment('Untuk soal upload file');
            $table->boolean('is_correct')->nullable();
            $table->decimal('score', 6, 2)->nullable();
            $table->boolean('is_flagged')->default(false);
            $table->boolean('graded')->default(false)->comment('Untuk essay yang dikoreksi guru');
            $table->timestamps();

            $table->unique(['exam_result_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answers');
        Schema::dropIfExists('exam_results');
    }
};

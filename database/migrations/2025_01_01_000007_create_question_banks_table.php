<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_bank_id')->constrained()->cascadeOnDelete();
            $table->enum('type', [
                'multiple_choice', // Pilihan Ganda
                'true_false',      // Benar / Salah
                'matching',        // Menjodohkan
                'short_answer',    // Isian Singkat
                'essay',           // Essay
                'file_upload',     // Upload File
            ])->default('multiple_choice');
            $table->text('question_text');
            $table->string('image')->nullable();
            $table->string('audio')->nullable();
            $table->string('video')->nullable();
            // options: array of {key,text,image} for MC; pairs for matching
            $table->json('options')->nullable();
            // correct_answer: key(s) for MC/TF, map for matching, text for short_answer
            $table->json('correct_answer')->nullable();
            $table->decimal('score', 6, 2)->default(1);
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->text('explanation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
        Schema::dropIfExists('question_banks');
    }
};

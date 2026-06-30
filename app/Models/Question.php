<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Question extends Model
{
    use HasFactory;

    public const TYPES = [
        'multiple_choice' => 'Pilihan Ganda',
        'true_false' => 'Benar / Salah',
        'matching' => 'Menjodohkan',
        'short_answer' => 'Isian Singkat',
        'essay' => 'Essay',
        'file_upload' => 'Upload File',
    ];

    protected $fillable = [
        'question_bank_id', 'type', 'question_text', 'image', 'audio', 'video',
        'options', 'correct_answer', 'score', 'difficulty', 'explanation',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answer' => 'array',
        'score' => 'decimal:2',
    ];

    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class);
    }

    public function examPackages(): BelongsToMany
    {
        return $this->belongsToMany(ExamPackage::class, 'exam_package_question')
            ->withPivot(['order', 'score'])->withTimestamps();
    }

    /**
     * Is this question auto-gradable, or does it need a teacher?
     */
    public function isAutoGradable(): bool
    {
        return in_array($this->type, ['multiple_choice', 'true_false', 'matching', 'short_answer'], true);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}

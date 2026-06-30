<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_result_id', 'question_id', 'answer', 'file_path',
        'is_correct', 'score', 'is_flagged', 'graded',
    ];

    protected $casts = [
        'answer' => 'array',
        'is_correct' => 'boolean',
        'is_flagged' => 'boolean',
        'graded' => 'boolean',
        'score' => 'decimal:2',
    ];

    public function examResult(): BelongsTo
    {
        return $this->belongsTo(ExamResult::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}

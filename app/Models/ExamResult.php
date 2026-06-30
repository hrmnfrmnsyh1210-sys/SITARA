<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_schedule_id', 'student_id', 'started_at', 'submitted_at', 'status',
        'total_score', 'correct_count', 'wrong_count', 'empty_count', 'is_passed',
        'question_order', 'remaining_seconds', 'ip_address',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'question_order' => 'array',
        'is_passed' => 'boolean',
        'total_score' => 'decimal:2',
    ];

    public function examSchedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function hasUngradedEssay(): bool
    {
        return $this->answers()->where('graded', false)->whereNotNull('answer')->exists();
    }
}

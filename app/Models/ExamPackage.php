<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id', 'subject_id', 'teacher_id', 'name', 'description',
        'randomize_questions', 'randomize_options',
    ];

    protected $casts = [
        'randomize_questions' => 'boolean',
        'randomize_options' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'exam_package_question')
            ->withPivot(['order', 'score'])->withTimestamps()->orderBy('order');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function getTotalScoreAttribute(): float
    {
        return (float) $this->questions->sum(fn ($q) => $q->pivot->score ?? $q->score);
    }
}

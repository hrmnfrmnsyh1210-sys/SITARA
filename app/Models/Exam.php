<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id', 'exam_package_id', 'subject_id', 'teacher_id',
        'academic_year_id', 'semester_id', 'title', 'description',
        'duration_minutes', 'passing_score', 'randomize_questions',
        'randomize_options', 'show_result', 'status',
    ];

    protected $casts = [
        'randomize_questions' => 'boolean',
        'randomize_options' => 'boolean',
        'show_result' => 'boolean',
        'passing_score' => 'decimal:2',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function examPackage(): BelongsTo
    {
        return $this->belongsTo(ExamPackage::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ExamSchedule::class);
    }
}

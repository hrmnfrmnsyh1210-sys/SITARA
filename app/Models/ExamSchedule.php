<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id', 'classroom_id', 'room_id', 'start_time', 'end_time', 'token', 'is_active',
        'requires_location',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
        'requires_location' => 'boolean',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    public function isOngoing(): bool
    {
        $now = now();
        return $this->is_active && $now->between($this->start_time, $this->end_time);
    }

    public function isUpcoming(): bool
    {
        return now()->lt($this->start_time);
    }

    public function isFinished(): bool
    {
        return now()->gt($this->end_time);
    }

    public function statusLabel(): string
    {
        if ($this->isUpcoming()) return 'Akan Datang';
        if ($this->isOngoing()) return 'Berlangsung';
        return 'Selesai';
    }
}

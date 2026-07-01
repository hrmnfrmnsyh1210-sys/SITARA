<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'npsn', 'level', 'address', 'phone', 'email', 'website',
        'logo', 'principal_name', 'primary_color', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? Storage::url($this->logo) : null;
    }

    // ----------------------------------------------------------------
    // Langganan
    // ----------------------------------------------------------------

    /**
     * Langganan aktif terakhir yang masih memberi akses, jika ada.
     */
    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->whereNotNull('ends_at')
            ->orderByDesc('ends_at')
            ->get()
            ->first(fn (Subscription $s) => $s->isCovering());
    }

    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }

    /**
     * Tanggal berakhir langganan aktif (untuk banner & info).
     */
    public function subscriptionEndsAt(): ?\Illuminate\Support\Carbon
    {
        return $this->activeSubscription()?->ends_at;
    }

    /**
     * Ada pengajuan perpanjangan yang masih menunggu konfirmasi super admin.
     */
    public function hasPendingSubscription(): bool
    {
        return $this->subscriptions()
            ->where('status', Subscription::STATUS_PENDING)
            ->exists();
    }
}

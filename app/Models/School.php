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

    protected $casts = [
        'is_active' => 'boolean',
        'frozen_at' => 'datetime',
    ];

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
     * Langganan berstatus aktif terakhir (tanpa syarat masih meng-cover), yaitu
     * langganan yang akan dibekukan / dilanjutkan saat sekolah dinonaktifkan.
     */
    public function latestActiveSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->whereNotNull('ends_at')
            ->orderByDesc('ends_at')
            ->first();
    }

    // ----------------------------------------------------------------
    // Pembekuan (freeze) langganan saat sekolah dinonaktifkan
    // ----------------------------------------------------------------

    /**
     * Sedang dalam status beku: dinonaktifkan selagi langganan masih berjalan.
     */
    public function isFrozen(): bool
    {
        return $this->frozen_at !== null;
    }

    /**
     * Sisa hari langganan yang tersimpan saat pembekuan. NULL bila tidak beku.
     */
    public function frozenRemainingDays(): ?int
    {
        if (! $this->frozen_at) {
            return null;
        }

        $sub = $this->latestActiveSubscription();
        if (! $sub || ! $sub->ends_at) {
            return 0;
        }

        return self::wholeDaysBetween($this->frozen_at, $sub->ends_at);
    }

    /**
     * Bekukan sisa masa langganan pada saat sekolah dinonaktifkan.
     * Tidak melakukan apa pun bila sudah beku atau tidak ada langganan aktif.
     */
    public function freezeSubscription(): void
    {
        if ($this->frozen_at || ! $this->hasActiveSubscription()) {
            return;
        }

        $this->forceFill(['frozen_at' => now()])->save();
    }

    /**
     * Lanjutkan langganan: kembalikan sisa hari (yang dibekukan) dihitung dari
     * tanggal reaktivasi, lalu hapus penanda beku.
     */
    public function thawSubscription(): void
    {
        if (! $this->frozen_at) {
            return;
        }

        $sub = $this->latestActiveSubscription();
        if ($sub && $sub->ends_at) {
            $remaining = self::wholeDaysBetween($this->frozen_at, $sub->ends_at);
            if ($remaining > 0) {
                $sub->ends_at = now()->startOfDay()->addDays($remaining);
                $sub->save();
            }
        }

        $this->forceFill(['frozen_at' => null])->save();
    }

    /**
     * Selisih hari penuh (>= 0) antara dua tanggal, dihitung lewat timestamp agar
     * tidak bergantung pada semantik diffInDays yang berubah antar versi Carbon.
     */
    private static function wholeDaysBetween(\DateTimeInterface $from, \DateTimeInterface $to): int
    {
        $fromTs = \Illuminate\Support\Carbon::instance($from)->startOfDay()->getTimestamp();
        $toTs = \Illuminate\Support\Carbon::instance($to)->startOfDay()->getTimestamp();

        return (int) max(0, floor(($toTs - $fromTs) / 86400));
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

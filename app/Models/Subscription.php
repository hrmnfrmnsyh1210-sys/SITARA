<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'school_id',
        'plan_name',
        'months',
        'price',
        'status',
        'starts_at',
        'ends_at',
        'payment_method',
        'payment_proof',
        'note',
        'requested_by',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'approved_at' => 'datetime',
            'price' => 'decimal:2',
            'months' => 'integer',
        ];
    }

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ----------------------------------------------------------------
    // Harga (disetel super admin, tersimpan di tabel settings)
    // ----------------------------------------------------------------

    public const PRICE_KEY = 'subscription_monthly_price';

    /**
     * Harga langganan per bulan. Diambil dari settings (global), dengan
     * fallback ke nilai default di config/sitara.php.
     */
    public static function monthlyPrice(): int
    {
        $stored = Setting::get(self::PRICE_KEY);

        return $stored !== null
            ? (int) $stored
            : (int) config('sitara.subscription.monthly_price');
    }

    public static function setMonthlyPrice(int $price): void
    {
        Setting::put(self::PRICE_KEY, $price);
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    /**
     * Apakah langganan ini sedang memberi akses (aktif & belum lewat masa berlaku + toleransi).
     */
    public function isCovering(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE || ! $this->ends_at) {
            return false;
        }

        $grace = (int) config('sitara.subscription.grace_days', 0);

        return $this->ends_at->copy()->addDays($grace)->endOfDay()->isFuture();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu Konfirmasi',
            self::STATUS_ACTIVE => $this->isCovering() ? 'Aktif' : 'Kedaluwarsa',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_EXPIRED => 'Kedaluwarsa',
            default => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match (true) {
            $this->status === self::STATUS_PENDING => 'warning',
            $this->status === self::STATUS_ACTIVE && $this->isCovering() => 'success',
            $this->status === self::STATUS_REJECTED => 'danger',
            default => 'secondary',
        };
    }
}

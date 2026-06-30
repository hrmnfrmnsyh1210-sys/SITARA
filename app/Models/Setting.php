<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = ['school_id', 'key', 'value'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public static function get(string $key, $default = null, ?int $schoolId = null)
    {
        return static::where('key', $key)
            ->where('school_id', $schoolId)
            ->value('value') ?? $default;
    }

    public static function put(string $key, $value, ?int $schoolId = null): void
    {
        static::updateOrCreate(
            ['key' => $key, 'school_id' => $schoolId],
            ['value' => $value],
        );
    }
}

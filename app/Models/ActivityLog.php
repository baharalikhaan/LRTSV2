<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'event',
        'description',
        'method',
        'url',
        'route',
        'ip',
        'user_agent',
        'duration_seconds',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Human-readable duration for a completed session (logout rows).
     */
    public function getDurationForHumansAttribute(): ?string
    {
        if ($this->duration_seconds === null) {
            return null;
        }

        $s = (int) $this->duration_seconds;
        $h = intdiv($s, 3600);
        $m = intdiv($s % 3600, 60);

        if ($h > 0) {
            return "{$h}h {$m}m";
        }
        if ($m > 0) {
            return "{$m}m";
        }

        return "{$s}s";
    }
}

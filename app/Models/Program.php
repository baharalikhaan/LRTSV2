<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $table = 'programs';

    protected $fillable = [
        'grant_id',
        'cycle_id',
        'program_title',
        'prog_rpt_deadline',
        'prog_rpt2_deadline',
        'final_rpt_deadline',
        'description',
        'is_visible',
    ];

    protected $casts = [
        'prog_rpt_deadline' => 'datetime',
        'prog_rpt2_deadline' => 'datetime',
        'final_rpt_deadline' => 'datetime',
        'is_visible' => 'boolean',
    ];

    public function grant()
    {
        return $this->belongsTo(Grant::class);
    }

    public function cycle()
    {
        return $this->belongsTo(CycleConfig::class, 'cycle_id');
    }

    public function cycleConfig()
    {
        return $this->belongsTo(CycleConfig::class, 'cycle_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'program_id');
    }

    /**
     * Determine if the program is still active based on deadlines.
     * A program is active if the final report deadline has not yet passed.
     * If final_rpt_deadline is null, defaults to active.
     */
    public function isActive(): bool
    {
        $now = now();

        if ($this->final_rpt_deadline === null) {
            return true;
        }

        return $now->lessThan($this->final_rpt_deadline);
    }

    /**
     * Get the computed status label based on deadlines.
     */
    public function computedStatus(): string
    {
        return $this->isActive() ? 'active' : 'inactive';
    }

    /**
     * Scope to only active programs (deadlines not passed).
     */
    public function scopeActive($query)
    {
        $now = now()->toDateTimeString();

        return $query->where(function ($q) use ($now) {
            $q->whereNull('final_rpt_deadline')
              ->orWhere('final_rpt_deadline', '>', $now);
        });
    }
}

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
        'extended_prog_rpt_deadline',
        'prog_rpt2_deadline',
        'extended_prog_rpt2_deadline',
        'final_rpt_deadline',
        'extended_final_rpt_deadline',
        'description',
        'is_visible',
    ];

    protected $casts = [
        'prog_rpt_deadline' => 'datetime',
        'extended_prog_rpt_deadline' => 'datetime',
        'prog_rpt2_deadline' => 'datetime',
        'extended_prog_rpt2_deadline' => 'datetime',
        'final_rpt_deadline' => 'datetime',
        'extended_final_rpt_deadline' => 'datetime',
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
     * A program is active if the final report deadline (or its extended version) has not yet passed.
     * If both final_rpt_deadline and extended_final_rpt_deadline are null, defaults to active.
     */
    public function isActive(): bool
    {
        $now = now();

        // Determine the effective final deadline (use extended if set, otherwise original)
        $finalDeadline = $this->extended_final_rpt_deadline ?? $this->final_rpt_deadline;

        // If no final deadline is set at all, consider the program active
        if ($finalDeadline === null) {
            return true;
        }

        // Program is active if the final deadline hasn't passed yet
        return $now->lessThan($finalDeadline);
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
            // Active if: final_rpt_deadline is null (no deadline set), OR
            // the effective final deadline (extended if set, otherwise original) is still in the future
            $q->whereNull('final_rpt_deadline')
              ->orWhere(function ($sub) use ($now) {
                  $sub->whereNotNull('extended_final_rpt_deadline')
                      ->where('extended_final_rpt_deadline', '>', $now);
              })
              ->orWhere(function ($sub) use ($now) {
                  $sub->whereNull('extended_final_rpt_deadline')
                      ->where('final_rpt_deadline', '>', $now);
              });
        });
    }
}

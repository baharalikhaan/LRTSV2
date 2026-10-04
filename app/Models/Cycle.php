<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cycle extends Model
{
    use HasFactory;

    protected $table = 'programs';

    protected $fillable = [
        'grant_id',
        'cycle_id',
        'program_title',
        'prog_rpt_deadline',
        'final_rpt_deadline',
        'description',
    ];

    protected $casts = [
        'prog_rpt_deadline' => 'datetime',
        'final_rpt_deadline' => 'datetime',
    ];

    public function grant()
    {
        return $this->belongsTo(Grant::class);
    }

    public function cycleConfig()
    {
        return $this->belongsTo(CycleConfig::class, 'cycle_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'program_id');
    }

    public function isActive(): bool
    {
        $now = now();

        // Check the latest applicable deadline
        $progDeadline = $this->prog_rpt_deadline;
        $finalDeadline = $this->final_rpt_deadline;

        // If both deadlines are null, treat as active
        if (!$progDeadline && !$finalDeadline) {
            return true;
        }

        // If progress report deadline exists and hasn't passed yet
        if ($progDeadline && $now->lessThanOrEqualTo($progDeadline)) {
            return true;
        }

        // If progress report deadline passed but final report deadline exists and hasn't passed yet
        if ($finalDeadline && $now->lessThanOrEqualTo($finalDeadline)) {
            return true;
        }

        // Both deadlines have passed
        return false;
    }

    public function scopeActive($query)
    {
        $now = now();
        return $query->where(function ($q) use ($now) {
            $q->whereNull('final_rpt_deadline')
              ->whereNull('prog_rpt_deadline')
              ->orWhere(function ($q2) use ($now) {
                  $q2->where(function ($q3) use ($now) {
                      $q3->whereNotNull('prog_rpt_deadline');
                  })->where(function ($q3) use ($now) {
                      $q3->whereRaw('prog_rpt_deadline >= ?', [$now]);
                  });
              })
              ->orWhere(function ($q2) use ($now) {
                  $q2->where(function ($q3) use ($now) {
                      $q3->whereNotNull('final_rpt_deadline');
                  })->where(function ($q3) use ($now) {
                      $q3->whereRaw('final_rpt_deadline >= ?', [$now]);
                  });
              });
        });
    }
}

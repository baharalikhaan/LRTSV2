<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectBudget extends Model
{
    use HasFactory;

    protected $table = 'project_budgets';

    protected $fillable = [
        'project_id',
        'project_num',
        'project_name',
        'budget_amount',
        'actual_exp_amount',
        'commitment_amount',
        'available_balance',
        'last_synced_at',
    ];

    protected $casts = [
        'budget_amount'      => 'decimal:2',
        'actual_exp_amount'  => 'decimal:2',
        'commitment_amount'  => 'decimal:2',
        'available_balance'  => 'decimal:2',
        'last_synced_at'     => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the utilization percentage (actual spend / budget * 100).
     */
    public function utilizationPercent(): float
    {
        if ($this->budget_amount <= 0) {
            return 0;
        }

        return round(($this->actual_exp_amount / $this->budget_amount) * 100, 1);
    }

    /**
     * Get the utilization status color class.
     */
    public function utilizationStatus(): string
    {
        $pct = $this->utilizationPercent();

        if ($pct < 50) {
            return 'warning'; // Under-utilized
        } elseif ($pct <= 90) {
            return 'success'; // Good
        } else {
            return 'info'; // Near full / over
        }
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* The Cycle class represents a model for cycles with specific attributes and relationships to
projects. */
class Cycle extends Model
{
    protected $table = 'cycle';
    const STATUS_ACTIVE = 'active';
    const STATUS_FINISH = 'finish';
    use HasFactory;
    protected $fillable = [
        'cycle_title',
        'status',
        'prog_rpt_deadline',
        'extended_prog_rpt_deadline',
        'prog2_rpt_deadline',
        'extended_prog2_rpt_deadline',
        'final_rpt_deadline',
        'extended_final_rpt_deadline',
        'upload_outcomes',
        'old_project_id',
        'final_rpt_deadline',
        'final_bi_monthly_reminder',
        'progress_rpt_deadline',
        'progress_bi_monthly_reminder',
        'grant_type'

    ];

    public function projects(){
        return $this->hasMany(project::class, 'cycle');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalGradingDraft extends Model
{
    use HasFactory;

    protected $table = 'final_grading_draft';

    protected $fillable = ['project_id', 'grade', 'final_decision', 'submitted'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

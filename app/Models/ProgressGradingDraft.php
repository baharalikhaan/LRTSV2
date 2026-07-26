<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressGradingDraft extends Model
{
    use HasFactory;

    protected $table = 'progress_grading_draft';

    protected $fillable = ['project_id', 'grade', 'submitted'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

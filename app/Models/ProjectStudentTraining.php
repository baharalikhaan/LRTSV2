<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectStudentTraining extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'student_level',
        'student_count',
        'training_days',
    ];

    protected $casts = [
        'student_count' => 'integer',
        'training_days' => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

}

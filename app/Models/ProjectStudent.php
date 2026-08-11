<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectStudent extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'type',
        'std_id',
        'days',
        'score',
        'verifcation_by_system',
        'verifcation_by_reviewer',
    ];

    protected $casts = [
        'days'  => 'integer',
        'score' => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasOne(ProjectStudentDetail::class, 'project_student_id');
    }
}

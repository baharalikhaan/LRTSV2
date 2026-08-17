<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalReportGrading extends Model
{
    use HasFactory;

    protected $table = 'final_report_grading';

    protected $fillable = [
        'project_id', 'user_id',
        'gradeA', 'commentA',
        'gradeB', 'commentB',
        'gradeC', 'commentC',
        'gradeD', 'commentD',
        'scoreA', 'autoGradeA',
        'scoreB', 'autoGradeB',
        'scoreC', 'autoGradeC',
        'total',
        'publish',
        'isAdmin', 'isAccepted',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

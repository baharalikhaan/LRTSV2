<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalReportGrading extends Model
{
    use HasFactory;
    protected $table = 'final_report_grading';

    protected $fillable = [
        'project_id',
        'user_id',
        'gradeA',
        'gradeB',
        'gradeC',
        'gradeD',
        'commentA',
        'commentB',
        'commentC',
        'commentD',
        'total',
        'publish',
        'isaccepted'
    ];
}

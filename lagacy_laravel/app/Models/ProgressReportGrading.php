<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressReportGrading extends Model
{
    use HasFactory;

    protected $table = 'progress_report_grading';
    protected $fillable = [
        'project_id',
        'user_id',
        'report_type',
        'analysis',
        'comments',
        'recommendation',
        "achievementsRating",
        "publicationsRating",
        "studentsRating",
        "achievementsComments",
        "publicationsComments",
        "studentsComments",
        "isAccepted",
        "ethical",
        "publish",
        "budgetRating",
        "budgetComments"
    ];
}

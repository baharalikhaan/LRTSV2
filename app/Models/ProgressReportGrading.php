<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressReportGrading extends Model
{
    use HasFactory;

    protected $table = 'progress_report_grading';

    protected $fillable = [
        'project_id', 'user_id',
        'achievementsRating', 'publicationsRating', 'studentsRating', 'budgetRating',
        'achievementsComments', 'publicationsComments', 'studentsComments', 'budgetComments',
        'ethical', 'analysis', 'comments', 'recommendation',
        'publish', 'report_type', 'isAccepted',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function achievementsRatingRef()
    {
        return $this->belongsTo(Rating::class, 'achievementsRating');
    }

    public function publicationsRatingRef()
    {
        return $this->belongsTo(Rating::class, 'publicationsRating');
    }

    public function studentsRatingRef()
    {
        return $this->belongsTo(Rating::class, 'studentsRating');
    }

    public function budgetRatingRef()
    {
        return $this->belongsTo(Rating::class, 'budgetRating');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressGradingDraft extends Model
{
    use HasFactory;
    protected $table = 'progress_grading_draft';
    protected $fillable = [
        'project_id',
        'user_id',
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

    ];
}

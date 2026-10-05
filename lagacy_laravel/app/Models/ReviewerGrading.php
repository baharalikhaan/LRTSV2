<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewerGrading extends Model
{
    use HasFactory;
    protected $table = 'reviewer_grading';
    protected $fillable = [
        'reviewer',
        'cycle',
        'conflict',
        'responsiveness',
        'comprehensiveness',
        'no_reviewers',
        'behaviour',
        'user_id'
    ];
}

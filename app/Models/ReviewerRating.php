<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewerRating extends Model
{
    use HasFactory;

    protected $table = 'reviewer_ratings';

    protected $fillable = [
        'reviewer_id',
        'user_id',
        'program_id',
        'conflict',
        'responsiveness',
        'comprehensiveness',
        'no_reviewers',
        'behaviour',
    ];

    protected $casts = [
        'conflict' => 'integer',
        'responsiveness' => 'integer',
        'comprehensiveness' => 'integer',
        'no_reviewers' => 'integer',
        'behaviour' => 'integer',
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function rater()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingDetails extends Model
{
    use HasFactory;
    protected $table = 'grading_details';
    protected $fillable = [
        'project_id',
        'user_id',
        'reviewer_grade',
        'outcome_grade',
    ];
}

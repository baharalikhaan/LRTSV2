<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalGradingDraft extends Model
{
    protected $table = 'final_grading_draft';
    use HasFactory;
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
        'total'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifyOutcomes extends Model
{
    use HasFactory;
    protected $table = 'verify_outcome';

    protected $fillable = [
        'project_id',
        'user_id',
        'outcome_id',
        'status',
    ];


}

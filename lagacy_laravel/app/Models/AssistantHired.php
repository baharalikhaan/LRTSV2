<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssistantHired extends Model
{
    use HasFactory;
    protected $table = 'assistants_hired';
    protected $fillable = [
        'project_id',
        'user_id',
        'type',
        'emp_id',

    ];
}

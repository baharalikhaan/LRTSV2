<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttachedStudent extends Model
{
    use HasFactory;
    protected $table = 'attached_students';

    protected $fillable = [
        'project_id',
        'user_id',
        'type',
        'std_id',
        'days',
        'score',

    ];
}

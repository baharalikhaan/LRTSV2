<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contribution extends Model
{
    use HasFactory;
    protected $table = 'contribution';

    protected $fillable = [
        'project_id',
        'user_id',
        'type',
        'detail',
        'score',

    ];

    
}

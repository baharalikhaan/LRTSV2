<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commitments extends Model
{
    use HasFactory;
    protected $table = 'commitments';
    protected $fillable = [
        'project_id',
        'q1article',
        'q2article',
        'q3article',
        'q4article',
        'confArticle',
        'books',
        'editBooks',
        'chapters',
        'ip',
        'filedPatent',
        'openSourceSW',
        'startUp',
        'ethical',
        'master',
        'UG',
        'Phd',
        'crossCollege',

    ];

}

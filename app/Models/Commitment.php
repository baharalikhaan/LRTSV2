<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commitment extends Model
{
    use HasFactory;

    protected $table = 'commitments';

    protected $fillable = [
        'project_id',
        // Research outputs
        'q1article',
        'q2article',
        'q3article',
        'q4article',
        'confArticle',
        'books',
        'editBooks',
        'chapters',
        // IP & innovation
        'ip',
        'filedPatent',
        'openSourceSW',
        'startUp',
        'ethical',
        // Students & training
        'master',
        'UG',
        'Phd',
        'crossCollege',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

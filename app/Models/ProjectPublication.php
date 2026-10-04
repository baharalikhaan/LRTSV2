<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectPublication extends Model
{
    protected $fillable = [
        'project_id',
        'outcome_id',
        'authors',
        'publication_title',
        'journal',
        'year',
        'doi',
        'url',
        'status',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

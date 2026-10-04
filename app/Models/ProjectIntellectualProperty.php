<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectIntellectualProperty extends Model
{
    protected $fillable = [
        'project_id',
        'ip_type',
        'title',
        'description',
        'filing_status',
        'application_number',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

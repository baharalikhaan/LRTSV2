<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'type',
        'original_filename',
        'stored_filename',
        'file_path',
        'version',
        'notes',
        'submitted',
        'submitted_at',
    ];

    protected $casts = [
        'version'      => 'integer',
        'submitted'    => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
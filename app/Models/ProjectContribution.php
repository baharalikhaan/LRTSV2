<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectContribution extends Model
{
    use HasFactory;

    protected $table = 'project_contributions';

    protected $fillable = [
        'project_id',
        'user_id',
        'type',
        'detail',
        'score',
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

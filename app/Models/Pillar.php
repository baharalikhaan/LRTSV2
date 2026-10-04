<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pillar extends Model
{
    use HasFactory;

    protected $table = 'pillars';

    protected $fillable = ['pillar', 'subpillar', 'description'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_pillars', 'pillar_id', 'user_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_pillar', 'pillar_id', 'project_id');
    }
}

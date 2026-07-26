<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $table = 'tags';

    protected $fillable = ['name', 'type', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_tags', 'tag_id', 'user_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_tag', 'tag_id', 'project_id');
    }
}

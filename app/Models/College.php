<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class College extends Model
{
    use HasFactory;

    protected $table = 'colleges';

    protected $fillable = ['code', 'name'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_college', 'tag_id', 'user_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_college', 'college_id', 'project_id');
    }
}

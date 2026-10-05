<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projects_reviewer extends Model
{
    use HasFactory;
    protected $table = 'projects_reviewers';

    protected $fillable = ['project_id', 'user_id'];

    public function assigned_projects(){
        return $this->hasMany(Projects::class,'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FromConfTool extends Model
{
    use HasFactory;
    protected $table = 'from_conf_tool';
    protected $fillable = [
        'old_project_id',
        'cycle',
        'title',
        'author',
        'email',
        'pillars',
        'tags',
        'grant_type',
        'added'
    ];
}

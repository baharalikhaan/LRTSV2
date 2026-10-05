<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectPillar extends Model
{
    use HasFactory;
    protected $table = 'project_pillar';
    protected $fillable = [
        'project_id',
        'pillar_id'
    ];
}

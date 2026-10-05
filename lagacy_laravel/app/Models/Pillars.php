<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pillars extends Model
{
    use HasFactory;
    protected $table = 'pillars';

    protected $fillable = [
        'pillar'
    ];
}

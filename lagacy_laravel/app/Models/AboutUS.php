<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutUS extends Model
{
    use HasFactory;
    protected $table = 'about_us';
    protected $fillable = [
        'path',
        'name',
        'role',
        'introduction',
        'email',
        'phone',
        'address'
    ];
}

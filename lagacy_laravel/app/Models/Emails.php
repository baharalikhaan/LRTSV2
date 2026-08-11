<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emails extends Model
{
    protected $table = 'email_content';
    protected $fillable = [

        'subject',
        'contenta',
        'contentb',
        'farewell',
        'regards',
    ];
    use HasFactory;
}

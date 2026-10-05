<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $table = 'email_template';
    protected $fillable = [

        'subject',
        'contents',
        'signature',
    ];
    use HasFactory;
}

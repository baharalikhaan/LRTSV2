<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailSMTP extends Model
{
    protected $table = 'email_settings';
    protected $fillable = [

        'host',
        'smtp_auth',
        'smtp_secure',
        'port',
        'username',
        'password',
        'setfrom_name',
        'setfrom_email'
    ];
    use HasFactory;
}

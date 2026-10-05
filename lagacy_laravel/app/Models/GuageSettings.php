<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuageSettings extends Model
{
    protected $table = 'guage_settings';
    protected $fillable = [
        'redfrom',
        'redto',
        'yellowfrom',
        'yellowto',
        'greenfrom',
        'greento',
    ];
    use HasFactory;
}

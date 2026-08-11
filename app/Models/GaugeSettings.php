<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GaugeSettings extends Model
{
    use HasFactory;

    protected $table = 'gauge_settings';

    protected $fillable = [
        'name',
        'redfrom',
        'redto',
        'yellowfrom',
        'yellowto',
        'greenfrom',
        'greento',
    ];
}

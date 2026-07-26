<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CycleConfig extends Model
{
    use HasFactory;

    protected $table = 'cycle_configs';

    protected $fillable = [
        'year',
        'title',
    ];

    protected $casts = [
        'year' => 'integer',
    ];

    public function programs()
    {
        return $this->hasMany(Program::class, 'cycle_id');
    }
}

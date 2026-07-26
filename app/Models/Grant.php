<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grant extends Model
{
    use HasFactory;

    protected $fillable = [
        'grant_code',
        'grant_name',
        'category',
        'funding_agency',
        'max_duration_years',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_duration_years' => 'integer',
    ];

    public function programs()
    {
        return $this->hasMany(Program::class, 'grant_id');
    }
}

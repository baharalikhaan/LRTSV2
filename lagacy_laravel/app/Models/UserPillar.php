<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPillar extends Model
{
    protected $table = 'user_pillars';
    use HasFactory;
    protected $fillable = [
        'user_id',
        'pillar_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

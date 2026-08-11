<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTag extends Model
{
    protected $table = 'users_tags';
    use HasFactory;
    protected $fillable = [
        'user_id',
        'tag_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

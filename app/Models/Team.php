<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'team';

    protected $fillable = [
        'path',
        'name',
        'role',
        'introduction',
        'email',
        'phone',
        'address',
    ];
}

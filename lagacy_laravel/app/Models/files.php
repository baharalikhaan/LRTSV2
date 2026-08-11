<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;
    protected $table = 'files';


    const TEST = 'asdsadsa';

    protected $fillable = [
        'name',
        'path',
        'user_id',
        'token'
    ];

}

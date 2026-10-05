<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockModel extends Model
{
    protected $table = 'BlockChain';

    use HasFactory;
    protected $fillable = ['id', 'previous_hash', 'data', 'hash', 'timestamp'];


}

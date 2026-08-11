<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileMetadata extends Model
{
    use HasFactory;
    protected $table = 'oais_archival';
    protected $fillable = [
        'filename',
        'path',
        'mime_type',
        'size',
        'extension',
        'hash',
        'last_modified',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicationDetail extends Model
{
    use HasFactory;
    protected $table = 'publication_detail';
    protected $fillable = [
        'title',
        'publication_date',
        'venue',
        'type',
        'outcome_id',
        'url'
    ];
}

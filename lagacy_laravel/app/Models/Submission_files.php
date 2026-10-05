<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission_files extends Model {
    use HasFactory;

    protected $table = 'submission_files';

    protected $fillable = [
        'submission_id',
        'path',
    ];


    public function submission() {
        return $this->belongsTo(Submissions::class, 'id');
    }

    public function props() {
        return $this->belongsTo(File::class, 'id');
    }

}

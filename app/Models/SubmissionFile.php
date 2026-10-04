<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionFile extends Model
{
    use HasFactory;

    protected $table = 'submission_files';

    protected $fillable = [
        'submission_id', 'file_path', 'file_name', 'file_type', 'file_size', 'uploaded_by',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

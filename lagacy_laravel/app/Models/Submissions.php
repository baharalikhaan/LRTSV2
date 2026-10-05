<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submissions extends Model {

    const TYPE_PROPOSAL = 'Proposal';
    const TYPE_TECHNICAL = 'Technical';
    const TYPE_PROGRESS = 'Progress';
    const TYPE_PROGRESS2 = 'Progress2';
    const TYPE_FINAL = 'Final';
    const TYPE_READINESS = 'Readiness';

    use HasFactory;
    protected $table = 'submissions';

    protected $fillable = [
        'project_id',
        'title',
        'type',
        'user_id',
        'due_date',
    ];


    // public function files() {
    //     return $this->hasMany(Submission_files::class, 'submission_id');
    // }


}

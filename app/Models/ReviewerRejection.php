<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewerRejection extends Model
{
    protected $table = 'reviewer_rejections';

    const UPDATED_AT = null;

    protected $fillable = [
        'project_id',
        'user_id',
        'reason',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
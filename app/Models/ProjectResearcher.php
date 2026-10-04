<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectResearcher extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'name',
        'category',
        'days',
        'score',
        'verifcation_by_reviewer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

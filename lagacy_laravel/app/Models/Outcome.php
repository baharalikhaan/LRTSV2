<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outcome extends Model
{
    const OUTCOME_SATISFACTORY = 'Satisfactory';
    const OUTCOME_OUTSTANDING = 'Outstanding';
    const OUTCOME_IMPROVEMENT = 'Improvement Desired';
    const OUTCOME_RESERVED = 'Reserved';

    use HasFactory;
    protected $table = 'outcomes';
    protected $fillable = [
        'project_id',
        'user_id',
        'type',
        'identifier',
        'verification_by_system',
        'verification_by_reviewer',
        'score',
        'online_date'
    ];

    public function project() {
        return $this->hasOne(Projects::class, 'project_id');
    }
    public function user() {
        return $this->hasOne(User::class, 'user_id');
    }
}

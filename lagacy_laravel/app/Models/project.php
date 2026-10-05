<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model {

    const STATUS_PENDING = 'Pending';
    const STATUS_ACCEPTED = 'Accepted';
    const STATUS_REJECTED = 'Rejected';
    const STATUS_COMPLETED = 'Completed';

    use HasFactory;
    protected $table = 'projects';

    protected $fillable = [
        'old_project_id',
        'conf_tool_id',
        'title',
        'user_id',
        'status',
        'cycle',
        'isAdmin',
        'requested_budget_qar',
        'college_decision',
        'rsd_feedback',
        'final_rsd_decision',
        'spending',
        'spending_detail',
        'publicatins',
        'student_engagement'

    ];

    public function owner() {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function reviewers() {
        return $this->belongsToMany(User::class, Projects_reviewer::class, 'project_id', 'user_id');
    }

    public function stakeholders() {
        return $this->belongsToMany(User::class, Projects_stakeholder::class, 'project_id', 'user_id');
    }

    public function submissions() {
        return $this->hasMany(Submissions::class, 'project_id');
    }
    public function outcome() {
        return $this->hasOne(Outcome::class, 'project_id');
    }
    public function commitments(){
        return $this->hasOne(Commitments::class, 'project_id');
    }
    public function FinalReportGrading() {
        return $this->hasMany(FinalReportGrading::class, 'project_id','id');
    }
    public function FinalGradingDraft() {
        return $this->hasMany(FinalGradingDraft::class, 'project_id');
    }

}

<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'faculty',
        'qu_id',
        'nationality_id',
        'phone',
        'department',
        'college',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'faculty' => 'boolean',
        'is_active' => 'boolean',
        'nationality_id' => 'integer',
    ];

    // ─── Role Checks ────────────────────────────────────────────────────────

    public function isAdmin()
    {
        return $this->type === 'Admin' || $this->type === 'Admin+LPI+Reviewer';
    }

    public function isLPI()
    {
        return in_array($this->type, ['LPI', 'LPI+Reviewer', 'Admin+LPI+Reviewer']);
    }

    public function isReviewer()
    {
        return in_array($this->type, ['Reviewer', 'LPI+Reviewer', 'Admin+LPI+Reviewer']);
    }

    /**
     * Get the list of individual sub-roles this user has.
     * e.g. 'Admin+LPI+Reviewer' → ['Admin', 'LPI', 'Reviewer']
     */
    public function subRoles(): array
    {
        return explode('+', $this->type);
    }

    /**
     * Get the current active role (from session if switched, otherwise the first sub-role).
     */
    public function activeRole(): string
    {
        $sessionRole = session('active_role');
        $subRoles = $this->subRoles();
        if ($sessionRole && in_array($sessionRole, $subRoles)) {
            return $sessionRole;
        }
        return $subRoles[0] ?? $this->type;
    }

    /**
     * Check if the user can switch roles (has multiple sub-roles).
     */
    public function canSwitchRole(): bool
    {
        return count($this->subRoles()) > 1;
    }

    // ─── Relationships ──────────────────────────────────────────────────────

    public function projects()
    {
        return $this->hasMany(Project::class, 'lpi_id');
    }

    public function reviewedProjects()
    {
        return $this->belongsToMany(Project::class, 'projects_reviewers', 'user_id', 'project_id');
    }

    public function stakeholderProjects()
    {
        return $this->belongsToMany(Project::class, 'projects_stakeholders', 'user_id', 'project_id');
    }

    public function nationality()
    {
        return $this->belongsTo(Nationality::class);
    }

    /**
     * College is stored as a 1-to-1 string field on the users table
     * (the user_college pivot table was removed). Return the matching
     * College model(s) for display purposes only.
     */
    public function getCollegeListAttribute()
    {
        if (!$this->college) {
            return collect();
        }

        $college = College::where('name', $this->college)->first();

        return $college ? collect([$college]) : collect();
    }

/**
     * The research pillars associated with this user, parsed from the
     * `users.pillars` string column (comma / semicolon / newline separated).
     * Returns a collection of { pillar: name } objects for display.
     */
    public function getPillarListAttribute()
    {
        if (!$this->pillars || trim($this->pillars) === '') {
            return collect();
        }

        $parts = preg_split('/[;,|\n\r]+/', $this->pillars);
        $names = array_values(array_filter(array_map('trim', $parts)));

        return collect($names)->map(function ($name) {
            return (object) ['pillar' => $name];
        });
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
}

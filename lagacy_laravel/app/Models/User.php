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
    protected $table = 'users';

    const TYPE_ADMIN = 'Admin';
    const TYPE_LPI = 'LPI';
    const TYPE_REVIEWER = 'Reviewer';
    const TYPE_LPI_REVIEWER = 'LPI+Reviewer';
    const TYPE_TEST = 'Test';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'userid',
        'password',
        'faculty',
        'type',
        'roles',
        'nationality',
        'hash'
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function tags()
    {
        return $this->belongsToMany(tag::class, 'users_tags', 'user_id', 'tag_id');
    }
    public function projects_owned()
    {
        return $this->hasMany(Projects::class, 'user_id');
    }
    public function userPillar()
    {
        return $this->hasOne(UserPillar::class, 'user_id', 'id');
    }

    public function userEmail()
    {
        return $this->email;
    }
}

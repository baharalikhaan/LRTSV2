<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class studentgrant_students extends Model
{
    use HasFactory;
    protected $table = 'studentgrant_students';
    protected $fillable = [
        'id',
        'project_id',

        'email',
        'first_name',
        'nationality',
        'student_id',
        'student_status',
        'last_name',
        'major',
        'minor',
        'std_program',
        'std_level',
        'admission_term',
        'reg_in_course',
        'college'

    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectAPI extends Model
{
    use HasFactory;
    protected $table = 'project_api';
    protected $fillable = [
        'project_num' ,
        'project_name',
        'budget_amount',
        'actual_exp_amount',
        'committment_amount',
        'available_balance',

      
    ];
}

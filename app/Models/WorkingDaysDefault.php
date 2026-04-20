<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingDaysDefault extends Model
{
    protected $table = 'working_days_defaults';
    
    protected $fillable = [
        'day_of_week',
        'day_name',
        'is_working',
    ];
    
    protected $casts = [
        'is_working' => 'boolean',
    ];
}
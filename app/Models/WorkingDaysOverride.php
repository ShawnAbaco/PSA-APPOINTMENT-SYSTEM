<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingDaysOverride extends Model
{
    protected $table = 'working_days_overrides';
    
    protected $fillable = [
        'date',
        'is_working',
        'reason',
    ];
    
    protected $casts = [
        'is_working' => 'boolean',
        'date' => 'date',
    ];
}
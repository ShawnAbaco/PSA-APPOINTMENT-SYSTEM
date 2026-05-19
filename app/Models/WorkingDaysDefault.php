<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingDaysDefault extends Model
{
    protected $fillable = [
        'day_of_week',
        'is_working',
    ];

    protected $casts = [
        'is_working' => 'boolean',
    ];
}
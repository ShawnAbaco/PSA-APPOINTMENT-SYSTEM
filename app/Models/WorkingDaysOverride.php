<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingDaysOverride extends Model
{
    protected $fillable = [
        'date',
        'is_working',
        'reason',
    ];

    protected $casts = [
        'date' => 'date',
        'is_working' => 'boolean',
    ];
}
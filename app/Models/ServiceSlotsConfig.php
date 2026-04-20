<?php
// app/Models/ServiceSlotsConfig.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSlotsConfig extends Model
{
    protected $table = 'service_slots_config';
    
    protected $fillable = [
        'service_code',
        'service_name',
        'default_capacity',
        'max_capacity',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'default_capacity' => 'integer',
        'max_capacity' => 'integer',
    ];
}
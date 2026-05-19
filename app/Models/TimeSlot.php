<?php
// app/Models/TimeSlot.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    protected $fillable = [
        'start_time',
        'end_time',
        'label',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function appointmentSlots()
    {
        return $this->hasMany(AppointmentSlot::class);
    }

    public function getFormattedTimeRangeAttribute()
    {
        return $this->label ??
            date('g:i A', strtotime($this->start_time)) . ' - ' .
            date('g:i A', strtotime($this->end_time));
    }
}
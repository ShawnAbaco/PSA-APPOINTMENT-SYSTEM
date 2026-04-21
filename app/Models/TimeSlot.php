<?php
// app/Models/TimeSlot.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_time',
        'end_time',
        'slot_label',
        'capacity_per_slot',
        'display_order',
        'is_active'
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'is_active' => 'boolean',
    ];

    /**
     * Get the appointment slots for this time slot
     */
    public function appointmentSlots()
    {
        return $this->hasMany(AppointmentSlot::class);
    }

    /**
     * Get capacity settings for this time slot
     */
    public function capacitySettings()
    {
        return $this->hasMany(TimeSlotCapacitySetting::class);
    }

    /**
     * Get formatted time range
     */
    public function getFormattedTimeRangeAttribute()
    {
        return $this->slot_label ?? date('g:i A', strtotime($this->start_time)) . ' - ' . date('g:i A', strtotime($this->end_time));
    }
}
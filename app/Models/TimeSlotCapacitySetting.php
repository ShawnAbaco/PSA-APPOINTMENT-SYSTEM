<?php
// app/Models/TimeSlotCapacitySetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlotCapacitySetting extends Model
{
    use HasFactory;

    protected $table = 'time_slot_capacity_settings';

    protected $fillable = [
        'time_slot_id',
        'day_type',
        'capacity'
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    /**
     * Get the time slot that owns this capacity setting
     */
    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }
}
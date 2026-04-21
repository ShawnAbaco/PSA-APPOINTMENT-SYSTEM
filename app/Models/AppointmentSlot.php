<?php
// app/Models/AppointmentSlot.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'time_slot_id',
        'day_type',
        'total_capacity',
        'reg_capacity',
        'updating_capacity',
        'inquiry_capacity',
        'reg_booked',
        'updating_booked',
        'inquiry_booked',
        'reg_available',
        'updating_available',
        'inquiry_available',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the time slot that this appointment slot belongs to
     */
    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class, 'time_slot_id');
    }

    /**
     * Get the user who created this slot
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
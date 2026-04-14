<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AppointmentSlot extends Model
{
    protected $table = 'appointment_slots';

    protected $fillable = [
        'date',
        'total_capacity',
        'booked_count',
        'available_count',
        'time_slots',
        'is_holiday',
        'is_special_non_working',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'date' => 'date',
        'time_slots' => 'array',
        'is_holiday' => 'boolean',
        'is_special_non_working' => 'boolean',
    ];

    /**
     * Get available slots for a specific date
     */
    public static function getAvailableSlots($date, $defaultCapacity = 20)
    {
        $slot = static::where('date', $date)->first();
        
        if (!$slot) {
            return $defaultCapacity;
        }
        
        if ($slot->is_holiday || $slot->is_special_non_working) {
            return 0;
        }
        
        return $slot->available_count ?? ($slot->total_capacity - $slot->booked_count);
    }
    
    /**
     * Check if date is bookable
     */
    public static function isBookable($date, $clientCount = 1)
    {
        $slot = static::where('date', $date)->first();
        
        if ($slot && ($slot->is_holiday || $slot->is_special_non_working)) {
            return false;
        }
        
        $availableSlots = self::getAvailableSlots($date);
        return $availableSlots >= $clientCount;
    }
}
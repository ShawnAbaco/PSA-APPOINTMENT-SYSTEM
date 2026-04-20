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
        'day_type',  // working, half_day, holiday, special
        'notes',
        'created_by'
    ];

    protected $casts = [
        'date' => 'date',
        'time_slots' => 'array',
    ];

    // Helper methods for day type
    public function isWorkingDay()
    {
        return $this->day_type === 'working';
    }
    
    public function isHalfDay()
    {
        return $this->day_type === 'half_day';
    }
    
    public function isHoliday()
    {
        return $this->day_type === 'holiday';
    }
    
    public function isSpecialDay()
    {
        return $this->day_type === 'special';
    }
    
    // Get available slots based on day type
    public function getAvailableSlotsAttribute()
    {
        if ($this->isHoliday()) {
            return 0;
        }
        
        if ($this->isHalfDay()) {
            return ceil($this->total_capacity / 2) - $this->booked_count;
        }
        
        return $this->total_capacity - $this->booked_count;
    }
}
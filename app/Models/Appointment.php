<?php
// app/Models/Appointment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'appointment_number',
        'type',
        'appointment_date',
        'appointment_time',
        'status',
        'contact_name',
        'contact_email',
        'contact_mobile',
        'reference_code',
        'confirmed_at',
        'cancelled_at',
        'cancellation_reason',
        'completed_at',
        'created_by',
        'processed_by',
        'notes',
        'metadata',
        // ========== NEW LOCATION FIELDS ==========
        'user_lat',
        'user_lng',
        'user_city',
        'user_address',
        'user_zipcode',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
        // ========== NEW LOCATION CASTS ==========
        'user_lat' => 'decimal:8',
        'user_lng' => 'decimal:8',
    ];

    // Relationships
    public function clients()
    {
        return $this->hasMany(AppointmentClient::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('appointment_date', now()->toDateString());
    }
    
    // ========== NEW LOCATION SCOPES ==========
    public function scopeByCity($query, $city)
    {
        return $query->where('user_city', 'like', '%' . $city . '%');
    }
    
    public function scopeWithLocation($query)
    {
        return $query->whereNotNull('user_lat')->whereNotNull('user_lng');
    }
    
    public function scopeInCities($query, array $cities)
    {
        return $query->whereIn('user_city', $cities);
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isConfirmed()
    {
        return $this->status === 'confirmed';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }
    
    // ========== NEW LOCATION ACCESSORS ==========
    public function getLocationNameAttribute()
    {
        return $this->user_city ?? 'Location not provided';
    }
    
    public function getFullAddressAttribute()
    {
        if ($this->user_address) {
            return $this->user_address;
        }
        return $this->user_city ?? 'No address provided';
    }
    
    public function getCoordinatesAttribute()
    {
        if ($this->user_lat && $this->user_lng) {
            return [
                'lat' => (float) $this->user_lat,
                'lng' => (float) $this->user_lng
            ];
        }
        return null;
    }
    
    public function hasLocation()
    {
        return !is_null($this->user_lat) && !is_null($this->user_lng);
    }
}
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
        'time_slot_id',
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
        'user_lat',
        'user_lng',
        'user_city',
        'user_address',
        'user_zipcode',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
        'user_lat' => 'decimal:8',
        'user_lng' => 'decimal:8',
    ];

    // Relationships
    public function clients()
    {
        return $this->hasMany(AppointmentClient::class);
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
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
    public function scopeToday($query)
    {
        return $query->whereDate('appointment_date', now());
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Helpers
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentClient extends Model
{
    use HasFactory;

    protected $table = 'appointment_clients';

    protected $fillable = [
        'appointment_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'sex',
        'birthdate',
        'service',
        'requirements_acknowledged',
        'acknowledged_at',
        'psa_reference_number',
        'is_verified',
        'verified_at',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'requirements_acknowledged' => 'boolean',
        'acknowledged_at' => 'datetime',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
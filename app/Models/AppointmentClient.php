<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentClient extends Model
{
    use HasFactory;

    protected $table = 'appointment_clients';

    protected $fillable = [
        'client_number',
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

    // Relationships
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        $name = "{$this->first_name} {$this->last_name}";
        if ($this->suffix) {
            $name .= " {$this->suffix}";
        }
        return $name;
    }

    public function getServiceNameAttribute()
    {
        $services = [
            'reg' => 'National ID Registration',
            'correction' => 'Correction/Updating',
            'ephilid' => 'ePhilID Issuance',
            'trn' => 'TRN Retrieval',
        ];
        return $services[$this->service] ?? $this->service;
    }
    
    /**
     * Generate a unique client number
     * Format: CLT-YYYYMMDD-XXXXX (e.g., CLT-20260414-00001)
     */
    public static function generateClientNumber()
    {
        $date = now()->format('Ymd');
        $last = self::whereDate('created_at', today())->count() + 1;
        return 'CLT-' . $date . '-' . str_pad($last, 5, '0', STR_PAD_LEFT);
    }
}
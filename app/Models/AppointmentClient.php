<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentClient extends Model
{
    use HasFactory;

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
        'has_trn',
        'trn_number',
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

    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name} {$this->suffix}");
    }

    public function getServiceNameAttribute()
    {
        return [
            'reg' => 'National ID Registration',
            'updating' => 'Updating/Correction',
            'inquiry' => 'Inquiry / TRN Retrieval',
        ][$this->service] ?? $this->service;
    }

    public static function generateClientNumber()
{
    $year = date('Y');
    $month = date('m');
    
    $last = self::whereYear('created_at', $year)
        ->whereMonth('created_at', $month)
        ->count() + 1;
    
    return 'CLN-' . $year . $month . '-' . str_pad($last, 5, '0', STR_PAD_LEFT);
}
}
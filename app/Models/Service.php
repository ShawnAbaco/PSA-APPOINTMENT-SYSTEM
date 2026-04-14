<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'requirements',
        'estimated_duration_minutes',
        'is_active',
        'display_order',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'estimated_duration_minutes' => 'integer',
        'display_order' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get service name by code
     */
    public static function getName($code)
    {
        $services = [
            'reg' => 'National ID Registration',
            'correction' => 'Correction/Updating',
            'ephilid' => 'ePhilID Issuance',
            'trn' => 'TRN Retrieval',
        ];
        
        return $services[$code] ?? $code;
    }

    /**
     * Get all active services
     */
    public static function getActiveServices()
    {
        return static::where('is_active', true)
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Get requirements by service code
     */
    public static function getRequirements($code)
    {
        $requirements = [
            'reg' => [
                'PRIMARY: PSA Birth Certificate + 1 government-issued ID',
                'SECONDARY: PSA/LCRO Birth Certificate, Voter\'s ID, Postal ID, PhilHealth ID',
                'Employee ID, School ID, Barangay Certificate'
            ],
            'correction' => [
                'First/Last Name: Birth Certificate, Marriage Certificate',
                'Sex/DOB: PSA Birth Certificate',
                'Address: Barangay Certificate + Proof of Billing'
            ],
            'ephilid' => [
                'Transaction slip or reference number',
                'For Representative: Authorization letter + valid ID',
                'For Minor: Birth Certificate + Guardian\'s valid ID'
            ],
            'trn' => [
                'Provide: First, Middle, Last Name',
                'Date of Birth (exact as registered)',
                'Sex / Gender information'
            ],
        ];
        
        return $requirements[$code] ?? [];
    }

    /**
     * Scope for active services
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordered by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
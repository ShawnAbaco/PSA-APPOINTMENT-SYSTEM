<?php
// app/Models/DocumentRequirement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentRequirement extends Model
{
    protected $fillable = [
        'service', 'age_group', 'requirement', 'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    // Scope for active requirements
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    // Get requirements by service and age group
    public static function getRequirements($service, $ageGroup = 'adult')
    {
        return self::active()
            ->where('service', $service)
            ->where('age_group', $ageGroup)
            ->get();
    }
    
    // Check if age is child (1-4 years old)
    public static function getAgeGroup($birthdate)
    {
        if (!$birthdate) return 'adult';
        
        $age = \Carbon\Carbon::parse($birthdate)->age;
        
        if ($age >= 1 && $age <= 4) {
            return 'child';
        }
        
        return 'adult';
    }
}
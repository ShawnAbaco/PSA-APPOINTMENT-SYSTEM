<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
    ];

    protected $casts = [
        'value' => 'json',
    ];

    /**
     * Get setting value by key (automatically decrypt password type)
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        if ($setting) {
            $value = $setting->value;
            
            // Decrypt if it's a password type and value is not empty
            if ($setting->type === 'password' && !empty($value)) {
                try {
                    // Check if the value looks like an encrypted string
                    if (preg_match('/^[a-zA-Z0-9\/\+=]+$/', $value) && strlen($value) > 30) {
                        return Crypt::decryptString($value);
                    }
                    return $value;
                } catch (\Exception $e) {
                    return $value;
                }
            }
            
            // Convert based on type
            if ($setting->type === 'boolean') {
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }
            if ($setting->type === 'number') {
                return (int)$value;
            }
            
            return $value;
        }
        return $default;
    }

    /**
     * Set setting value by key (automatically encrypt password type)
     */
    public static function set($key, $value, $group = 'general', $type = 'text', $description = null)
    {
        // Encrypt if it's a password type and value is not empty and not placeholder
        if ($type === 'password' && !empty($value) && $value !== '********') {
            $value = Crypt::encryptString($value);
        }
        
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'type' => $type,
                'description' => $description,
            ]
        );
    }
}
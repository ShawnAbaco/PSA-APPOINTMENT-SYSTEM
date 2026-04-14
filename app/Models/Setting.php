<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Get setting value by key
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        if ($setting) {
            // Decode JSON value if needed
            $value = $setting->value;
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
     * Set setting value by key
     */
    public static function set($key, $value, $group = 'general', $type = 'text', $description = null)
    {
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
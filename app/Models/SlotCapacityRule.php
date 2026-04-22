<?php
// app/Models/SlotCapacityRule.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlotCapacityRule extends Model
{
    use HasFactory;

    protected $table = 'slot_capacity_rules';

    protected $fillable = [
        'time_slot_id',
        'day_type',
        'reg_capacity',
        'updating_capacity',
        'inquiry_capacity',
    ];

    protected $casts = [
        'reg_capacity' => 'integer',
        'updating_capacity' => 'integer',
        'inquiry_capacity' => 'integer',
    ];

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }
}
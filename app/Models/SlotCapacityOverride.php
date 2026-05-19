<?php
// app/Models/SlotCapacityOverride.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlotCapacityOverride extends Model
{
    use HasFactory;

    protected $table = 'slot_capacity_overrides';

    protected $fillable = [
        'date',
        'time_slot_id',
        'reg_capacity',
        'updating_capacity',
        'inquiry_capacity',
        'reason',
    ];

    protected $casts = [
        'date' => 'date',
        'reg_capacity' => 'integer',
        'updating_capacity' => 'integer',
        'inquiry_capacity' => 'integer',
    ];

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }
}
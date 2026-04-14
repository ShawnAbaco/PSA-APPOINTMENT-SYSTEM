<?php
// app/Http/Controllers/Admin/SettingsController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentSlot;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.settings.index', compact('settings'));
    }
    
    public function update(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now()]
            );
        }
        
        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    public function manageSlots()
{
    $slots = AppointmentSlot::orderBy('date', 'desc')->paginate(30);
    return view('admin.slots.index', compact('slots'));
}

public function updateSlot(Request $request, $id)
{
    $slot = AppointmentSlot::findOrFail($id);
    
    $validated = $request->validate([
        'total_capacity' => 'required|integer|min:0|max:100',
        'is_holiday' => 'boolean',
        'is_special_non_working' => 'boolean',
        'notes' => 'nullable|string',
    ]);
    
    $slot->update($validated);
    $slot->available_count = $slot->total_capacity - $slot->booked_count;
    $slot->save();
    
    return redirect()->back()->with('success', 'Slot updated successfully');
}

public function createSlot(Request $request)
{
    $validated = $request->validate([
        'date' => 'required|date|unique:appointment_slots,date',
        'total_capacity' => 'required|integer|min:0|max:100',
    ]);
    
    AppointmentSlot::create([
        'date' => $validated['date'],
        'total_capacity' => $validated['total_capacity'],
        'booked_count' => 0,
        'available_count' => $validated['total_capacity'],
    ]);
    
    return redirect()->back()->with('success', 'Slot created successfully');
}

}
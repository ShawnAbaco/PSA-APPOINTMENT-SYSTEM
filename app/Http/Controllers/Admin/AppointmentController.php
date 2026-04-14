<?php
// app/Http/Controllers/Admin/AppointmentController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentClient;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with('clients', 'processedBy');
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('appointment_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('appointment_date', '<=', $request->date_to);
        }
        
        $appointments = $query->latest()->paginate(20);
        $statuses = ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'];
        
        return view('admin.appointments.index', compact('appointments', 'statuses'));
    }
    
    public function show($id)
    {
        $appointment = Appointment::with('clients', 'processedBy', 'createdBy')->findOrFail($id);
        return view('admin.appointments.show', compact('appointment'));
    }
    
    public function updateStatus(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update([
            'status' => $request->status,
            'processed_by' => auth()->id(),
            $request->status . '_at' => now()
        ]);
        
        return redirect()->back()->with('success', 'Appointment status updated successfully.');
    }
    
    public function calendar()
    {
        $appointments = Appointment::with('clients')
            ->whereIn('status', ['confirmed', 'pending'])
            ->get();
            
        return view('admin.appointments.calendar', compact('appointments'));
    }
    
    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();
        
        return redirect()->route('admin.appointments.index')->with('success', 'Appointment deleted successfully.');
    }
}
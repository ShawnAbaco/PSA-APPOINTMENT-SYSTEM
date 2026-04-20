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
    // PSA Misamis Oriental Coordinates (from landing-page.js)
    const PSA_LAT = 8.4815315;
    const PSA_LNG = 124.6549067;

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
        
        // Filter by city
        if ($request->filled('city')) {
            $query->where('user_city', 'like', '%' . $request->city . '%');
        }
        
        $appointments = $query->latest()->paginate(20);
        $statuses = ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'];
        
        // Get unique cities for filter dropdown
        $cities = Appointment::whereNotNull('user_city')
            ->select('user_city')
            ->distinct()
            ->pluck('user_city');
        
        return view('admin.appointments.index', compact('appointments', 'statuses', 'cities'));
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
    
    // ========== NEW METHODS ==========
    
    public function getByLocation(Request $request)
    {
        $query = Appointment::with('clients');
        
        if ($request->filled('start_date')) {
            $query->whereDate('appointment_date', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('appointment_date', '<=', $request->end_date);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $appointments = $query->whereNotNull('user_lat')
            ->whereNotNull('user_lng')
            ->get(['id', 'user_lat', 'user_lng', 'user_city', 'user_address', 'status', 'appointment_date']);
        
        return response()->json($appointments);
    }
    
    public function cityStatistics(Request $request)
    {
        $query = Appointment::query();
        
        if ($request->filled('start_date')) {
            $query->whereDate('appointment_date', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('appointment_date', '<=', $request->end_date);
        }
        
        $stats = $query->whereNotNull('user_city')
            ->selectRaw('user_city, COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending')
            ->selectRaw('SUM(CASE WHEN status = "confirmed" THEN 1 ELSE 0 END) as confirmed')
            ->selectRaw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
            ->selectRaw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled')
            ->groupBy('user_city')
            ->orderBy('total', 'desc')
            ->get();
        
        return response()->json($stats);
    }
    
    public function getPsaCoordinates()
    {
        return response()->json([
            'lat' => self::PSA_LAT,
            'lng' => self::PSA_LNG,
            'address' => 'Capt. Vicente Roa Street, Brgy. 31, Cagayan de Oro City, 9000 Misamis Oriental, Philippines'
        ]);
    }
}
<?php
// app/Http/Controllers/Staff/AppointmentController.php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentClient;
use App\Models\Service;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with('clients');
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }
        
        $appointments = $query->latest()->paginate(20);
        $statuses = ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'];
        
        return view('staff.appointments.index', compact('appointments', 'statuses'));
    }
    
    public function show($id)
    {
        $appointment = Appointment::with('clients')->findOrFail($id);
        return view('staff.appointments.show', compact('appointment'));
    }
    
    public function create()
    {
        $services = Service::where('is_active', true)->get();
        return view('staff.appointments.create', compact('services'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'appointment_date' => 'required|date',
            'contact_name' => 'required',
            'contact_mobile' => 'required',
            'contact_email' => 'nullable|email',
            'clients' => 'required|array',
            'clients.*.first_name' => 'required',
            'clients.*.last_name' => 'required',
            'clients.*.sex' => 'required',
            'clients.*.birthdate' => 'required|date',
            'clients.*.service' => 'required',
        ]);
        
        $appointment = Appointment::create([
            'appointment_number' => $this->generateAppointmentNumber(),
            'type' => count($validated['clients']) > 1 ? 'multiple' : 'single',
            'appointment_date' => $validated['appointment_date'],
            'contact_name' => $validated['contact_name'],
            'contact_email' => $validated['contact_email'],
            'contact_mobile' => $validated['contact_mobile'],
            'reference_code' => $this->generateReferenceCode(),
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'created_by' => auth()->id(),
            'processed_by' => auth()->id(),
        ]);
        
        foreach ($validated['clients'] as $client) {
            AppointmentClient::create([
                'appointment_id' => $appointment->id,
                'first_name' => $client['first_name'],
                'middle_name' => $client['middle_name'] ?? null,
                'last_name' => $client['last_name'],
                'suffix' => $client['suffix'] ?? null,
                'sex' => $client['sex'],
                'birthdate' => $client['birthdate'],
                'service' => $client['service'],
                'requirements_acknowledged' => true,
                'acknowledged_at' => now(),
            ]);
        }
        
        return redirect()->route('staff.appointments.show', $appointment->id)
            ->with('success', 'Appointment created successfully.');
    }
    
    public function confirm($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'processed_by' => auth()->id(),
        ]);
        
        return redirect()->back()->with('success', 'Appointment confirmed.');
    }
    
    public function cancel(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->reason,
            'processed_by' => auth()->id(),
        ]);
        
        return redirect()->back()->with('success', 'Appointment cancelled.');
    }
    
    public function complete($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update([
            'status' => 'completed',
            'completed_at' => now(),
            'processed_by' => auth()->id(),
        ]);
        
        return redirect()->back()->with('success', 'Appointment marked as completed.');
    }
    
    private function generateAppointmentNumber()
    {
        $date = Carbon::now()->format('Ymd');
        $last = Appointment::whereDate('created_at', Carbon::today())->count() + 1;
        return "PSA-{$date}-" . str_pad($last, 5, '0', STR_PAD_LEFT);
    }
    
    private function generateReferenceCode()
    {
        return 'REF-' . strtoupper(uniqid());
    }
}
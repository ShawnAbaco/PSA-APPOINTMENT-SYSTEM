<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentClient;
use App\Models\TimeSlot;
use App\Models\SlotCapacityOverride;
use App\Models\WorkingDaysDefault;
use App\Models\WorkingDaysOverride;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OAppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with('clients', 'timeSlot');
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('appointment_number', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%")
                  ->orWhere('contact_mobile', 'like', "%{$search}%");
            });
        }
        
        // Week filter
        if ($request->filled('week_filter')) {
            $today = Carbon::today();
            switch ($request->week_filter) {
                case 'today':
                    $query->whereDate('appointment_date', $today);
                    break;
                case 'tomorrow':
                    $query->whereDate('appointment_date', $today->copy()->addDay());
                    break;
                case 'this_week':
                    $query->whereBetween('appointment_date', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()]);
                    break;
                case 'next_week':
                    $query->whereBetween('appointment_date', [$today->copy()->addWeek()->startOfWeek(), $today->copy()->addWeek()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('appointment_date', $today->month)
                          ->whereYear('appointment_date', $today->year);
                    break;
            }
        }
        
        $appointments = $query->orderBy('appointment_date', 'desc')
            ->orderBy('time_slot_id', 'asc')
            ->paginate($request->get('per_page', 15));
        
        if ($request->ajax()) {
            return response()->json([
                'html' => view('operator.appointments.partials.table', compact('appointments'))->render(),
                'total' => $appointments->total(),
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
            ]);
        }
        
        return view('operator.appointments.index', compact('appointments'));
    }
    
    public function create()
    {
        $timeSlots = TimeSlot::where('is_active', true)
            ->orderBy('display_order')
            ->get();
        
        return view('operator.appointments.create', compact('timeSlots'));
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_name' => 'required|string|max:255',
            'contact_mobile' => 'required|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'appointment_date' => 'required|date|after_or_equal:today',
            'time_slot_id' => 'required|exists:time_slots,id',
            'clients' => 'required|array|min:1|max:10',
            'clients.*.first_name' => 'required|string|max:255',
            'clients.*.last_name' => 'required|string|max:255',
            'clients.*.sex' => 'required|in:Male,Female',
            'clients.*.birthdate' => 'required|date|before:today',
            'clients.*.service' => 'required|in:reg,updating,inquiry',
            'clients.*.has_trn' => 'nullable|boolean',
            'clients.*.trn_number' => 'nullable|string|size:29|regex:/^\d+$/',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Check if the date is a working day
        if (!$this->isWorkingDay($request->appointment_date)) {
            return redirect()->back()
                ->with('error', 'Cannot book appointments on non-working days.')
                ->withInput();
        }
        
        // Check capacity for the selected time slot
        $capacityCheck = $this->checkCapacity($request->appointment_date, $request->time_slot_id, $request->clients);
        if (!$capacityCheck['available']) {
            return redirect()->back()
                ->with('error', $capacityCheck['message'])
                ->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            // Generate appointment number
            $date = Carbon::now()->format('Ymd');
            $last = Appointment::whereDate('created_at', Carbon::today())->count() + 1;
            $appointmentNumber = 'PSA-' . $date . '-' . str_pad($last, 5, '0', STR_PAD_LEFT);
            $referenceCode = 'REF-' . strtoupper(uniqid());
            
            // Create appointment
            $appointment = new Appointment();
            $appointment->appointment_number = $appointmentNumber;
            $appointment->type = count($request->clients) > 1 ? 'multiple' : 'single';
            $appointment->appointment_date = $request->appointment_date;
            $appointment->time_slot_id = $request->time_slot_id;
            $appointment->contact_name = $request->contact_name;
            $appointment->contact_email = $request->contact_email;
            $appointment->contact_mobile = $request->contact_mobile;
            $appointment->reference_code = $referenceCode;
            $appointment->status = 'pending';
            $appointment->created_by = auth()->id();
            $appointment->save();
            
            // Create clients
            foreach ($request->clients as $clientData) {
                $client = new AppointmentClient();
                $client->client_number = $this->generateClientNumber();
                $client->appointment_id = $appointment->id;
                $client->first_name = $clientData['first_name'];
                $client->middle_name = $clientData['middle_name'] ?? null;
                $client->last_name = $clientData['last_name'];
                $client->suffix = $clientData['suffix'] ?? null;
                $client->sex = $clientData['sex'];
                $client->birthdate = $clientData['birthdate'];
                $client->service = $clientData['service'];
                $client->requirements_acknowledged = true;
                $client->acknowledged_at = now();
                
                if ($clientData['service'] === 'inquiry') {
                    $client->has_trn = $clientData['has_trn'] ?? null;
                    $client->trn_number = ($clientData['has_trn'] ?? false) ? ($clientData['trn_number'] ?? null) : null;
                }
                
                $client->save();
            }
            
            DB::commit();
            
            return redirect()->route('operator.appointments.index')
                ->with('success', 'Appointment created successfully!');
            
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Appointment creation failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to create appointment: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function show($id)
    {
        $appointment = Appointment::with(['clients', 'timeSlot', 'createdBy'])
            ->findOrFail($id);
        
        return view('operator.appointments.show', compact('appointment'));
    }
    
    public function edit($id)
    {
        $appointment = Appointment::with(['clients', 'timeSlot'])->findOrFail($id);
        $timeSlots = TimeSlot::where('is_active', true)
            ->orderBy('display_order')
            ->get();
        
        return view('operator.appointments.edit', compact('appointment', 'timeSlots'));
    }
    
    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'contact_name' => 'required|string|max:255',
            'contact_mobile' => 'required|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'appointment_date' => 'required|date',
            'time_slot_id' => 'required|exists:time_slots,id',
            'status' => 'required|in:pending,confirmed,cancelled,completed,no_show',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // If date or time slot changed, check capacity
        if ($appointment->appointment_date != $request->appointment_date || 
            $appointment->time_slot_id != $request->time_slot_id) {
            
            $clients = $appointment->clients->toArray();
            $capacityCheck = $this->checkCapacity($request->appointment_date, $request->time_slot_id, $clients);
            
            if (!$capacityCheck['available']) {
                return redirect()->back()
                    ->with('error', $capacityCheck['message'])
                    ->withInput();
            }
        }
        
        $appointment->contact_name = $request->contact_name;
        $appointment->contact_mobile = $request->contact_mobile;
        $appointment->contact_email = $request->contact_email;
        $appointment->appointment_date = $request->appointment_date;
        $appointment->time_slot_id = $request->time_slot_id;
        $appointment->status = $request->status;
        $appointment->save();
        
        return redirect()->route('operator.appointments.show', $appointment->id)
            ->with('success', 'Appointment updated successfully!');
    }
    
    public function confirm($id)
    {
        $appointment = Appointment::findOrFail($id);
        
        if ($appointment->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending appointments can be confirmed.');
        }
        
        $appointment->status = 'confirmed';
        $appointment->confirmed_at = now();
        $appointment->processed_by = auth()->id();
        $appointment->save();
        
        return redirect()->back()
            ->with('success', 'Appointment confirmed successfully!');
    }
    
    public function cancel($id)
    {
        $appointment = Appointment::findOrFail($id);
        
        if (!in_array($appointment->status, ['pending', 'confirmed'])) {
            return redirect()->back()
                ->with('error', 'Only pending or confirmed appointments can be cancelled.');
        }
        
        $appointment->status = 'cancelled';
        $appointment->cancelled_at = now();
        $appointment->cancellation_reason = request('reason', 'Cancelled by operator');
        $appointment->processed_by = auth()->id();
        $appointment->save();
        
        return redirect()->back()
            ->with('success', 'Appointment cancelled successfully!');
    }
    
    public function complete($id)
    {
        $appointment = Appointment::findOrFail($id);
        
        if ($appointment->status !== 'confirmed') {
            return redirect()->back()
                ->with('error', 'Only confirmed appointments can be marked as completed.');
        }
        
        $appointment->status = 'completed';
        $appointment->completed_at = now();
        $appointment->processed_by = auth()->id();
        $appointment->save();
        
        return redirect()->back()
            ->with('success', 'Appointment marked as completed!');
    }
    
    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        
        // Check if appointment can be deleted (only cancelled or old appointments)
        if (!in_array($appointment->status, ['cancelled', 'completed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only cancelled or completed appointments can be deleted.'
            ], 400);
        }
        
        $appointment->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Appointment deleted successfully.'
        ]);
    }
    
    /**
     * Check if a date is a working day
     */
    private function isWorkingDay($date)
    {
        $carbonDate = Carbon::parse($date);
        
        // Check override first
        $override = WorkingDaysOverride::where('date', $carbonDate->format('Y-m-d'))->first();
        if ($override) {
            return $override->is_working;
        }
        
        // Check default
        $dayOfWeek = $carbonDate->dayOfWeek == 0 ? 7 : $carbonDate->dayOfWeek;
        $default = WorkingDaysDefault::where('day_of_week', $dayOfWeek)->first();
        
        return $default ? $default->is_working : false;
    }
    
    /**
     * Check capacity for a specific date, time slot, and clients
     */
    private function checkCapacity($date, $timeSlotId, $clients)
    {
        // Group clients by service
        $clientsByService = [];
        foreach ($clients as $client) {
            $service = $client['service'];
            if (!isset($clientsByService[$service])) {
                $clientsByService[$service] = 0;
            }
            $clientsByService[$service]++;
        }
        
        // Get capacity from override or calculate from rules
        $override = SlotCapacityOverride::where('date', $date)
            ->where('time_slot_id', $timeSlotId)
            ->first();
        
        if ($override) {
            $capacities = [
                'reg' => $override->reg_capacity ?? 0,
                'updating' => $override->updating_capacity ?? 0,
                'inquiry' => $override->inquiry_capacity ?? 0,
            ];
        } else {
            // Get day type and default rules
            $dayType = $this->getDayTypeForDate($date);
            
            $rule = DB::table('slot_capacity_rules')
                ->where('time_slot_id', $timeSlotId)
                ->where('day_type', $dayType)
                ->first();
            
            if ($rule) {
                $capacities = [
                    'reg' => $rule->reg_capacity ?? 0,
                    'updating' => $rule->updating_capacity ?? 0,
                    'inquiry' => $rule->inquiry_capacity ?? 0,
                ];
            } else {
                $capacities = ['reg' => 0, 'updating' => 0, 'inquiry' => 0];
            }
        }
        
        // Get current booked counts
        $bookedCounts = AppointmentClient::whereHas('appointment', function($query) use ($date, $timeSlotId) {
            $query->whereDate('appointment_date', $date)
                  ->where('time_slot_id', $timeSlotId)
                  ->whereIn('status', ['pending', 'confirmed']);
        })
        ->selectRaw('service, COUNT(*) as count')
        ->groupBy('service')
        ->pluck('count', 'service')
        ->toArray();
        
        // Check each service
        foreach ($clientsByService as $service => $needed) {
            $capacity = $capacities[$service] ?? 0;
            $booked = $bookedCounts[$service] ?? 0;
            $available = $capacity - $booked;
            
            if ($available < $needed) {
                $serviceNames = [
                    'reg' => 'Registration',
                    'updating' => 'Correction/Updating',
                    'inquiry' => 'Status Inquiry'
                ];
                return [
                    'available' => false,
                    'message' => "Not enough capacity for {$serviceNames[$service]}. Only {$available} slots available, you need {$needed}."
                ];
            }
        }
        
        return ['available' => true, 'message' => ''];
    }
    
    /**
     * Get day type for a specific date
     */
    private function getDayTypeForDate($date)
    {
        $carbonDate = Carbon::parse($date);
        
        // Check override first
        $override = WorkingDaysOverride::where('date', $carbonDate->format('Y-m-d'))->first();
        if ($override) {
            return $override->is_working ? 'weekday' : 'holiday';
        }
        
        // Check default
        $dayOfWeek = $carbonDate->dayOfWeek;
        $default = WorkingDaysDefault::where('day_of_week', $dayOfWeek)->first();
        
        if (!$default || !$default->is_working) {
            if ($dayOfWeek == 6) return 'saturday';
            if ($dayOfWeek == 0) return 'sunday';
            return 'holiday';
        }
        
        return 'weekday';
    }
    
    /**
     * Generate unique client number
     */
    private function generateClientNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $last = AppointmentClient::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;
        
        return 'CLN-' . $year . $month . '-' . str_pad($last, 5, '0', STR_PAD_LEFT);
    }
}
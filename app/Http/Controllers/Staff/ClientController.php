<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AppointmentClient;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    /**
     * Display a listing of all clients.
     */
        public function index(Request $request)
    {
        $query = AppointmentClient::with('appointment.timeSlot');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('psa_reference_number', 'like', "%{$search}%")
                  ->orWhere('trn_number', 'like', "%{$search}%");
            });
        }
        
        // Filter by service (UPDATED: reg, updating, inquiry)
        if ($request->filled('service')) {
            $query->where('service', $request->service);
        }
        
        // Filter by sex
        if ($request->filled('sex')) {
            $query->where('sex', $request->sex);
        }
        
        // Filter by verification status
        if ($request->filled('verified')) {
            $query->where('is_verified', $request->verified === 'true');
        }
        
        // Order by latest first
        $clients = $query->latest()->paginate(10);
        
        // Get service counts for statistics (UPDATED service codes)
        $serviceCounts = AppointmentClient::selectRaw('service, COUNT(*) as count')
            ->groupBy('service')
            ->pluck('count', 'service');
        
        $totalClients = AppointmentClient::count();
        $verifiedClients = AppointmentClient::where('is_verified', true)->count();
        
        // Updated service mapping
        $services = [
            'reg' => 'National ID Registration',
            'updating' => 'Correction/Updating',
            'inquiry' => 'Status Inquiry / TRN Retrieval'
        ];
        
        return view('staff.clients.index', compact(
            'clients', 
            'serviceCounts', 
            'totalClients', 
            'verifiedClients',
            'services'
        ));
    }
    
    /**
     * Display client details in modal via AJAX.
     */
    public function showModal($id)
    {
        $client = AppointmentClient::with('appointment.timeSlot')->findOrFail($id);
        
        // Get client's appointment history
        $clientHistory = AppointmentClient::with('appointment.timeSlot')
            ->where('first_name', $client->first_name)
            ->where('last_name', $client->last_name)
            ->where('birthdate', $client->birthdate)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $services = [
            'reg' => 'National ID Registration',
            'updating' => 'Correction/Updating',
            'inquiry' => 'Status Inquiry / TRN Retrieval'
        ];
        
        // Return only the content without layout for modal
        return view('staff.clients.show', compact('client', 'clientHistory', 'services'));
    }
    
    /**
     * Search clients via AJAX.
     */
    public function search(Request $request)
    {
        $search = $request->get('q');
        
        $clients = AppointmentClient::where('first_name', 'like', "%{$search}%")
            ->orWhere('last_name', 'like', "%{$search}%")
            ->orWhere('psa_reference_number', 'like', "%{$search}%")
            ->orWhere('trn_number', 'like', "%{$search}%")
            ->limit(10)
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'suffix', 'psa_reference_number', 'trn_number']);
        
        return response()->json($clients);
    }
    
    /**
     * Update client information.
     */
    public function update(Request $request, $id)
    {
        $client = AppointmentClient::findOrFail($id);
        
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'sex' => 'required|in:Male,Female',
            'birthdate' => 'required|date',
            'service' => 'required|in:reg,updating,inquiry',
            'psa_reference_number' => 'nullable|string|max:255',
            'has_trn' => 'nullable|boolean',
            'trn_number' => 'nullable|string|size:29|regex:/^\d+$/',
        ]);
        
        // Validate TRN if provided and service is inquiry
        if ($validated['service'] === 'inquiry' && !empty($validated['trn_number'])) {
            if (!preg_match('/^\d{29}$/', $validated['trn_number'])) {
                return redirect()->back()
                    ->with('error', 'TRN number must be exactly 29 digits.')
                    ->withInput();
            }
            $validated['has_trn'] = true;
        } elseif ($validated['service'] === 'inquiry') {
            $validated['has_trn'] = $request->has('has_trn') ? true : false;
            if (!$validated['has_trn']) {
                $validated['trn_number'] = null;
            }
        } else {
            $validated['has_trn'] = null;
            $validated['trn_number'] = null;
        }
        
        $client->update($validated);
        
        return redirect()->back()->with('success', 'Client information updated successfully.');
    }
    
    /**
     * Verify client via AJAX
     */
    public function verify($id)
    {
        try {
            $client = AppointmentClient::findOrFail($id);
            
            $client->update([
                'is_verified' => true,
                'verified_at' => now(),
            ]);
            
            // Update appointment status if pending
            if ($client->appointment && $client->appointment->status === 'pending') {
                $client->appointment->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                    'processed_by' => Auth::id(),
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Client verified successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify client: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update reference number via AJAX
     */
    public function updateReferenceNumber(Request $request, $id)
    {
        try {
            $request->validate([
                'psa_reference_number' => 'required|string|max:255',
            ]);
            
            $client = AppointmentClient::findOrFail($id);
            $client->update([
                'psa_reference_number' => $request->psa_reference_number,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Reference number updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update reference number: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update TRN number for inquiry clients.
     */
    public function updateTrnNumber(Request $request, $id)
    {
        $request->validate([
            'trn_number' => 'required|string|size:29|regex:/^\d+$/',
        ]);
        
        $client = AppointmentClient::findOrFail($id);
        
        if ($client->service !== 'inquiry') {
            return response()->json([
                'success' => false,
                'message' => 'TRN numbers can only be added for inquiry service clients.'
            ], 400);
        }
        
        $client->update([
            'has_trn' => true,
            'trn_number' => $request->trn_number,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'TRN number updated successfully.'
        ]);
    }
    
    /**
     * Export clients to CSV.
     */
    public function export(Request $request)
    {
        $clients = AppointmentClient::with('appointment.timeSlot')->get();
        
        $filename = 'clients_export_' . date('Y-m-d_His') . '.csv';
        
        return response()->stream(
            function() use ($clients) {
                $handle = fopen('php://output', 'w');
                
                // Add CSV headers (UPDATED)
                fputcsv($handle, [
                    'ID', 'Client Number', 'First Name', 'Middle Name', 'Last Name', 'Suffix', 
                    'Sex', 'Birthdate', 'Service', 'Has TRN', 'TRN Number', 'PSA Reference Number', 
                    'Is Verified', 'Verified At', 'Appointment Number', 'Appointment Date', 
                    'Appointment Time', 'Appointment Status', 'Created At'
                ]);
                
                // Service name mapping
                $serviceNames = [
                    'reg' => 'National ID Registration',
                    'updating' => 'Correction/Updating',
                    'inquiry' => 'Status Inquiry / TRN Retrieval'
                ];
                
                foreach ($clients as $client) {
                    $timeSlotLabel = $client->appointment?->timeSlot?->label ?? 'N/A';
                    
                    fputcsv($handle, [
                        $client->id,
                        $client->client_number,
                        $client->first_name,
                        $client->middle_name,
                        $client->last_name,
                        $client->suffix,
                        $client->sex,
                        $client->birthdate,
                        $serviceNames[$client->service] ?? $client->service,
                        $client->has_trn ? 'Yes' : 'No',
                        $client->trn_number,
                        $client->psa_reference_number,
                        $client->is_verified ? 'Yes' : 'No',
                        $client->verified_at,
                        $client->appointment?->appointment_number,
                        $client->appointment?->appointment_date,
                        $timeSlotLabel,
                        $client->appointment?->status,
                        $client->created_at,
                    ]);
                }
                
                fclose($handle);
            },
            200,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
    
    /**
     * Get client statistics for dashboard.
     */
    public function statistics()
    {
        $totalClients = AppointmentClient::count();
        $verifiedClients = AppointmentClient::where('is_verified', true)->count();
        $unverifiedClients = $totalClients - $verifiedClients;
        
        // Clients with TRN (for inquiry service)
        $clientsWithTrn = AppointmentClient::where('has_trn', true)->count();
        
        // Clients by service (UPDATED)
        $clientsByService = AppointmentClient::selectRaw('service, COUNT(*) as count')
            ->groupBy('service')
            ->get()
            ->map(function($item) {
                $services = [
                    'reg' => 'Registration',
                    'updating' => 'Correction/Updating',
                    'inquiry' => 'Status Inquiry'
                ];
                $item->service_name = $services[$item->service] ?? $item->service;
                return $item;
            });
        
        // Clients by sex
        $clientsBySex = AppointmentClient::selectRaw('sex, COUNT(*) as count')
            ->groupBy('sex')
            ->get();
        
        // Recent clients
        $recentClients = AppointmentClient::with('appointment.timeSlot')
            ->latest()
            ->take(10)
            ->get();
        
        return response()->json([
            'total_clients' => $totalClients,
            'verified_clients' => $verifiedClients,
            'unverified_clients' => $unverifiedClients,
            'clients_with_trn' => $clientsWithTrn,
            'by_service' => $clientsByService,
            'by_sex' => $clientsBySex,
            'recent_clients' => $recentClients,
        ]);
    }
    
    /**
     * Delete a client record.
     */
    public function destroy($id)
    {
        $client = AppointmentClient::findOrFail($id);
        
        // Check if client has an appointment
        if ($client->appointment) {
            return redirect()->back()->with('error', 
                'Cannot delete client because they have an associated appointment. Delete the appointment first.'
            );
        }
        
        $client->delete();
        
        return redirect()->route('staff.clients.index')->with('success', 'Client deleted successfully.');
    }
    
    /**
     * Get client details for AJAX request.
     */
    public function getClientDetails($id)
    {
        $client = AppointmentClient::with('appointment.timeSlot')->findOrFail($id);
        
        $services = [
            'reg' => 'National ID Registration',
            'updating' => 'Correction/Updating',
            'inquiry' => 'Status Inquiry / TRN Retrieval'
        ];
        
        return response()->json([
            'id' => $client->id,
            'client_number' => $client->client_number,
            'first_name' => $client->first_name,
            'middle_name' => $client->middle_name,
            'last_name' => $client->last_name,
            'suffix' => $client->suffix,
            'full_name' => $client->full_name,
            'sex' => $client->sex,
            'birthdate' => $client->birthdate ? $client->birthdate->format('Y-m-d') : null,
            'birthdate_formatted' => $client->birthdate ? $client->birthdate->format('F d, Y') : null,
            'service' => $client->service,
            'service_name' => $services[$client->service] ?? $client->service,
            'has_trn' => $client->has_trn,
            'trn_number' => $client->trn_number,
            'psa_reference_number' => $client->psa_reference_number,
            'is_verified' => $client->is_verified,
            'verified_at' => $client->verified_at ? $client->verified_at->format('F d, Y h:i A') : null,
            'requirements_acknowledged' => $client->requirements_acknowledged,
            'acknowledged_at' => $client->acknowledged_at ? $client->acknowledged_at->format('F d, Y h:i A') : null,
            'appointment' => $client->appointment ? [
                'id' => $client->appointment->id,
                'number' => $client->appointment->appointment_number,
                'date' => $client->appointment->appointment_date ? $client->appointment->appointment_date->format('F d, Y') : null,
                'time' => $client->appointment->timeSlot?->label ?? 'N/A',
                'status' => $client->appointment->status,
            ] : null,
            'created_at' => $client->created_at ? $client->created_at->format('F d, Y h:i A') : null,
        ]);
    }
    
    /**
     * Bulk verify clients.
     */
    public function bulkVerify(Request $request)
    {
        $request->validate([
            'client_ids' => 'required|array',
            'client_ids.*' => 'exists:appointment_clients,id',
        ]);
        
        $count = AppointmentClient::whereIn('id', $request->client_ids)
            ->where('is_verified', false)
            ->update([
                'is_verified' => true,
                'verified_at' => now(),
            ]);
        
        return response()->json([
            'success' => true,
            'message' => "{$count} client(s) verified successfully."
        ]);
    }
}
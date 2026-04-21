<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AppointmentClient;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    /**
     * Display a listing of all clients.
     */
    public function index(Request $request)
    {
        $query = AppointmentClient::with('appointment');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('psa_reference_number', 'like', "%{$search}%");
            });
        }
        
        // Filter by service
        if ($request->filled('service')) {
            $query->where('service', $request->service);
        }
        
        // Filter by sex
        if ($request->filled('sex')) {
            $query->where('sex', $request->sex);
        }
        
        // Order by latest first
        $clients = $query->latest()->paginate(10);
        
        // Get service counts for statistics
        $serviceCounts = AppointmentClient::selectRaw('service, COUNT(*) as count')
            ->groupBy('service')
            ->pluck('count', 'service');
        
        $totalClients = AppointmentClient::count();
        $verifiedClients = AppointmentClient::where('is_verified', true)->count();
        
        $services = [
            'reg' => 'National ID Registration',
            'correction' => 'Correction/Updating',
            'ephilid' => 'ePhilID Issuance',
            'trn' => 'TRN Retrieval'
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
     * Display the specified client details.
     */
    public function show($id)
    {
        $client = AppointmentClient::with('appointment')->findOrFail($id);
        
        // Get client's appointment history (all appointments for this person)
        $clientHistory = AppointmentClient::with('appointment')
            ->where('first_name', $client->first_name)
            ->where('last_name', $client->last_name)
            ->where('birthdate', $client->birthdate)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get service name mapping
        $services = [
            'reg' => 'National ID Registration',
            'correction' => 'Correction/Updating of Demographic Information',
            'ephilid' => 'Issuance of National ID Paper Form (ePhilID)',
            'trn' => 'Retrieval of TRN / Other Concern'
        ];
        
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
            ->limit(10)
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'suffix', 'psa_reference_number']);
        
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
            'service' => 'required|in:reg,correction,ephilid,trn',
            'psa_reference_number' => 'nullable|string|max:255',
        ]);
        
        $client->update($validated);
        
        return redirect()->back()->with('success', 'Client information updated successfully.');
    }
    
    /**
     * Verify client (mark as verified).
     */
    public function verify($id)
    {
        $client = AppointmentClient::findOrFail($id);
        
        $client->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);
        
        // Log activity
        $client->appointment?->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'processed_by' => Auth::id(),
        ]);
        
        return redirect()->back()->with('success', 'Client verified successfully.');
    }
    
    /**
     * Update PSA reference number (TRN).
     */
    public function updateReferenceNumber(Request $request, $id)
    {
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
    }
    
    /**
     * Export clients to CSV.
     */
    public function export(Request $request)
    {
        $clients = AppointmentClient::with('appointment')->get();
        
        $filename = 'clients_export_' . date('Y-m-d_His') . '.csv';
        $handle = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($handle, [
            'ID', 'First Name', 'Middle Name', 'Last Name', 'Suffix', 
            'Sex', 'Birthdate', 'Service', 'PSA Reference Number', 
            'Is Verified', 'Verified At', 'Appointment Number', 
            'Appointment Date', 'Created At'
        ]);
        
        // Add data rows
        foreach ($clients as $client) {
            fputcsv($handle, [
                $client->id,
                $client->first_name,
                $client->middle_name,
                $client->last_name,
                $client->suffix,
                $client->sex,
                $client->birthdate,
                $client->service_name,
                $client->psa_reference_number,
                $client->is_verified ? 'Yes' : 'No',
                $client->verified_at,
                $client->appointment?->appointment_number,
                $client->appointment?->appointment_date,
                $client->created_at,
            ]);
        }
        
        fclose($handle);
        
        return response()->stream(
            function() use ($clients) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, [
                    'ID', 'First Name', 'Middle Name', 'Last Name', 'Suffix', 
                    'Sex', 'Birthdate', 'Service', 'PSA Reference Number', 
                    'Is Verified', 'Verified At', 'Appointment Number', 
                    'Appointment Date', 'Created At'
                ]);
                
                foreach ($clients as $client) {
                    fputcsv($handle, [
                        $client->id,
                        $client->first_name,
                        $client->middle_name,
                        $client->last_name,
                        $client->suffix,
                        $client->sex,
                        $client->birthdate,
                        $client->service_name,
                        $client->psa_reference_number,
                        $client->is_verified ? 'Yes' : 'No',
                        $client->verified_at,
                        $client->appointment?->appointment_number,
                        $client->appointment?->appointment_date,
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
        
        $clientsByService = AppointmentClient::selectRaw('service, COUNT(*) as count')
            ->groupBy('service')
            ->get()
            ->map(function($item) {
                $services = [
                    'reg' => 'Registration',
                    'correction' => 'Correction',
                    'ephilid' => 'ePhilID',
                    'trn' => 'TRN Retrieval'
                ];
                $item->service_name = $services[$item->service] ?? $item->service;
                return $item;
            });
        
        $clientsBySex = AppointmentClient::selectRaw('sex, COUNT(*) as count')
            ->groupBy('sex')
            ->get();
        
        $recentClients = AppointmentClient::with('appointment')
            ->latest()
            ->take(10)
            ->get();
        
        return response()->json([
            'total_clients' => $totalClients,
            'verified_clients' => $verifiedClients,
            'unverified_clients' => $unverifiedClients,
            'by_service' => $clientsByService,
            'by_sex' => $clientsBySex,
            'recent_clients' => $recentClients,
        ]);
    }
    
    /**
     * Delete a client record (soft delete or permanent).
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
        $client = AppointmentClient::with('appointment')->findOrFail($id);
        
        return response()->json([
            'id' => $client->id,
            'first_name' => $client->first_name,
            'middle_name' => $client->middle_name,
            'last_name' => $client->last_name,
            'suffix' => $client->suffix,
            'full_name' => $client->full_name,
            'sex' => $client->sex,
            'birthdate' => $client->birthdate->format('Y-m-d'),
            'birthdate_formatted' => $client->birthdate->format('F d, Y'),
            'service' => $client->service,
            'service_name' => $client->service_name,
            'psa_reference_number' => $client->psa_reference_number,
            'is_verified' => $client->is_verified,
            'verified_at' => $client->verified_at ? $client->verified_at->format('F d, Y h:i A') : null,
            'appointment' => $client->appointment ? [
                'id' => $client->appointment->id,
                'number' => $client->appointment->appointment_number,
                'date' => $client->appointment->appointment_date->format('F d, Y'),
                'status' => $client->appointment->status,
            ] : null,
            'created_at' => $client->created_at->format('F d, Y h:i A'),
        ]);
    }
}
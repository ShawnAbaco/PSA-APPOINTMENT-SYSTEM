<?php
// app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        // Get pending accounts count
        $pendingCount = User::where('account_status', 'pending')->count();
        $users = User::with('creator', 'approver')->latest()->paginate(15);
        return view('admin.users.index', compact('users', 'pendingCount'));
    }
    
    public function create()
    {
        return view('admin.users.create');
    }
    
    /**
     * Generate employee ID based on role
     * Format: PSA-{ROLE}-{NUMBER}
     * Roles: ADMIN, STAFF, OPERATOR
     */
    private function generateEmployeeId($role)
    {
        // If role is 'user', return null (no employee ID)
        if ($role === 'user') {
            return null;
        }
        
        // Map role to code
        $roleCode = '';
        switch ($role) {
            case 'admin':
                $roleCode = 'ADMIN';
                break;
            case 'staff':
                $roleCode = 'STAFF';
                break;
            case 'operator':
                $roleCode = 'OPERATOR';
                break;
            default:
                $roleCode = 'OPERATOR';
                break;
        }
        
        // Get the latest employee ID for this role
        $prefix = "PSA-{$roleCode}-";
        $latestUser = User::where('role', $role)
            ->where('employee_id', 'LIKE', $prefix . '%')
            ->orderBy('employee_id', 'desc')
            ->first();
        
        if ($latestUser && $latestUser->employee_id) {
            // Extract the number from the existing ID
            $lastNumber = intval(substr($latestUser->employee_id, strlen($prefix)));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        // Format with leading zeros (3 digits)
        $formattedNumber = str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        
        return $prefix . $formattedNumber;
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'first_name' => 'required',
            'last_name' => 'required',
            'role' => 'required|in:admin,staff,operator,user',
            'password' => 'required|min:6',
            'contact_number' => 'nullable',
        ]);
        
        // Generate employee ID based on role
        $employeeId = $this->generateEmployeeId($validated['role']);
        
        $validated['password'] = Hash::make($validated['password']);
        $validated['created_by'] = auth()->id();
        $validated['is_active'] = true; // Admin created accounts are active by default
        $validated['account_status'] = 'approved'; // Admin created accounts are approved by default
        $validated['employee_id'] = $employeeId;
        
        $user = User::create($validated);
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true, 
                'message' => 'User created successfully.',
                'user' => $user
            ]);
        }
        
        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }
    
    public function edit($id)
    {
        $user = User::findOrFail($id);
        
        if (request()->wantsJson()) {
            return response()->json($user);
        }
        
        return view('admin.users.edit', compact('user'));
    }
    
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'username' => 'required|unique:users,username,' . $id,
            'email' => 'required|email|unique:users,email,' . $id,
            'first_name' => 'required',
            'last_name' => 'required',
            'role' => 'required|in:admin,staff,operator,user',
            'contact_number' => 'nullable',
            'is_active' => 'boolean',
        ]);
        
        // Check if role changed and regenerate employee ID if needed
        $oldRole = $user->role;
        $newRole = $validated['role'];
        
        if ($oldRole !== $newRole) {
            // Role changed, generate new employee ID based on new role
            $validated['employee_id'] = $this->generateEmployeeId($newRole);
        } else {
            // Keep existing employee ID
            $validated['employee_id'] = $user->employee_id;
        }
        
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }
        
        $validated['updated_by'] = auth()->id();
        $user->update($validated);
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true, 
                'message' => 'User updated successfully.',
                'user' => $user
            ]);
        }
        
        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }
    
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'You cannot delete your own account.'], 403);
            }
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }
        
        $user->delete();
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
        }
        
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
    
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'is_active' => $user->is_active]);
        }
        
        return redirect()->back()->with('success', 'User status updated.');
    }
    
    // NEW METHODS FOR ACCOUNT APPROVAL
    
    public function pendingAccounts()
    {
        $pendingUsers = User::where('account_status', 'pending')
            ->orderBy('created_at', 'asc')
            ->paginate(20);
        
        return view('admin.users.pending', compact('pendingUsers'));
    }
    
    public function approveAccount($id)
    {
        $user = User::findOrFail($id);
        
        // Generate employee ID for approved account if role is not 'user'
        $employeeId = null;
        if ($user->role !== 'user') {
            $employeeId = $this->generateEmployeeId($user->role);
        }
        
        $updateData = [
            'account_status' => 'approved',
            'is_active' => true,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ];
        
        if ($employeeId) {
            $updateData['employee_id'] = $employeeId;
        }
        
        $user->update($updateData);
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'employee_id' => $employeeId]);
        }
        
        return redirect()->back()->with('success', "Account for {$user->first_name} {$user->last_name} has been approved.");
    }
    
    public function rejectAccount(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);
        
        $user = User::findOrFail($id);
        
        $user->update([
            'account_status' => 'rejected',
            'is_active' => false,
            'rejection_reason' => $request->rejection_reason,
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
        ]);
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->back()->with('success', "Account for {$user->first_name} {$user->last_name} has been rejected.");
    }
    
    // Add this method to get user data for editing via AJAX
    public function getEditData($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }
}
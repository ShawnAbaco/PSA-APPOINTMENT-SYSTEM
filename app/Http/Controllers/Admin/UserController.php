<?php
// app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\TotpService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        // Get pending accounts count (excluding soft deleted)
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
 * Includes soft-deleted users to prevent duplicate IDs
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

    $prefix = "PSA-{$roleCode}-";

    // Get ALL users (including soft-deleted) with this role AND employee_id pattern
    // Use a simpler approach - just get all users with the role and non-null employee_id
    $allUsers = User::withTrashed()
        ->where('role', $role)
        ->whereNotNull('employee_id')
        ->get();

    $existingNumbers = [];

    foreach ($allUsers as $user) {
        if ($user->employee_id && strpos($user->employee_id, $prefix) === 0) {
            // Extract the number part (after the prefix)
            $numberPart = substr($user->employee_id, strlen($prefix));
            // Remove any non-numeric characters just in case
            $numberPart = preg_replace('/[^0-9]/', '', $numberPart);
            if (is_numeric($numberPart) && !empty($numberPart)) {
                $number = intval($numberPart);
                if ($number > 0) {
                    $existingNumbers[] = $number;
                }
            }
        }
    }

    // Sort numbers and find the next available (smallest unused number)
    sort($existingNumbers);
    $nextNumber = 1;
    foreach ($existingNumbers as $num) {
        if ($num == $nextNumber) {
            $nextNumber++;
        } else if ($num > $nextNumber) {
            break;
        }
    }

    // Format with leading zeros (3 digits)
    $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    $newEmployeeId = $prefix . $formattedNumber;

    // Double-check that this ID doesn't exist (safety net)
    $exists = User::withTrashed()
        ->where('employee_id', $newEmployeeId)
        ->exists();

    if ($exists) {
        // If somehow it exists, find the next available number by querying directly
        $existingIds = User::withTrashed()
            ->where('employee_id', 'LIKE', $prefix . '%')
            ->pluck('employee_id')
            ->map(function($id) use ($prefix) {
                $num = intval(substr($id, strlen($prefix)));
                return $num;
            })
            ->sort()
            ->values()
            ->toArray();

        $nextNumber = 1;
        foreach ($existingIds as $num) {
            if ($num == $nextNumber) {
                $nextNumber++;
            } else {
                break;
            }
        }

        $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        $newEmployeeId = $prefix . $formattedNumber;
    }

    \Log::info('Generated employee ID', [
        'role' => $role,
        'prefix' => $prefix,
        'existing_numbers' => $existingNumbers,
        'next_number' => $nextNumber,
        'new_employee_id' => $newEmployeeId
    ]);

    return $newEmployeeId;
}

    /**
     * Check if username or email is unique (excluding soft-deleted)
     */
    private function isUniqueCredential($field, $value, $excludeId = null)
    {
        $query = User::withTrashed()->where($field, $value);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return !$query->exists();
    }

    public function store(Request $request)
    {
        // Check for uniqueness including soft-deleted
        if (!User::withTrashed()->where('username', $request->username)->doesntExist()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Username already exists.'], 422);
            }
            return back()->withErrors(['username' => 'Username already exists.'])->withInput();
        }

        if (!User::withTrashed()->where('email', $request->email)->doesntExist()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Email already exists.'], 422);
            }
            return back()->withErrors(['email' => 'Email already exists.'])->withInput();
        }

        $validated = $request->validate([
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'first_name' => 'required',
            'last_name' => 'required',
            'role' => 'required|in:admin,staff,operator,user',
            'password' => 'required|min:6',
            'contact_number' => 'nullable',
        ]);

        // Generate employee ID based on role (includes soft-deleted in check)
        $employeeId = $this->generateEmployeeId($validated['role']);

        $validated['password'] = Hash::make($validated['password']);
        $validated['created_by'] = auth()->id();
        $validated['is_active'] = true;
        $validated['account_status'] = 'approved';
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
        $user = User::withTrashed()->findOrFail($id);

        if (request()->wantsJson()) {
            return response()->json($user);
        }

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
{
    $user = User::withTrashed()->findOrFail($id);

    // Check uniqueness including soft-deleted
    if ($request->username != $user->username) {
        if (!User::withTrashed()->where('username', $request->username)->doesntExist()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Username already exists.'], 422);
            }
            return back()->withErrors(['username' => 'Username already exists.'])->withInput();
        }
    }

    if ($request->email != $user->email) {
        if (!User::withTrashed()->where('email', $request->email)->doesntExist()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Email already exists.'], 422);
            }
            return back()->withErrors(['email' => 'Email already exists.'])->withInput();
        }
    }

    $validated = $request->validate([
        'username' => 'required',
        'email' => 'required|email',
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
        // Role changed - generate new employee ID based on new role
        // First, clear the old employee_id to avoid conflicts
        $validated['employee_id'] = $this->generateEmployeeId($newRole);

        \Log::info('Role changed for user', [
            'user_id' => $id,
            'old_role' => $oldRole,
            'new_role' => $newRole,
            'old_employee_id' => $user->employee_id,
            'new_employee_id' => $validated['employee_id']
        ]);
    } else {
        // Keep existing employee ID
        $validated['employee_id'] = $user->employee_id;
    }

    if ($request->filled('password')) {
        $validated['password'] = Hash::make($request->password);
    }

    $validated['updated_by'] = auth()->id();

    try {
        $user->update($validated);
    } catch (\Illuminate\Database\QueryException $e) {
        if ($e->errorInfo[1] == 1062) { // Duplicate entry error
            \Log::error('Duplicate employee ID error', [
                'user_id' => $id,
                'employee_id' => $validated['employee_id'] ?? null,
                'error' => $e->getMessage()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update user: Employee ID conflict. Please try again.'
                ], 422);
            }
            return back()->withErrors(['role' => 'Failed to update user: Employee ID conflict. Please try again.'])->withInput();
        }
        throw $e;
    }

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
        $user = User::withTrashed()->findOrFail($id);

        if ($user->id === auth()->id()) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'You cannot delete your own account.'], 403);
            }
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        // Soft delete the user
        $user->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
        }

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Restore a soft-deleted user
     */
    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        if (!$user->trashed()) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'User is not deleted.'], 400);
            }
            return redirect()->back()->with('error', 'User is not deleted.');
        }

        // Check if username/email already exists with another active user
        $existingUser = User::where('username', $user->username)->first();
        if ($existingUser && $existingUser->id != $user->id) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Cannot restore: Username already in use.'], 400);
            }
            return redirect()->back()->with('error', 'Cannot restore: Username already in use.');
        }

        $existingEmail = User::where('email', $user->email)->first();
        if ($existingEmail && $existingEmail->id != $user->id) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Cannot restore: Email already in use.'], 400);
            }
            return redirect()->back()->with('error', 'Cannot restore: Email already in use.');
        }

        $user->restore();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'User restored successfully.']);
        }

        return redirect()->route('admin.users.index')->with('success', 'User restored successfully.');
    }

    /**
     * Permanently delete a soft-deleted user
     */
    public function forceDelete($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        if (!$user->trashed()) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'User must be soft-deleted first.'], 400);
            }
            return redirect()->back()->with('error', 'User must be soft-deleted first.');
        }

        if ($user->id === auth()->id()) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'You cannot permanently delete your own account.'], 403);
            }
            return redirect()->back()->with('error', 'You cannot permanently delete your own account.');
        }

        $user->forceDelete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'User permanently deleted.']);
        }

        return redirect()->route('admin.users.index')->with('success', 'User permanently deleted.');
    }

    public function toggleStatus($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($user->trashed()) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Cannot toggle status for deleted account.'], 400);
            }
            return redirect()->back()->with('error', 'Cannot toggle status for deleted account.');
        }

        $user->update(['is_active' => !$user->is_active]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'is_active' => $user->is_active]);
        }

        return redirect()->back()->with('success', 'User status updated.');
    }

    public function pendingAccounts()
    {
        $pendingUsers = User::where('account_status', 'pending')
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return view('admin.users.pending', compact('pendingUsers'));
    }

   public function approveAccount($id)
{
    try {
        $user = User::withTrashed()->findOrFail($id);

        // Check if already approved
        if ($user->account_status === 'approved') {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Account is already approved.'], 400);
            }
            return redirect()->back()->with('error', 'Account is already approved.');
        }

        // Check if user is soft-deleted
        if ($user->trashed()) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Cannot approve a deleted account.'], 400);
            }
            return redirect()->back()->with('error', 'Cannot approve a deleted account.');
        }

        // Generate employee ID for approved account if role is not 'user'
        $employeeId = null;
        if ($user->role !== 'user') {
            // Use the same generation logic but ensure we get a unique ID
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
            return response()->json([
                'success' => true,
                'message' => 'Account approved successfully.',
                'employee_id' => $employeeId
            ]);
        }

        return redirect()->back()->with('success', "Account for {$user->first_name} {$user->last_name} has been approved.");

    } catch (\Exception $e) {
        \Log::error('Approval error: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());

        if (request()->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Error approving account: ' . $e->getMessage()
            ], 500);
        }

        return redirect()->back()->with('error', 'Error approving account: ' . $e->getMessage());
    }
}

    public function rejectAccount(Request $request, $id)
    {
        try {
            $request->validate([
                'rejection_reason' => 'required|string|max:500',
            ]);

            $user = User::withTrashed()->findOrFail($id);

            // Check if already approved or rejected
            if ($user->account_status !== 'pending') {
                if (request()->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Account is no longer pending.'], 400);
                }
                return redirect()->back()->with('error', 'Account is no longer pending.');
            }

            // Check if user is soft-deleted
            if ($user->trashed()) {
                if (request()->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Cannot reject a deleted account.'], 400);
                }
                return redirect()->back()->with('error', 'Cannot reject a deleted account.');
            }

            $user->update([
                'account_status' => 'rejected',
                'is_active' => false,
                'rejection_reason' => $request->rejection_reason,
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
            ]);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account rejected successfully.'
                ]);
            }

            return redirect()->back()->with('success', "Account for {$user->first_name} {$user->last_name} has been rejected.");

        } catch (\Exception $e) {
            \Log::error('Rejection error: ' . $e->getMessage());

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error rejecting account: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error rejecting account: ' . $e->getMessage());
        }
    }

    /**
     * Display soft-deleted users (trashed)
     */
    public function trashed()
    {
        $trashedUsers = User::onlyTrashed()
            ->with('creator', 'approver')
            ->latest('deleted_at')
            ->paginate(15);

        $pendingCount = User::where('account_status', 'pending')->count();

        return view('admin.users.trashed', compact('trashedUsers', 'pendingCount'));
    }

    /**
     * Debug method to see all employee IDs (for troubleshooting)
     */
    public function debugEmployeeIds()
    {
        $allUsers = User::withTrashed()
            ->whereNotNull('employee_id')
            ->select('id', 'username', 'role', 'employee_id', 'deleted_at')
            ->orderBy('employee_id')
            ->get();

        return response()->json($allUsers);
    }
}

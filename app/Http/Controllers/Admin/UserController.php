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
        $users = User::with('creator')->latest()->paginate(15);
        return view('admin.users.index', compact('users'));
    }
    
    public function create()
    {
        return view('admin.users.create');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'first_name' => 'required',
            'last_name' => 'required',
            'role' => 'required|in:admin,staff,user',
            'password' => 'required|min:6',
            'contact_number' => 'nullable',
        ]);
        
        $validated['password'] = Hash::make($validated['password']);
        $validated['created_by'] = auth()->id();
        
        User::create($validated);
        
        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }
    
    public function edit($id)
    {
        $user = User::findOrFail($id);
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
            'role' => 'required|in:admin,staff,user',
            'contact_number' => 'nullable',
            'is_active' => 'boolean',
        ]);
        
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }
        
        $validated['updated_by'] = auth()->id();
        $user->update($validated);
        
        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }
    
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }
        
        $user->delete();
        
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
    
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);
        
        return redirect()->back()->with('success', 'User status updated.');
    }
}
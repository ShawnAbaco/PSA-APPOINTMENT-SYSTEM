<?php
// app/Http/Controllers/Auth/RegisterController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        if (auth()->check()) {
            return redirect()->route('login');
        }
        return view('auth.create');
    }
    
    public function register(Request $request)
    {
        $validator = $this->validator($request->all());
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            $user = $this->create($request->all());
            
            return redirect()->route('login')
                ->with('success', 'Account created successfully! Please wait for admin approval before logging in.');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Registration failed: ' . $e->getMessage())->withInput();
        }
    }
    
    protected function validator(array $data)
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ];
        
        $messages = [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'position.required' => 'Position/Job Title is required.',
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
        
        return Validator::make($data, $rules, $messages);
    }
    
    protected function create(array $data)
    {
        $userData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'username' => $data['username'],
            'position' => $data['position'],
            'password' => Hash::make($data['password']),
            'role' => 'staff',
            'is_active' => false, // Not active until approved
            'account_status' => 'pending', // Pending approval
            'email' => $data['username'] . '@psa.gov.ph',
            'employee_id' => 'EMP-' . strtoupper(substr($data['first_name'], 0, 1) . substr($data['last_name'], 0, 3) . rand(100, 999)),
        ];
        
        return User::create($userData);
    }
}
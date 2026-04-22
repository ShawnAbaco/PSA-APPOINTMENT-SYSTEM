<?php
// app/Http/Controllers/Auth/LoginController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role === 'staff') {
                return redirect()->route('staff.dashboard');
            }
        }
        
        return view('auth.login');
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        
        $field = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        if (Auth::attempt([$field => $request->username, 'password' => $request->password], $request->remember)) {
            $user = Auth::user();
            
            // Check if account is approved
            if ($user->account_status === 'pending') {
                Auth::logout();
                return back()->withErrors(['username' => 'Your account is pending approval. Please wait for admin approval.']);
            }
            
            if ($user->account_status === 'rejected') {
                Auth::logout();
                $reason = $user->rejection_reason ? ' Reason: ' . $user->rejection_reason : '';
                return back()->withErrors(['username' => 'Your account has been rejected.' . $reason]);
            }
            
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['username' => 'Account deactivated.']);
            }
            
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);
            
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role === 'staff') {
                return redirect()->route('staff.dashboard');
            }
            
            return redirect('/');
        }
        
        return back()->withErrors(['username' => 'Invalid credentials.']);
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
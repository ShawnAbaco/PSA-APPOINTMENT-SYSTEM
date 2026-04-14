<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        if (!in_array(Auth::user()->role, ['admin', 'staff'])) {
            abort(403, 'Access denied. Staff only.');
        }
        
        return $next($request);
    }
}
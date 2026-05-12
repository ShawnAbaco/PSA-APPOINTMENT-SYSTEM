<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\TotpService;
use App\Models\User;

class TwoFactorController extends Controller
{
    public function index()
    {
        if (!session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }

        return view('auth.2fa');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        // normalize code: remove non-digits and trim
        $rawCode = (string) $request->input('code');
        $code = preg_replace('/\D+/', '', $rawCode);
        if (strlen($code) !== 6) {
            return back()->withErrors(['code' => 'Please enter a valid 6-digit code.'])->withInput();
        }

        $userId = session('2fa:user:id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Session expired.');
        }

        $user = User::find($userId);
        if (!$user || !$user->two_factor_enabled || !$user->two_factor_secret) {
            session()->forget('2fa:user:id');
            return redirect()->route('login')->with('error', 'Two-factor not configured for this account.');
        }

        // verify TOTP using normalized code
        if (TotpService::verify($user->two_factor_secret, $code)) {
            // Passed 2FA: log user in and regenerate session
            Auth::loginUsingId($user->id);
            session()->forget('2fa:user:id');
            $request->session()->regenerate();
            $user->updateLastLogin($request->ip());

            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role === 'staff') {
                return redirect()->route('staff.dashboard');
            } elseif ($user->role === 'operator') {
                return redirect()->route('operator.dashboard');
            }

            return redirect('/');
        }

        return back()->withErrors(['code' => 'Invalid two-factor code.'])->withInput();
    }
}

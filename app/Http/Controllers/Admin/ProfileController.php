<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\JsonResponse;
use PragmaRX\Google2FA\Google2FA;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

class ProfileController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Display the profile page.
     */
    public function index()
    {
        return view('admin.profile.index');
    }

    /**
     * Show the edit profile form.
     */
    public function edit()
    {
        return view('admin.edit');
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'contact_number' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->email = $validated['email'];
        $user->contact_number = $validated['contact_number'] ?? null;

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $user->profile_photo = $path;
        }

        $user->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!'
            ]);
        }

        return redirect()->route('admin.edit')->with('success', 'Profile updated successfully!');
    }

    /**
     * Show the change password form.
     */
    public function changePassword()
    {
        return view('admin.change-password');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()
            ],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect.'
                ], 422);
            }
            return back()->with('error', 'Current password is incorrect.');
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully!'
            ]);
        }

        return redirect()->route('admin.index')->with('success', 'Password updated successfully!');
    }

    /**
     * Generate a random secret key for 2FA
     */
    private function generateRandomSecret()
    {
        return $this->google2fa->generateSecretKey(32);
    }

    /**
     * Generate recovery codes
     */
    private function generateRecoveryCodes($count = 8)
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
        }
        return $codes;
    }

    /**
     * Generate QR Code with FULL OTP URI format (required for Google Authenticator)
     */
    private function generateQRCodeBase64($email, $secret)
    {
        try {
            $companyName = 'PSA Appointment System';
            // This creates the correct OTP URI format that Google Authenticator expects
            $qrCodeUrl = $this->google2fa->getQRCodeUrl($companyName, $email, $secret);
            
            // Generate QR code using Endroid
            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($qrCodeUrl)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                ->size(250)
                ->margin(10)
                ->build();
            
            return $result->getDataUri();
        } catch (\Exception $e) {
            \Log::error('QR Code generation failed: ' . $e->getMessage());
            
            // Fallback: Use Google Charts API
            $companyName = 'PSA Appointment System';
            $qrCodeUrl = $this->google2fa->getQRCodeUrl($companyName, $email, $secret);
            return 'https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=' . urlencode($qrCodeUrl);
        }
    }

    /**
     * Check if 2FA is enabled for the current user
     */
    public function checkTwoFactorStatus(): JsonResponse
    {
        $user = Auth::user();
        $user->refresh();
        
        return response()->json([
            'success' => true,
            'enabled' => (bool) $user->two_factor_enabled,
            'has_secret' => !empty($user->two_factor_secret),
            'confirmed' => !is_null($user->two_factor_confirmed_at)
        ]);
    }

    /**
     * Toggle two-factor authentication for the current user.
     */
    public function toggleTwoFactor(Request $request): JsonResponse
    {
        $user = Auth::user();
        $action = $request->input('action');

        \Log::info('2FA Toggle Request', [
            'user_id' => $user->id,
            'action' => $action,
            'current_2fa_enabled' => $user->two_factor_enabled
        ]);

        if ($action === 'enable') {
            $secret = $this->generateRandomSecret();
            $recoveryCodes = $this->generateRecoveryCodes(8);
            
            try {
                $user->two_factor_secret = $secret;
                $user->two_factor_enabled = true;
                $user->two_factor_confirmed_at = now();
                $user->two_factor_recovery_codes = json_encode($recoveryCodes);
                $user->save();
                $user->refresh();
                
                \Log::info('2FA Enabled Successfully', [
                    'user_id' => $user->id,
                    'secret' => $secret,
                    'codes_count' => count($recoveryCodes)
                ]);
                
                // Generate QR code with proper OTP URI format
                $qrCodeBase64 = $this->generateQRCodeBase64($user->email, $secret);
                $companyName = 'PSA Appointment System';
                $qrCodeUrl = $this->google2fa->getQRCodeUrl($companyName, $user->email, $secret);

                return response()->json([
                    'success' => true,
                    'enabled' => true,
                    'secret' => $secret,
                    'qr' => $qrCodeBase64,
                    'qr_image' => $qrCodeBase64,
                    'qr_code_url' => $qrCodeUrl,
                    'manual_entry' => $companyName . ' (' . $user->email . ')',
                    'recovery_codes' => $recoveryCodes,
                    'message' => 'Two-factor authentication enabled successfully!'
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to enable 2FA: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to enable 2FA: ' . $e->getMessage()
                ], 500);
            }
        }

        // disable 2FA
        try {
            $user->two_factor_enabled = false;
            $user->two_factor_secret = null;
            $user->two_factor_confirmed_at = null;
            $user->two_factor_recovery_codes = null;
            $user->save();
            
            \Log::info('2FA Disabled Successfully', ['user_id' => $user->id]);
            
            return response()->json([
                'success' => true,
                'enabled' => false,
                'message' => 'Two-factor authentication disabled successfully!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to disable 2FA: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to disable 2FA: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the QR and secret for the current user if 2FA is enabled.
     */
    public function showTwoFactorQr(): JsonResponse
    {
        $user = Auth::user();
        $user->refresh();
        
        \Log::info('2FA QR Request', [
            'user_id' => $user->id,
            'two_factor_enabled' => $user->two_factor_enabled,
            'has_secret' => !empty($user->two_factor_secret)
        ]);
        
        if (!$user->two_factor_enabled || empty($user->two_factor_secret)) {
            return response()->json([
                'success' => false, 
                'message' => '2FA not enabled.'
            ], 404);
        }
        
        $secret = $user->two_factor_secret;
        $companyName = 'PSA Appointment System';
        
        // Generate QR code with proper OTP URI format
        $qrCodeBase64 = $this->generateQRCodeBase64($user->email, $secret);
        $qrCodeUrl = $this->google2fa->getQRCodeUrl($companyName, $user->email, $secret);
        
        $recoveryCodes = [];
        if ($user->two_factor_recovery_codes) {
            $recoveryCodes = is_string($user->two_factor_recovery_codes) 
                ? json_decode($user->two_factor_recovery_codes, true) 
                : $user->two_factor_recovery_codes;
        }
        
        return response()->json([
            'success' => true,
            'secret' => $secret,
            'qr' => $qrCodeBase64,
            'qr_image' => $qrCodeBase64,
            'qr_code_url' => $qrCodeUrl,
            'manual_entry' => $companyName . ' (' . $user->email . ')',
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    /**
     * Verify 2FA code
     */
    public function verifyTwoFactor(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        $user = Auth::user();
        
        if (!$user->two_factor_secret) {
            return response()->json([
                'success' => false,
                'message' => '2FA not set up yet.'
            ], 400);
        }

        $valid = $this->google2fa->verifyKey($user->two_factor_secret, $request->code);
        
        if ($valid) {
            $user->two_factor_confirmed_at = now();
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => '2FA verified successfully!'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Invalid verification code.'
        ], 422);
    }
}
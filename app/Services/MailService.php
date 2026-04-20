<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class MailService
{
    protected $mail;
    protected $isEnabled;

    public function __construct()
    {
        $this->isEnabled = $this->getSetting('notification.enable_email', true);
        
        if ($this->isEnabled) {
            $this->initialize();
        }
    }

    protected function initialize()
    {
        $this->mail = new PHPMailer(true);
        
        try {
            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host = $this->getSetting('email_host', 'smtp.gmail.com');
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $this->getSetting('email_username', '');
            
            // Get password and decrypt if needed
            $password = $this->getPassword();
            $this->mail->Password = $password;
            
            $this->mail->SMTPSecure = $this->getSetting('email_encryption', 'tls');
            $this->mail->Port = $this->getSetting('email_port', 587);
            $this->mail->setFrom(
                $this->getSetting('email_from_address', 'noreply@psa.gov.ph'),
                $this->getSetting('email_from_name', 'PSA Appointment System')
            );
            
            // Disable SSL verification for local development (remove in production)
            $this->mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
        } catch (Exception $e) {
            Log::error('PHPMailer initialization failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get and decrypt password from settings
     */
    protected function getPassword()
    {
        $setting = Setting::where('key', 'email_password')->first();
        
        if (!$setting || empty($setting->value)) {
            return '';
        }
        
        // Check if the value is already plain text or encrypted
        $value = $setting->value;
        
        // Try to decrypt (if it's encrypted)
        try {
            // Check if it looks like an encrypted string (base64 encoded)
            if (preg_match('/^[a-zA-Z0-9\/\+=]+$/', $value) && strlen($value) > 50) {
                return Crypt::decryptString($value);
            }
        } catch (\Exception $e) {
            // If decryption fails, assume it's plain text (for backward compatibility)
            Log::warning('Could not decrypt email password, using as is');
        }
        
        return $value;
    }

    protected function getSetting($key, $default = null)
    {
        return Setting::get($key, $default);
    }
    
    // Rest of your MailService methods remain the same...
    
    public function sendAppointmentConfirmation($appointment, $clients)
    {
        if (!$this->isEnabled || !$appointment->contact_email) {
            return false;
        }

        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($appointment->contact_email, $appointment->contact_name);
            
            $this->mail->Subject = 'PSA National ID Appointment Confirmation - ' . $appointment->appointment_number;
            
            $body = $this->generateConfirmationEmail($appointment, $clients);
            $this->mail->isHTML(true);
            $this->mail->Body = $body;
            $this->mail->AltBody = strip_tags($body);
            
            $this->mail->send();
            
            Log::info('Appointment confirmation email sent', [
                'appointment_number' => $appointment->appointment_number,
                'email' => $appointment->contact_email
            ]);
            
            return true;
            
        } catch (Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage(), [
                'appointment_number' => $appointment->appointment_number
            ]);
            return false;
        }
    }
    
    protected function generateConfirmationEmail($appointment, $clients)
    {
        // Your existing email generation code...
        $services = [
            'reg' => 'National ID Registration',
            'correction' => 'Correction/Updating',
            'ephilid' => 'ePhilID Issuance',
            'trn' => 'TRN Retrieval'
        ];
        
        $clientsHtml = '';
        foreach ($clients as $index => $client) {
            $serviceName = $services[$client['service']] ?? $client['service'];
            $middleName = isset($client['middle_name']) && $client['middle_name'] ? ' ' . $client['middle_name'] : '';
            $fullName = $client['last_name'] . ', ' . $client['first_name'] . $middleName;
            if (isset($client['suffix']) && $client['suffix']) {
                $fullName .= ' ' . $client['suffix'];
            }
            
            $clientsHtml .= "
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd;'>" . ($index + 1) . "</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>{$fullName}</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>{$serviceName}</td>
                </tr>
            ";
        }
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Appointment Confirmation</title>
            <meta charset='UTF-8'>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='text-align: center; border-bottom: 3px solid #2c5f8a; padding-bottom: 15px; margin-bottom: 20px;'>
                    <img src='https://psa.gov.ph/sites/default/files/psa_logo.png' alt='PSA Logo' style='width: 80px; margin-bottom: 10px;'>
                    <h2 style='color: #2c5f8a; margin: 0;'>Philippine Statistics Authority</h2>
                    <p style='margin: 5px 0;'>National ID Appointment System</p>
                </div>
                
                <div style='background: #e8f5e9; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center;'>
                    <h3 style='color: #2e7d32; margin: 0;'>✓ Appointment Confirmed!</h3>
                </div>
                
                <p>Dear <strong>{$appointment->contact_name}</strong>,</p>
                <p>Your appointment has been successfully booked. Please find the details below:</p>
                
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0; background: #f9f9f9;'>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd; background: #f5f5f5; width: 40%;'><strong>Appointment Number:</strong></td>
                        <td style='padding: 10px; border: 1px solid #ddd;'><strong>{$appointment->appointment_number}</strong></td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd; background: #f5f5f5;'><strong>Reference Code:</strong></td>
                        <td style='padding: 10px; border: 1px solid #ddd;'><code style='background: #fff; padding: 3px 5px;'>{$appointment->reference_code}</code></td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd; background: #f5f5f5;'><strong>Appointment Date:</strong></td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>" . date('F d, Y', strtotime($appointment->appointment_date)) . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd; background: #f5f5f5;'><strong>Appointment Type:</strong></td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>" . ucfirst($appointment->type) . "</td>
                    </tr>
                </table>
                
                <h3 style='color: #2c5f8a;'>👥 Client Information</h3>
                <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                    <thead>
                        <tr style='background: #2c5f8a; color: white;'>
                            <th style='padding: 10px; border: 1px solid #2c5f8a; text-align: left;'>#</th>
                            <th style='padding: 10px; border: 1px solid #2c5f8a; text-align: left;'>Name</th>
                            <th style='padding: 10px; border: 1px solid #2c5f8a; text-align: left;'>Service</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$clientsHtml}
                    </tbody>
                </table>
                
                <h3 style='color: #2c5f8a;'>📞 Contact Information</h3>
                <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f9f9f9;'>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd; background: #f5f5f5; width: 40%;'><strong>Contact Person:</strong></td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>{$appointment->contact_name}</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd; background: #f5f5f5;'><strong>Mobile Number:</strong></td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>{$appointment->contact_mobile}</td>
                    </tr>
                    " . ($appointment->contact_email ? "
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd; background: #f5f5f5;'><strong>Email:</strong></td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>{$appointment->contact_email}</td>
                    </tr>" : "") . "
                </table>
                
                <div style='background: #fff3e0; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ff9800;'>
                    <h4 style='color: #e65100; margin-top: 0;'>📌 Important Reminders:</h4>
                    <ul style='margin-bottom: 0;'>
                        <li>Please bring this confirmation email and a valid government-issued ID on your appointment date.</li>
                        <li>Arrive at least 15 minutes before your scheduled time.</li>
                        <li>Bring all required documents as per your selected service.</li>
                        <li>For minors, a parent or legal guardian must accompany them.</li>
                    </ul>
                </div>
                
                <div style='text-align: center; font-size: 12px; color: #999; margin-top: 20px; padding-top: 15px; border-top: 1px solid #ddd;'>
                    <p>For inquiries: <strong>misamisoriental@psa.gov.ph</strong> | <strong>0956 576 6106</strong></p>
                    <p>Office Hours: Monday - Friday, 8:00 AM - 5:00 PM</p>
                    <p>© " . date('Y') . " Philippine Statistics Authority. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
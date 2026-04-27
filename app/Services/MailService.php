<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;

class MailService
{
    protected $mail;
    protected $isEnabled;

    public function __construct()
    {
        // Check if mail is enabled in .env (default to true)
        $this->isEnabled = env('MAIL_ENABLED', true);
        
        if ($this->isEnabled) {
            $this->initialize();
        }
    }

    protected function initialize()
    {
        $this->mail = new PHPMailer(true);
        
        try {
            // Server settings from .env
            $this->mail->isSMTP();
            $this->mail->Host = env('MAIL_HOST', 'smtp.gmail.com');
            $this->mail->SMTPAuth = true;
            $this->mail->Username = env('MAIL_USERNAME', '');
            $this->mail->Password = env('MAIL_PASSWORD', '');
            $this->mail->SMTPSecure = env('MAIL_ENCRYPTION', 'tls');
            $this->mail->Port = env('MAIL_PORT', 587);
            
            // From address from .env
            $this->mail->setFrom(
                env('MAIL_FROM_ADDRESS', 'noreply@psa.gov.ph'),
                env('MAIL_FROM_NAME', 'PSA Appointment System')
            );
            
            // Disable SSL verification for local development (remove in production)
            $this->mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
            
            Log::info('Mail service initialized with .env configuration', [
                'host' => env('MAIL_HOST'),
                'port' => env('MAIL_PORT'),
                'encryption' => env('MAIL_ENCRYPTION'),
                'from' => env('MAIL_FROM_ADDRESS')
            ]);
            
        } catch (Exception $e) {
            Log::error('PHPMailer initialization failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Send appointment confirmation email
     */
    public function sendAppointmentConfirmation($appointment, $clients)
    {
        if (!$this->isEnabled || !$appointment->contact_email) {
            Log::info('Email not sent: disabled or no recipient', [
                'enabled' => $this->isEnabled,
                'has_email' => !empty($appointment->contact_email)
            ]);
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
                'appointment_number' => $appointment->appointment_number,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Send test email
     */
    public function sendTestEmail($recipientEmail)
    {
        if (!$this->isEnabled) {
            Log::error('Test email failed: Mail service is disabled');
            return false;
        }

        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($recipientEmail);
            
            $this->mail->Subject = 'Test Email from PSA Appointment System';
            
            $body = $this->generateTestEmailBody();
            $this->mail->isHTML(true);
            $this->mail->Body = $body;
            $this->mail->AltBody = strip_tags($body);
            
            $this->mail->send();
            
            Log::info('Test email sent successfully to: ' . $recipientEmail);
            return true;
            
        } catch (Exception $e) {
            Log::error('Test email failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate test email body
     */
    protected function generateTestEmailBody()
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Test Email</title>
            <meta charset='UTF-8'>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='text-align: center; border-bottom: 3px solid #2c5f8a; padding-bottom: 15px; margin-bottom: 20px;'>
                    <h2 style='color: #2c5f8a; margin: 0;'>Philippine Statistics Authority</h2>
                    <p style='margin: 5px 0;'>National ID Appointment System</p>
                </div>
                
                <div style='background: #e8f5e9; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center;'>
                    <h3 style='color: #2e7d32; margin: 0;'>✓ Test Email Successful!</h3>
                </div>
                
                <p>Dear User,</p>
                <p>This is a test email from the PSA Appointment System. Your email configuration is working correctly!</p>
                
                <div style='background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h4 style='color: #2c5f8a; margin-top: 0;'>Configuration Details:</h4>
                    <p><strong>SMTP Host:</strong> " . env('MAIL_HOST', 'Not set') . "</p>
                    <p><strong>SMTP Port:</strong> " . env('MAIL_PORT', 'Not set') . "</p>
                    <p><strong>Encryption:</strong> " . env('MAIL_ENCRYPTION', 'Not set') . "</p>
                    <p><strong>From Address:</strong> " . env('MAIL_FROM_ADDRESS', 'Not set') . "</p>
                    <p><strong>From Name:</strong> " . env('MAIL_FROM_NAME', 'Not set') . "</p>
                </div>
                
                <div style='text-align: center; font-size: 12px; color: #999; margin-top: 20px; padding-top: 15px; border-top: 1px solid #ddd;'>
                    <p>© " . date('Y') . " Philippine Statistics Authority. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Generate appointment confirmation email body
     */
    protected function generateConfirmationEmail($appointment, $clients)
    {
        $services = [
            'reg' => 'National ID Registration',
            'updating' => 'Correction/Updating',
            'inquiry' => 'Status Inquiry / TRN Retrieval'
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
                
                <p>Dear <strong>" . htmlspecialchars($appointment->contact_name) . "</strong>,</p>
                <p>Your appointment has been successfully booked. Please find the details below:</p>
                
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0; background: #f9f9f9;'>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd; background: #f5f5f5; width: 40%;'><strong>Appointment Number:</strong></td>
                        <td style='padding: 10px; border: 1px solid #ddd;'><strong>" . htmlspecialchars($appointment->appointment_number) . "</strong></td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd; background: #f5f5f5;'><strong>Reference Code:</strong></td>
                        <td style='padding: 10px; border: 1px solid #ddd;'><code style='background: #fff; padding: 3px 5px;'>" . htmlspecialchars($appointment->reference_code) . "</code></td>
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
                        <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($appointment->contact_name) . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd; background: #f5f5f5;'><strong>Mobile Number:</strong></td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($appointment->contact_mobile) . "</td>
                    </tr>
                    " . ($appointment->contact_email ? "
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd; background: #f5f5f5;'><strong>Email:</strong></td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($appointment->contact_email) . "</td>
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
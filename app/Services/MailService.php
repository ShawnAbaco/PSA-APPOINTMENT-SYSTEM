<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class MailService
{
    protected $mail;
    protected $isEnabled;

    public function __construct()
    {
        // Check if mail is enabled in .env (default to true)
        $this->isEnabled = env('MAIL_ENABLED', true);
        
        if ($this->isEnabled) {
            try {
                $this->initialize();
            } catch (\Exception $e) {
                Log::error('Mail service initialization failed: ' . $e->getMessage());
                $this->isEnabled = false;
            }
        }
    }

    protected function initialize()
    {
        $this->mail = new PHPMailer(true);
        
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
        
        Log::info('Mail service initialized with .env configuration');
    }
    
    /**
     * Send appointment confirmation email with PDF attachments
     */
    public function sendAppointmentConfirmation($appointment, $clients, $timeSlotLabel = null)
    {
        if (!$this->isEnabled || !$appointment->contact_email) {
            Log::info('Email not sent: disabled or no recipient');
            return false;
        }

        try {
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            $this->mail->addAddress($appointment->contact_email, $appointment->contact_name);
            
            $this->mail->Subject = 'PSA National ID Appointment Confirmation - ' . $appointment->appointment_number;
            
            // Generate email body
            $body = $this->generateConfirmationEmail($appointment, $clients, $timeSlotLabel);
            $this->mail->isHTML(true);
            $this->mail->Body = $body;
            $this->mail->AltBody = strip_tags($body);
            
            // Generate and attach PDF for each client using the existing pdf.blade.php
            foreach ($clients as $client) {
                $pdfContent = $this->generateClientPDF($appointment, $client);
                $safeName = preg_replace('/[^a-zA-Z0-9]/', '_', $client['name']);
                $fileName = "appointment_slip_{$safeName}.pdf";
                $this->mail->addStringAttachment($pdfContent, $fileName);
                Log::info("Attached PDF for: " . $client['name']);
            }
            
            $this->mail->send();
            
            Log::info('Appointment confirmation email sent with attachments', [
                'appointment_number' => $appointment->appointment_number,
                'email' => $appointment->contact_email,
                'clients_count' => count($clients)
            ]);
            
            return true;
            
        } catch (Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            Log::error('Email sending failed (general): ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate PDF for a single client using the existing pdf.blade.php view
     */
    protected function generateClientPDF($appointment, $client)
    {
        // Prepare data in the format expected by pdf.blade.php
        $services = [
            'reg' => 'National ID Registration',
            'updating' => 'Correction/Updating',
            'inquiry' => 'Status Inquiry / Retrieval Of TRN / Other Concern'
        ];
        
        // Build full name if not already present
        if (!isset($client['name']) || empty($client['name'])) {
            $middleName = isset($client['middle_name']) && $client['middle_name'] ? ' ' . $client['middle_name'] : '';
            $fullName = $client['last_name'] . ', ' . $client['first_name'] . $middleName;
            if (isset($client['suffix']) && $client['suffix']) {
                $fullName .= ' ' . $client['suffix'];
            }
            $client['name'] = $fullName;
        }
        
        // Prepare appointment data matching pdf.blade.php expectations
        $appointmentData = [
            'number' => $appointment->appointment_number,
            'reference_code' => $appointment->reference_code,
            'date' => date('F d, Y', strtotime($appointment->appointment_date)),
            'contact_name' => $appointment->contact_name,
            'contact_mobile' => $appointment->contact_mobile,
            'contact_email' => $appointment->contact_email,
            'appointment_number' => $appointment->appointment_number,
        ];
        
        // Prepare client data matching pdf.blade.php expectations
        $clientData = [
            'name' => $client['name'],
            'client_number' => $client['client_number'],
            'service_name' => $services[$client['service']] ?? $client['service'],
            'service_label' => $services[$client['service']] ?? $client['service'],
            'time_slot' => $client['time_slot'] ?? 'Not specified',
            'time_slot_label' => $client['time_slot'] ?? 'Not specified',
            'first_name' => $client['first_name'] ?? '',
            'middle_name' => $client['middle_name'] ?? '',
            'last_name' => $client['last_name'] ?? '',
            'suffix' => $client['suffix'] ?? '',
            'service' => $client['service'],
        ];
        
        // Render the existing pdf.blade.php view
        // Pass a single client in an array as the view expects multiple clients
        $html = view('client.pdf', [
            'appointment' => $appointmentData,
            'clients' => [$clientData],
            'appointment_reference' => $appointment->reference_code,
            'schedule_display' => $appointmentData['date'],
            'contact_name' => $appointment->contact_name,
            'clients_list' => [$clientData],
        ])->render();
        
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->output();
    }
    
    /**
     * Generate appointment confirmation email body
     */
    protected function generateConfirmationEmail($appointment, $clients, $timeSlotLabel = null)
    {
        $services = [
            'reg' => 'National ID Registration',
            'updating' => 'Correction/Updating',
            'inquiry' => 'Status Inquiry / TRN Retrieval'
        ];
        
        // Build applicant list for email
        $applicantsHtml = '';
        foreach ($clients as $index => $client) {
            $serviceName = $services[$client['service']] ?? $client['service'];
            $middleName = isset($client['middle_name']) && $client['middle_name'] ? ' ' . $client['middle_name'] : '';
            $fullName = $client['last_name'] . ', ' . $client['first_name'] . $middleName;
            if (isset($client['suffix']) && $client['suffix']) {
                $fullName .= ' ' . $client['suffix'];
            }
            
            $clientTimeSlot = $timeSlotLabel ?? $client['time_slot'] ?? 'Not specified';
            $clientNumber = $client['client_number'] ?? 'CLN-' . str_pad($index + 1, 5, '0', STR_PAD_LEFT);
            
            $applicantsHtml .= '
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 20px; padding: 15px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div>
                        <h4 style="margin: 0; color: #2c5f8a;">Applicant ' . htmlspecialchars($clientNumber) . '</h4>
                        <p style="margin: 5px 0 0 0; font-weight: 600; font-size: 16px;">' . htmlspecialchars($fullName) . '</p>
                    </div>
                </div>
                <div style="border-top: 1px solid #e2e8f0; padding-top: 10px;">
                    <p style="margin: 5px 0;"><strong>Service:</strong> ' . htmlspecialchars($serviceName) . '</p>
                    <p style="margin: 5px 0;"><strong>Time Slot:</strong> ' . htmlspecialchars($clientTimeSlot) . '</p>
                </div>
            </div>';
        }
        
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <title>Appointment Confirmation</title>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 700px; margin: 0 auto; padding: 20px; }
                .header { text-align: center; border-bottom: 3px solid #2c5f8a; padding-bottom: 15px; margin-bottom: 20px; }
                .logo { font-size: 24px; font-weight: bold; color: #2c5f8a; }
                .success { background: #e8f5e9; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
                .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; background: #f9f9f9; }
                .info-table td { padding: 10px; border: 1px solid #ddd; }
                .info-table td:first-child { background: #f5f5f5; width: 40%; font-weight: bold; }
                .attachments { background: #e3f2fd; padding: 15px; border-radius: 8px; margin: 20px 0; }
                .office { margin: 20px 0; padding: 15px; background: #f0f4f8; border-radius: 8px; text-align: center; }
                .reminder { background: #fff3e0; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; font-size: 12px; color: #999; margin-top: 20px; padding-top: 15px; border-top: 1px solid #ddd; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="logo">Philippine Statistics Authority</div>
                    <div class="subtitle">PhilSys Appointment Management System</div>
                </div>
                
                <div class="success">
                    <h3 style="color: #2e7d32;">✓ Appointment Confirmed!</h3>
                    <p>Reference Code: <strong>' . htmlspecialchars($appointment->reference_code) . '</strong></p>
                </div>
                
                <p>Dear <strong>' . htmlspecialchars($appointment->contact_name) . '</strong>,</p>
                <p>Your appointment has been successfully booked.</p>
                
                <table class="info-table">
                    <tr>
                        <td><strong>Appointment Number:</strong></td>
                        <td><strong>' . htmlspecialchars($appointment->appointment_number) . '</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Reference Code:</strong></td>
                        <td><code>' . htmlspecialchars($appointment->reference_code) . '</code></td>
                    </tr>
                    <tr>
                        <td><strong>Appointment Date:</strong></td>
                        <td>' . date('F d, Y', strtotime($appointment->appointment_date)) . '</td>
                    </tr>
                    <tr>
                        <td><strong>Appointment Type:</strong></td>
                        <td>' . ucfirst($appointment->type) . ' (' . count($clients) . ' applicant(s))</td>
                    </tr>
                </table>
                
                <h3>📋 Applicant Details</h3>
                ' . $applicantsHtml . '
                
                <div class="attachments">
                    <strong>📎 PDF Attachments:</strong>
                    <ul>' . $this->generateAttachmentList($clients) . '</ul>
                    <p>✓ Each applicant has their own personalized appointment slip attached.</p>
                </div>
                
                <div class="office">
                    <strong>📍 PSA Misamis Oriental Office</strong><br>
                    Capt. Vicente Roa Street, Brgy. 31, Cagayan de Oro City, 9000 Misamis Oriental
                </div>
                
                <div class="reminder">
                    <strong>⏰ Important Reminders:</strong>
                    <ul>
                        <li>Please arrive at least 30 minutes before your scheduled time</li>
                        <li>Bring valid ID and required documents</li>
                        <li>Bring original documents (no photocopies)</li>
                    </ul>
                </div>
                
                <div class="footer">
                    <p>For inquiries: <strong>misamisoriental@psa.gov.ph</strong> | <strong>0956 576 6106</strong></p>
                    <p>© ' . date('Y') . ' Philippine Statistics Authority</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    /**
     * Generate list of attached PDFs
     */
    protected function generateAttachmentList($clients)
    {
        $html = '';
        foreach ($clients as $client) {
            $middleName = isset($client['middle_name']) && $client['middle_name'] ? ' ' . $client['middle_name'] : '';
            $fullName = $client['last_name'] . ', ' . $client['first_name'] . $middleName;
            if (isset($client['suffix']) && $client['suffix']) {
                $fullName .= ' ' . $client['suffix'];
            }
            $html .= '<li>📄 ' . htmlspecialchars($fullName) . '</li>';
        }
        return $html;
    }
}
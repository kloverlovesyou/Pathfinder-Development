<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrevoEmailService
{
    private $apiKey;
    private $fromEmail;
    private $fromName;

    public function sendOrganizationApprovedEmail($toEmail, $orgName)
    {
        $subject = "🎉 Your Organization Has Been Approved!";

        $htmlContent = "
        <div style='font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px;'>
            <div style='max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.1);'>
                <div style='background-color: #4CAF50; color: #fff; padding: 20px; text-align: center;'>
                    <h1>Congratulations!</h1>
                </div>
                <div style='padding: 20px; color: #333; line-height: 1.6;'>
                    <p>Hi <strong>{$orgName}</strong>,</p>
                    <p>We are excited to inform you that your organization has been <strong>approved</strong> by our admin team.</p>
                    <p>You can now log in and start managing your trainings and careers.</p>
                    <div style='text-align: center; margin: 20px 0;'>
                        <a href='" . url('/login') . "' style='background-color: #4CAF50; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Login Now</a>
                    </div>
                    <p>Thank you,<br>The Admin Team</p>
                </div>
            </div>
        </div>
        ";

        return $this->send($toEmail, $subject, $htmlContent);
    }

    public function sendOrganizationRejectedEmail($toEmail, $orgName, $reason)
    {
        $subject = "⚠️ Your Organization Registration Was Rejected";

        $htmlContent = "
        <div style='font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px;'>
            <div style='max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.1);'>
                <div style='background-color: #f44336; color: #fff; padding: 20px; text-align: center;'>
                    <h1>Registration Rejected</h1>
                </div>
                <div style='padding: 20px; color: #333; line-height: 1.6;'>
                    <p>Hi <strong>{$orgName}</strong>,</p>
                    <p>We regret to inform you that your organization registration has been <strong>rejected</strong>.</p>
                    <p><strong>Reason:</strong> {$reason}</p>
                    <p>If you believe this is a mistake or would like to reapply, please contact support through email.</p>
                    <div style='text-align: center; margin: 20px 0;'>
                        <a href='mailto:pathfineradmin@gmail.com' style='background-color: #f44336; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Contact Support</a>
                    </div>
                    <p>Thank you,<br>The Admin Team</p>
                </div>
            </div>
        </div>
        ";

        return $this->send($toEmail, $subject, $htmlContent);
    }

    public function sendPasswordChangeOTP($toEmail, $orgName, $otp)
    {
        $subject = "🔐 Password Change Verification Code";

        $htmlContent = "
        <div style='font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px;'>
            <div style='max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.1);'>
                <div style='background-color: #44576D; color: #fff; padding: 20px; text-align: center;'>
                    <h1>Password Change Verification</h1>
                </div>
                <div style='padding: 20px; color: #333; line-height: 1.6;'>
                    <p>Hi <strong>{$orgName}</strong>,</p>
                    <p>You have requested to change your password. Please use the following verification code to complete the process:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <div style='background-color: #f0f0f0; border: 2px dashed #44576D; border-radius: 8px; padding: 20px; display: inline-block;'>
                            <div style='font-size: 32px; font-weight: bold; color: #44576D; letter-spacing: 8px;'>{$otp}</div>
                        </div>
                    </div>
                    <p style='color: #666; font-size: 14px;'>This code will expire in 10 minutes. If you did not request this change, please ignore this email or contact support.</p>
                    <p>Thank you,<br>The Pathfinder Team</p>
                </div>
            </div>
        </div>
        ";

        return $this->send($toEmail, $subject, $htmlContent);
    }

    public function sendLoginOTP($toEmail, $adminName, $otp)
    {
        $subject = "🔐 Admin Login Verification Code";

        $htmlContent = "
        <div style='font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px;'>
            <div style='max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.1);'>
                <div style='background-color: #44576D; color: #fff; padding: 20px; text-align: center;'>
                    <h1>Admin Login Verification</h1>
                </div>
                <div style='padding: 20px; color: #333; line-height: 1.6;'>
                    <p>Hi <strong>{$adminName}</strong>,</p>
                    <p>You have attempted to log in to your admin account. Please use the following verification code to complete the login:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <div style='background-color: #f0f0f0; border: 2px dashed #44576D; border-radius: 8px; padding: 20px; display: inline-block;'>
                            <div style='font-size: 32px; font-weight: bold; color: #44576D; letter-spacing: 8px;'>{$otp}</div>
                        </div>
                    </div>
                    <p style='color: #666; font-size: 14px;'>This code will expire in 10 minutes. If you did not attempt to log in, please ignore this email or contact support immediately.</p>
                    <p>Thank you,<br>The Pathfinder Team</p>
                </div>
            </div>
        </div>
        ";

        return $this->send($toEmail, $subject, $htmlContent);
    }

    public function __construct()
    {
        // Try multiple ways to get the API key, with trimming to handle spaces
        $apiKeyFromConfig = config('services.brevo.api_key');
        $apiKeyFromEnv = env('BREVO_API_KEY');
        
        // Use config first, then env, and trim to remove any whitespace
        $this->apiKey = trim($apiKeyFromConfig ?: $apiKeyFromEnv ?: '');
        
        $this->fromEmail = config('mail.from.address');
        $this->fromName = config('mail.from.name');
        
        Log::info('BrevoEmailService initialized', [
            'api_key_set' => !empty($this->apiKey),
            'api_key_length' => $this->apiKey ? strlen($this->apiKey) : 0,
            'api_key_preview' => $this->apiKey ? substr($this->apiKey, 0, 10) . '...' : 'not set',
            'from_config' => !empty($apiKeyFromConfig),
            'from_env' => !empty($apiKeyFromEnv),
            'from_email' => $this->fromEmail,
            'from_name' => $this->fromName,
        ]);
    }

    /**
     * Send email using Brevo API
     */
    public function send($to, $subject, $htmlContent, $textContent = null)
    {
        return $this->sendWithCc($to, $subject, $htmlContent, [], $textContent);
    }

    /**
     * Send email using Brevo API with CC support
     */
    public function sendWithCc($to, $subject, $htmlContent, $ccEmails = [], $textContent = null)
    {
        Log::info('BrevoEmailService::sendWithCc called', [
            'to' => $to,
            'subject' => $subject,
            'cc_emails' => $ccEmails,
            'api_key_set' => !empty($this->apiKey),
            'from_email' => $this->fromEmail,
        ]);
        
        if (!$this->apiKey) {
            Log::error('Brevo API key is missing');
            throw new \Exception('Brevo API key is not configured. Set BREVO_API_KEY in your environment variables.');
        }

        Log::info('Sending request to Brevo API', [
            'to' => $to,
            'cc' => $ccEmails,
            'url' => 'https://api.brevo.com/v3/smtp/email'
        ]);

        $payload = [
            'sender' => [
                'name' => $this->fromName,
                'email' => $this->fromEmail,
            ],
            'to' => [
                [
                    'email' => $to,
                ],
            ],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
            'textContent' => $textContent ?? strip_tags($htmlContent),
        ];

        // Add CC if provided
        if (!empty($ccEmails)) {
            $payload['cc'] = array_map(function($email) {
                return ['email' => $email];
            }, $ccEmails);
        }

        // Configure HTTP client with SSL verification
        // For localhost/development, we MUST disable SSL verification
        // This fixes the "cURL error 60" SSL certificate issue on Windows/localhost
        $appEnv = config('app.env', 'production');
        $appUrl = config('app.url', 'http://localhost');
        $appDebug = config('app.debug', false);
        
        // Check if we're on localhost - be very permissive
        $isLocalhost = strpos(strtolower($appUrl), 'localhost') !== false ||
                       strpos($appUrl, '127.0.0.1') !== false ||
                       in_array(strtolower($appEnv), ['local', 'development']) ||
                       $appDebug === true ||
                       php_sapi_name() === 'cli-server'; // Built-in PHP server
        
        // ALWAYS disable SSL verification for localhost to fix Windows SSL issues
        // This is safe for development and necessary for localhost
        if ($isLocalhost) {
            // Use withoutVerifying() which sets verify => false in Guzzle options
            $httpClient = Http::timeout(10)->withoutVerifying();
            Log::info('SSL verification DISABLED for localhost/development', [
                'app_env' => $appEnv,
                'app_url' => $appUrl,
                'app_debug' => $appDebug,
                'php_sapi' => php_sapi_name(),
                'is_localhost' => true,
            ]);
        } else {
            $httpClient = Http::timeout(10);
            Log::info('SSL verification ENABLED for production', [
                'app_env' => $appEnv,
                'app_url' => $appUrl,
            ]);
        }
        
        $response = $httpClient->withHeaders([
                'accept' => 'application/json',
                'api-key' => $this->apiKey,
                'content-type' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', $payload);

        if ($response->successful()) {
            $messageId = $response->json('messageId');
            Log::info('Brevo email sent successfully', [
                'to' => $to,
                'cc' => $ccEmails,
                'subject' => $subject,
                'message_id' => $messageId,
            ]);
            return true;
        }

        // Get detailed error information
        $statusCode = $response->status();
        $errorBody = $response->json();
        $responseBody = $response->body();
        $errorMessage = $errorBody['message'] ?? $errorBody['error'] ?? 'Unknown error';
        $fullError = $errorBody;
        
        Log::error('Brevo email failed', [
            'to' => $to,
            'cc' => $ccEmails,
            'subject' => $subject,
            'status_code' => $statusCode,
            'error' => $errorMessage,
            'full_response' => $fullError,
            'response_body' => $responseBody,
        ]);

        // Provide more helpful error messages
        if ($statusCode === 401) {
            throw new \Exception('Brevo API authentication failed. Check your BREVO_API_KEY. Status: ' . $statusCode . ' - ' . $errorMessage . ' | Body: ' . $responseBody);
        } elseif ($statusCode === 400) {
            throw new \Exception('Brevo API request invalid: ' . $errorMessage . ' (Status: ' . $statusCode . ') | Body: ' . $responseBody);
        } else {
            throw new \Exception('Brevo API error (Status ' . $statusCode . '): ' . $errorMessage . ' | Body: ' . $responseBody);
        }
    }
}


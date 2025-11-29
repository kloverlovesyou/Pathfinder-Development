<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrevoEmailService
{
    private $apiKey;
    private $fromEmail;
    private $fromName;

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
        $errorMessage = $errorBody['message'] ?? $errorBody['error'] ?? 'Unknown error';
        $fullError = $errorBody;
        
        Log::error('Brevo email failed', [
            'to' => $to,
            'cc' => $ccEmails,
            'subject' => $subject,
            'status_code' => $statusCode,
            'error' => $errorMessage,
            'full_response' => $fullError,
            'response_body' => $response->body(),
        ]);

        // Provide more helpful error messages
        if ($statusCode === 401) {
            throw new \Exception('Brevo API authentication failed. Check your BREVO_API_KEY. Status: ' . $statusCode . ' - ' . $errorMessage);
        } elseif ($statusCode === 400) {
            throw new \Exception('Brevo API request invalid: ' . $errorMessage . ' (Status: ' . $statusCode . ')');
        } else {
            throw new \Exception('Brevo API error (Status ' . $statusCode . '): ' . $errorMessage);
        }
    }
}


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
        $this->apiKey = config('services.brevo.api_key', env('BREVO_API_KEY'));
        $this->fromEmail = config('mail.from.address');
        $this->fromName = config('mail.from.name');
        
        Log::info('BrevoEmailService initialized', [
            'api_key_set' => !empty($this->apiKey),
            'api_key_length' => $this->apiKey ? strlen($this->apiKey) : 0,
            'from_email' => $this->fromEmail,
            'from_name' => $this->fromName,
        ]);
    }

    /**
     * Send email using Brevo API
     */
    public function send($to, $subject, $htmlContent, $textContent = null)
    {
        Log::info('BrevoEmailService::send called', [
            'to' => $to,
            'subject' => $subject,
            'api_key_set' => !empty($this->apiKey),
            'from_email' => $this->fromEmail,
        ]);
        
        if (!$this->apiKey) {
            Log::error('Brevo API key is missing');
            throw new \Exception('Brevo API key is not configured. Set BREVO_API_KEY in your environment variables.');
        }

        Log::info('Sending request to Brevo API', [
            'to' => $to,
            'url' => 'https://api.brevo.com/v3/smtp/email'
        ]);

        $response = Http::timeout(10) // 10 second timeout for faster failure
            ->withHeaders([
                'accept' => 'application/json',
                'api-key' => $this->apiKey,
                'content-type' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', [
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
        ]);

        if ($response->successful()) {
            $messageId = $response->json('messageId');
            Log::info('Brevo email sent successfully', [
                'to' => $to,
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


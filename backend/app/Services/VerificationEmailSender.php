<?php

namespace App\Services;

use App\Mail\EmailVerification;
use App\Services\BrevoEmailService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;

class VerificationEmailSender
{
    /**
     * Send a verification email using Brevo API with SMTP fallback.
     *
     * @return array{
     *     email_sent: bool,
     *     email_error: string|null,
     *     brevo_error: string|null,
     *     used_brevo_api: bool,
     *     method: string|null
     * }
     */
    public function send(
        string $email,
        string $verificationUrl,
        string $userName,
        string $userType = 'user',
        bool $throwOnFailure = false
    ): array {
        $result = [
            'email_sent' => false,
            'email_error' => null,
            'brevo_error' => null,
            'used_brevo_api' => false,
            'method' => null,
        ];

        $brevoApiKey = config('services.brevo.api_key', env('BREVO_API_KEY'));

        $htmlContent = View::make('emails.verification', [
            'verificationUrl' => $verificationUrl,
            'userName' => $userName,
            'userType' => $userType,
        ])->render();

        if (!empty($brevoApiKey)) {
            try {
                /** @var BrevoEmailService $brevoService */
                $brevoService = app(BrevoEmailService::class);

                $brevoService->send(
                    $email,
                    'Verify Your Email Address - Pathfinder',
                    $htmlContent
                );

                $result['email_sent'] = true;
                $result['used_brevo_api'] = true;
                $result['method'] = 'brevo_api';

                Log::info('Verification email sent via Brevo API', [
                    'email' => $email,
                    'user_type' => $userType,
                ]);

                return $result;
            } catch (\Throwable $exception) {
                $result['brevo_error'] = $exception->getMessage();

                Log::warning('Brevo API failed for verification email', [
                    'email' => $email,
                    'user_type' => $userType,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        try {
            Mail::to($email)->send(new EmailVerification($verificationUrl, $userName, $userType));

            $result['email_sent'] = true;
            $result['method'] = 'smtp';

            Log::info('Verification email sent via SMTP fallback', [
                'email' => $email,
                'user_type' => $userType,
            ]);
        } catch (\Throwable $exception) {
            $errorParts = array_filter([$result['brevo_error'], $exception->getMessage()]);
            $result['email_error'] = implode(' | ', $errorParts) ?: $exception->getMessage();

            Log::error('SMTP failed for verification email', [
                'email' => $email,
                'user_type' => $userType,
                'error' => $exception->getMessage(),
            ]);

            if ($throwOnFailure) {
                throw $exception;
            }
        }

        if (!$result['email_sent']) {
            $result['email_error'] = $result['email_error'] ?? ($result['brevo_error'] ?: 'Failed to send verification email.');

            if ($throwOnFailure) {
                throw new \RuntimeException($result['email_error']);
            }
        }

        return $result;
    }

    /**
     * Send a password reset email using Brevo API with SMTP fallback.
     *
     * @return array{
     *     email_sent: bool,
     *     email_error: string|null,
     *     brevo_error: string|null,
     *     used_brevo_api: bool,
     *     method: string|null
     * }
     */
    public function sendPasswordReset(
        string $email,
        string $resetUrl,
        string $userName,
        string $userType = 'user',
        bool $throwOnFailure = false
    ): array {
        $result = [
            'email_sent' => false,
            'email_error' => null,
            'brevo_error' => null,
            'used_brevo_api' => false,
            'method' => null,
        ];

        $brevoApiKey = config('services.brevo.api_key', env('BREVO_API_KEY'));

        $htmlContent = View::make('emails.password-reset', [
            'resetUrl' => $resetUrl,
            'userName' => $userName,
            'userType' => $userType,
        ])->render();

        if (!empty($brevoApiKey)) {
            try {
                /** @var BrevoEmailService $brevoService */
                $brevoService = app(BrevoEmailService::class);

                $brevoService->send(
                    $email,
                    'Reset Your Password - Pathfinder',
                    $htmlContent
                );

                $result['email_sent'] = true;
                $result['used_brevo_api'] = true;
                $result['method'] = 'brevo_api';

                Log::info('Password reset email sent via Brevo API', [
                    'email' => $email,
                    'user_type' => $userType,
                ]);

                return $result;
            } catch (\Throwable $exception) {
                $result['brevo_error'] = $exception->getMessage();

                Log::warning('Brevo API failed for password reset email', [
                    'email' => $email,
                    'user_type' => $userType,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        try {
            Mail::to($email)->send(new \App\Mail\PasswordReset($resetUrl, $userName, $userType));

            $result['email_sent'] = true;
            $result['method'] = 'smtp';

            Log::info('Password reset email sent via SMTP fallback', [
                'email' => $email,
                'user_type' => $userType,
            ]);
        } catch (\Throwable $exception) {
            $errorParts = array_filter([$result['brevo_error'], $exception->getMessage()]);
            $result['email_error'] = implode(' | ', $errorParts) ?: $exception->getMessage();

            Log::error('SMTP failed for password reset email', [
                'email' => $email,
                'user_type' => $userType,
                'error' => $exception->getMessage(),
            ]);

            if ($throwOnFailure) {
                throw $exception;
            }
        }

        if (!$result['email_sent']) {
            $result['email_error'] = $result['email_error'] ?? ($result['brevo_error'] ?: 'Failed to send password reset email.');

            if ($throwOnFailure) {
                throw new \RuntimeException($result['email_error']);
            }
        }

        return $result;
    }
}


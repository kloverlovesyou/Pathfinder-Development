<?php

namespace App\Jobs;

use App\Services\VerificationEmailSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendVerificationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Retry the job a few times with exponential-ish backoff to absorb transient API outages.
     */
    public int $tries = 3;
    public array $backoff = [5, 30, 120];

    public function __construct(
        public string $email,
        public string $verificationUrl,
        public string $userName,
        public string $userType
    ) {
    }

    public function handle(VerificationEmailSender $verificationEmailSender): void
    {
        $verificationEmailSender->send(
            $this->email,
            $this->verificationUrl,
            $this->userName,
            $this->userType,
            true // Bubble failures so the queue can retry
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Verification email job failed', [
            'email' => $this->email,
            'user_type' => $this->userType,
            'error' => $exception->getMessage(),
        ]);
    }
}



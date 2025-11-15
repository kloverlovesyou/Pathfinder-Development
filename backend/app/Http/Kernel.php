<?php

namespace App\Http;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Support\Str;
use App\Models\Training;

class Kernel extends HttpKernel
{
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            \Illuminate\Http\Middleware\HandleCors::class, 
            'throttle:api', // ✅ Correct syntax
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.custom' => \App\Http\Middleware\AuthCustom::class,
        'apitoken' => \App\Http\Middleware\ApiTokenAuth::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class, // ✅ Add this line
    ];

    // ✅ Auto-generate QR key 1 minute before or right at schedule
        protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $now = now();

            $trainings = Training::where('schedule', '<=', $now)
                ->whereNull('attendance_key')
                ->get();

            foreach ($trainings as $training) {
                $training->attendance_key = Str::random(16);
                $training->attendance_expires_at = now()->addMinutes(30);
                $training->save();
            }

            // Optional: remove expired keys
            $expiredTrainings = Training::whereNotNull('attendance_expires_at')
                ->where('attendance_expires_at', '<', $now)
                ->get();

            foreach ($expiredTrainings as $training) {
                $training->attendance_key = null;
                $training->attendance_expires_at = null;
                $training->save();
            }
        })->everyMinute();
    }
}
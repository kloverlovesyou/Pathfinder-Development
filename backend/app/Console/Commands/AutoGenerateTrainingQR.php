<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\Training;

class AutoGenerateTrainingQR extends Command
{
    protected $signature = 'qr:autogenerate';
    protected $description = 'Automatically generate QR keys for active trainings';

    public function handle()
    {
        $now = now();

        // Generate keys for trainings that need them
        $trainings = Training::where('schedule', '<=', $now)
            ->whereNull('attendance_key')
            ->get();

        foreach ($trainings as $training) {
            $training->attendance_key = Str::random(16);
            $training->attendance_expires_at = $now->addMinutes(30);
            $training->save();
        }

        // Remove expired keys
        $expiredTrainings = Training::whereNotNull('attendance_expires_at')
            ->where('attendance_expires_at', '<', $now)
            ->get();

        foreach ($expiredTrainings as $training) {
            $training->attendance_key = null;
            $training->attendance_expires_at = null;
            $training->save();
        }

        $this->info('✅ Auto QR generation check complete.');
    }
}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Training;

class AutoGenerateTrainingQR extends Command
{
    protected $signature = 'qr:autogenerate';
    protected $description = 'Automatically generate QR keys for active trainings';

    public function handle()
    {
        $now = now();
        $trainings = Training::all();

        foreach ($trainings as $training) {
            // Call the same QR auto-generate logic
            app('App\Http\Controllers\TrainingController')->autoGenerateQR($training);
        }

        $this->info('✅ Auto QR generation check complete.');
    }
}
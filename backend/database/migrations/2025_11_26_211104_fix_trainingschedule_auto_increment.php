<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'mysql') {
            // MySQL: Set trainingScheduleID to AUTO_INCREMENT
            DB::statement('ALTER TABLE trainingschedule MODIFY COLUMN trainingScheduleID INT AUTO_INCREMENT');
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: Use SERIAL type (auto-increment)
            // First, create a sequence if it doesn't exist
            DB::statement('CREATE SEQUENCE IF NOT EXISTS trainingschedule_trainingscheduleid_seq');
            // Set the column to use the sequence
            DB::statement('ALTER TABLE trainingschedule ALTER COLUMN "trainingScheduleID" SET DEFAULT nextval(\'trainingschedule_trainingscheduleid_seq\')');
            // Set the sequence to start from the current max value
            DB::statement('SELECT setval(\'trainingschedule_trainingscheduleid_seq\', COALESCE((SELECT MAX("trainingScheduleID") FROM trainingschedule), 1), true)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'mysql') {
            // MySQL: Remove AUTO_INCREMENT
            DB::statement('ALTER TABLE trainingschedule MODIFY COLUMN trainingScheduleID INT');
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: Remove default sequence
            DB::statement('ALTER TABLE trainingschedule ALTER COLUMN "trainingScheduleID" DROP DEFAULT');
            // Optionally drop the sequence (commented out to preserve data)
            // DB::statement('DROP SEQUENCE IF EXISTS trainingschedule_trainingscheduleid_seq');
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedule_attendance', function (Blueprint $table) {
            $table->id('scheduleAttendanceID');
            $table->integer('registrationID');
            $table->integer('trainingScheduleID');
            $table->timestamp('attendedAt')->useCurrent();
            $table->string('attendanceKey')->nullable(); // The QR key used for attendance
            
            // Foreign key constraints
            $table->foreign('registrationID')
                ->references('registrationID')
                ->on('registration')
                ->onDelete('cascade');
            
            $table->foreign('trainingScheduleID')
                ->references('trainingScheduleID')
                ->on('trainingschedule')
                ->onDelete('cascade');
            
            // Ensure one attendance record per registration per schedule
            $table->unique(['registrationID', 'trainingScheduleID']);
            
            // Indexes for performance
            $table->index('registrationID');
            $table->index('trainingScheduleID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_attendance');
    }
};


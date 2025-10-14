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
        Schema::create('career', function (Blueprint $table) {
            $table->id('careerID');

            $table->string('position');
            $table->text('detailsAndInstructions');
            $table->text('qualifications');
            $table->text('requirements');
            $table->string('applicationLetterAddress');
            $table->datetime('deadlineOfSubmission');
            
            //foreign key constraints
            $table->foreign('organizationID')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career');
    }
};

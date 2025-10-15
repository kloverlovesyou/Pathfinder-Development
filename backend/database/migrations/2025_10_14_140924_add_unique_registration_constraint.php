<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration', function(Blueprint $table){
            //in case registration already exists
            $table->unique(['applicantID', 'trainingID'], 'registration_applicant_training_unique');
        });
    }


    public function down(): void
    {
        Schema::table('registration', function(Blueprint $table){
            $table->dropUnique('registration_applicant_training_unique');
        });
    }
};

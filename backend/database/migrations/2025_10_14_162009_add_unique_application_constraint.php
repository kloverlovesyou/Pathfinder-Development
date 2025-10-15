<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('application', function (Blueprint $table) {
            $table->unique(['applicantID', 'careerID'], 'application_applicant_career_unique');
        });
    }

    public function down(): void
    {
        Schema::table('application', function (Blueprint $table) {
            $table->dropUnique('application_applicant_career_unique');
        });
    }
};
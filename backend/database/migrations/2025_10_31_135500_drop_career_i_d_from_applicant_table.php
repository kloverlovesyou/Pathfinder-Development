<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::table('applicant', function (Blueprint $table) {
            if (Schema::hasColumn('applicant', 'careerID')) {
                $table->dropForeign(['careerID']);
                $table->dropColumn('careerID');
            }
        });
    }

   
    public function down(): void
    {
        Schema::table('applicant', function (Blueprint $table) {
            //
        });
    }
};

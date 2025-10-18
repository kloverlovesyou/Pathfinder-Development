<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if(!Schema::hasTable('career')){
            Schema::create('career', function (Blueprint $table) {
                $table->id('careerID');
    
                $table->string('position');
                $table->text('detailsAndInstructions');
                $table->text('qualifications');
                $table->text('requirements');
                $table->string('applicationLetterAddress');
                $table->datetime('deadlineOfSubmission');
                
                //foreign key constraints
                $table->unsignedBigInteger('organizationID')->nullable();
                $table->foreign('organizationID')
                    ->references('organizationID')
                    ->on('organization')
                    ->nullOnDelete();
            });
        } else{
            Schema::table('career', function (Blueprint $table){
                if(!Schema::hasColumn('career', 'organizationID')){
                    $table->unsignedBigInteger('organizationID')->nullable()->after('deadlineOfSubmission');
                    $table->foreign('organizationID')
                        ->references('organizationID')
                        ->on('organization')
                        ->nullOnDelete();
                }
            });
        }
        
        
    }

    
    public function down(): void
    {
        Schema::dropIfExists('career');
    }
};

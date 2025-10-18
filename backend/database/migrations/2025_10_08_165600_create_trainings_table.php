<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training', function (Blueprint $table) {
            $table->id('trainingID');

            // match the vue form fields
            $table->string('title');
            $table->string('description');
            $table->dateTime('schedule');
            $table->enum('mode', ['On-Site', 'Online']);
            $table->string('location')->nullable();
            $table->string('trainingLink')->nullable();
            
            //link the organizaton who posted it
            $table->foreignId('organizationID');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training');
    }
};

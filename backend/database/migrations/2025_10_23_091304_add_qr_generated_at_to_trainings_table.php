<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('training', function (Blueprint $table) {
            $table->timestamp('qr_generated_at')->nullable()->after('attendance_key');
        });
    }

    public function down(): void
    {
        Schema::table('training', function (Blueprint $table) {
            $table->dropColumn('qr_generated_at');
        });
    }
};
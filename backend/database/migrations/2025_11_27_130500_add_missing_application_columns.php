<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application', function (Blueprint $table) {
            if (!Schema::hasColumn('application', 'dateSubmitted')) {
                $table->timestamp('dateSubmitted')->nullable();
            }

            if (!Schema::hasColumn('application', 'applicationStatus')) {
                $table->string('applicationStatus', 100)->default('Submitted');
            }

            if (!Schema::hasColumn('application', 'interviewSchedule')) {
                $table->timestamp('interviewSchedule')->nullable();
            }

            if (!Schema::hasColumn('application', 'interviewMode')) {
                $table->string('interviewMode', 50)->nullable();
            }

            if (!Schema::hasColumn('application', 'interviewLocation')) {
                $table->string('interviewLocation', 255)->nullable();
            }

            if (!Schema::hasColumn('application', 'interviewLink')) {
                $table->string('interviewLink', 255)->nullable();
            }

            if (!Schema::hasColumn('application', 'appliedDate')) {
                $table->timestamp('appliedDate')->nullable();
            }

            if (!Schema::hasColumn('application', 'screenDate')) {
                $table->timestamp('screenDate')->nullable();
            }

            if (!Schema::hasColumn('application', 'pendingDate')) {
                $table->timestamp('pendingDate')->nullable();
            }

            if (!Schema::hasColumn('application', 'hiredDate')) {
                $table->timestamp('hiredDate')->nullable();
            }

            if (!Schema::hasColumn('application', 'declinedDate')) {
                $table->timestamp('declinedDate')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('application', function (Blueprint $table) {
            $columns = [
                'declinedDate',
                'hiredDate',
                'pendingDate',
                'screenDate',
                'appliedDate',
                'interviewLink',
                'interviewLocation',
                'interviewMode',
                'interviewSchedule',
                'applicationStatus',
                'dateSubmitted',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('application', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};


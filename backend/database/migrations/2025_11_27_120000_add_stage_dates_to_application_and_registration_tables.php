<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application', function (Blueprint $table) {
            if (!Schema::hasColumn('application', 'appliedDate')) {
                $table->timestamp('appliedDate')->nullable()->after('dateSubmitted');
            }
            if (!Schema::hasColumn('application', 'screenDate')) {
                $table->timestamp('screenDate')->nullable()->after('appliedDate');
            }
            if (!Schema::hasColumn('application', 'pendingDate')) {
                $table->timestamp('pendingDate')->nullable()->after('screenDate');
            }
            if (!Schema::hasColumn('application', 'hiredDate')) {
                $table->timestamp('hiredDate')->nullable()->after('pendingDate');
            }
            if (!Schema::hasColumn('application', 'declinedDate')) {
                $table->timestamp('declinedDate')->nullable()->after('hiredDate');
            }
        });

        // Backfill appliedDate with existing submission dates
        DB::statement('UPDATE "application" SET "appliedDate" = COALESCE("appliedDate", "dateSubmitted")');

        Schema::table('registration', function (Blueprint $table) {
            if (!Schema::hasColumn('registration', 'registeredDate')) {
                $table->timestamp('registeredDate')->nullable()->after('registrationDate');
            }
            if (!Schema::hasColumn('registration', 'ongoingDate')) {
                $table->timestamp('ongoingDate')->nullable()->after('registeredDate');
            }
            if (!Schema::hasColumn('registration', 'completedDate')) {
                $table->timestamp('completedDate')->nullable()->after('ongoingDate');
            }
            if (!Schema::hasColumn('registration', 'certifiedDate')) {
                $table->timestamp('certifiedDate')->nullable()->after('completedDate');
            }
        });

        DB::statement('UPDATE "registration" SET "registeredDate" = COALESCE("registeredDate", "registrationDate")');
        DB::statement('UPDATE "registration" SET "certifiedDate" = COALESCE("certifiedDate", "certGivenDate")');
    }

    public function down(): void
    {
        Schema::table('application', function (Blueprint $table) {
            if (Schema::hasColumn('application', 'declinedDate')) {
                $table->dropColumn('declinedDate');
            }
            if (Schema::hasColumn('application', 'hiredDate')) {
                $table->dropColumn('hiredDate');
            }
            if (Schema::hasColumn('application', 'pendingDate')) {
                $table->dropColumn('pendingDate');
            }
            if (Schema::hasColumn('application', 'screenDate')) {
                $table->dropColumn('screenDate');
            }
            if (Schema::hasColumn('application', 'appliedDate')) {
                $table->dropColumn('appliedDate');
            }
        });

        Schema::table('registration', function (Blueprint $table) {
            if (Schema::hasColumn('registration', 'certifiedDate')) {
                $table->dropColumn('certifiedDate');
            }
            if (Schema::hasColumn('registration', 'completedDate')) {
                $table->dropColumn('completedDate');
            }
            if (Schema::hasColumn('registration', 'ongoingDate')) {
                $table->dropColumn('ongoingDate');
            }
            if (Schema::hasColumn('registration', 'registeredDate')) {
                $table->dropColumn('registeredDate');
            }
        });
    }
};


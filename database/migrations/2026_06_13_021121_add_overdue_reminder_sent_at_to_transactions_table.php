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
        Schema::table('transactions', function (Blueprint $table) {
            // When the last overdue-loan reminder was sent for this loan, so the
            // daily scan can re-remind on a cadence instead of emailing every day.
            $table->timestamp('overdue_reminder_sent_at')->nullable()->after('expected_return_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('overdue_reminder_sent_at');
        });
    }
};

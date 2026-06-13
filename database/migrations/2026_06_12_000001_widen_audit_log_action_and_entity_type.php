<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The original create_audit_log_table migration was amended in place to use a
 * plain string `action` column (the enum rejected the Auditable trait's
 * 'created'/'updated'/'deleted' and TransactionService's 'transaction_created')
 * and a wider `entity_type` (class names like App\Models\Item). Fresh installs
 * get the corrected schema from the create migration; this migration upgrades
 * databases that ran the original version. It is a no-op-equivalent on
 * already-corrected columns, so it is safe everywhere.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_log', function (Blueprint $table) {
            $table->string('entity_type', 100)->change();
            $table->string('action', 50)->change();
        });
    }

    public function down(): void
    {
        // Intentionally left empty: reverting to the enum would corrupt or
        // reject audit rows already written with the new action values.
    }
};

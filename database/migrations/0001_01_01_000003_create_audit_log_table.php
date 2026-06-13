<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('entity_type', 100);
            $table->unsignedInteger('entity_id');
            // Plain string rather than an enum: audit actions come from two
            // sources with different conventions — controllers emit 'create' /
            // 'update' / 'delete' / 'restore' / login events, while the Auditable
            // model trait emits past-tense 'created' / 'updated' / 'deleted' and
            // TransactionService emits 'transaction_created'. An enum rejected the
            // latter set (CHECK violation on SQLite, silent corruption on MySQL).
            $table->string('action', 50);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at');

            $table->index(['entity_type', 'entity_id']);
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};

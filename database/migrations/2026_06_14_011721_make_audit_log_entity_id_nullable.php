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
        // App-level audit events (e.g. settings changes) aren't tied to a single
        // entity row, so entity_id must be allowed to be null.
        Schema::table('audit_log', function (Blueprint $table) {
            $table->unsignedInteger('entity_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('audit_log', function (Blueprint $table) {
            $table->unsignedInteger('entity_id')->nullable(false)->change();
        });
    }
};

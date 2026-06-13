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
        Schema::create('item_valuations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->decimal('value', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('source', 255)->nullable();
            $table->date('valued_at');
            $table->timestamps();

            // Queried as a per-item time series.
            $table->index(['item_id', 'valued_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_valuations');
    }
};

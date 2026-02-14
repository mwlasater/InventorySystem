<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->enum('transaction_type', [
                'sold', 'given_away', 'traded', 'loaned_out', 'returned',
                'lost', 'disposed', 'status_correction',
            ]);
            $table->date('transaction_date');
            $table->string('recipient_name', 255)->nullable();
            $table->string('recipient_contact', 255)->nullable();
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->decimal('shipping_cost', 10, 2)->nullable();
            $table->string('platform', 100)->nullable();
            $table->decimal('net_proceeds', 10, 2)->nullable();
            $table->date('expected_return_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['item_id', 'transaction_date']);
            $table->index('transaction_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('sku', 100)->unique()->nullable();
            $table->string('barcode', 255)->nullable()->index();
            $table->enum('condition_rating', ['new', 'like_new', 'very_good', 'good', 'fair', 'poor', 'for_parts'])->nullable();
            $table->string('brand', 255)->nullable();
            $table->string('model_number', 255)->nullable();
            $table->string('year_manufactured', 50)->nullable();
            $table->string('color', 100)->nullable();
            $table->string('dimensions', 255)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->date('acquisition_date')->nullable();
            $table->string('acquisition_source', 255)->nullable();
            $table->enum('acquisition_method', ['purchased', 'gift', 'trade', 'found', 'inherited', 'other'])->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->char('purchase_currency', 3)->default('USD');
            $table->decimal('estimated_value', 10, 2)->nullable();
            $table->date('valuation_date')->nullable();
            $table->string('valuation_source', 255)->nullable();
            $table->enum('status', ['in_collection', 'sold', 'given_away', 'traded', 'loaned_out', 'lost', 'damaged', 'disposed'])->default('in_collection');
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('modified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('category_id');
            $table->index('location_id');
            $table->index('status');
            $table->index('is_deleted');
        });

        // Add FULLTEXT index after table creation (MySQL-specific)
        DB::statement('ALTER TABLE items ADD FULLTEXT fulltext_search (name, description, notes)');

        // Create item_tags pivot table (deferred from Phase 2)
        Schema::create('item_tags', function (Blueprint $table) {
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->primary(['item_id', 'tag_id']);
            $table->index(['tag_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_tags');
        Schema::dropIfExists('items');
    }
};

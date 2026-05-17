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
        Schema::create('purchase_requisition_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_requisition_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('requested_quantity');
            $table->integer('approved_quantity')->nullable();
            $table->decimal('suggested_price', 12, 2)->nullable();

            $table->timestamps();

            // ✅ Explicit short name
            $table->unique(
                ['purchase_requisition_id', 'product_id'],
                'pr_items_prid_pid_unique'
            );

            // Optional (foreignId already adds indexes, but keeping is fine)
            $table->index('purchase_requisition_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requisition_items');
    }
};

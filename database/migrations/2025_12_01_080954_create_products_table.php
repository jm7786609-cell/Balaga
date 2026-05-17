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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code', 50)->unique(); // SKU or unique code or Auto-generated
            $table->string('product_name', 100);
            $table->string('product_brand', 100);
            $table->string('generic_name', 100);
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('price', 12, 2);
            $table->string('unit', 20);

            // EOQ INPUTS (system uses these to compute EOQ)
            $table->decimal('ordering_cost', 12, 2)->default(0);  // S
            $table->decimal('holding_cost', 12, 2)->default(0);   // H

            // Reorder point inputs
            $table->integer('lead_time_days')->default(0);
            $table->integer('safety_stock')->default(0);
            $table->integer('eoq')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

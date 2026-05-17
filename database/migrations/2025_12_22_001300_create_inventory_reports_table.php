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
        Schema::create('inventory_reports', function (Blueprint $table) {
            $table->id();

            // The date this report covers (e.g., daily, monthly, or custom period date)
            $table->date('report_date')->index();

            // The product this report line is for
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // Total quantity at the beginning of the period
            $table->integer('beginning_inventory')->unsigned()->default(0);

            // Total quantity at the end of the period
            $table->integer('ending_inventory')->unsigned()->default(0);

            // Optional remarks (e.g., "Physical count adjustment", "Damaged items removed", etc.)
            $table->text('remarks')->nullable();

            // Who created the report (optional but useful for audit)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->integer('physical_count')->unsigned()->default(0)->after('ending_inventory');
            $table->integer('variance')->virtualAs('physical_count - ending_inventory')->nullable();

            $table->timestamps();

            // Useful composite index for queries by date + product
            $table->index(['report_date', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_reports');
    }
};

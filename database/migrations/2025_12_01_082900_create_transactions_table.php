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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // Cashier who handles this transaction
            $table->foreignId('cashier_id')->constrained('users');

            // Financial fields
            $table->decimal('total_amount', 12, 2);
            $table->decimal('amount_paid', 12, 2);
            $table->decimal('change_due', 12, 2);

            // Track pending/completed status
            $table->enum('status', ['pending', 'completed'])->default('pending');

            $table->enum('discount_type', ['none', 'pwd', 'senior'])->default('none');
            $table->decimal('discount_amount', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

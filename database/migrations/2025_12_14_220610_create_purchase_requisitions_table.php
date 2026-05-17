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
        Schema::create('purchase_requisitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete(); // The supplier to approve
            $table->foreignId('requested_by')->constrained('users'); // Internal user who created it (e.g., admin/pharmacist)
            $table->foreignId('approved_by')->nullable()->constrained('users'); // Supplier user who approves/rejects
            $table->dateTime('approved_at')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])
                ->default('pending');

            $table->text('notes')->nullable(); // Optional internal notes
            $table->text('supplier_response')->nullable(); // Supplier's comments on approval/rejection

            $table->timestamps();

            // Index for faster queries
            $table->index('status');
            $table->index('supplier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requisitions');
    }
};

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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            // One payment settles one invoice, pending confirmation
            // (discovery §L.2 #9); revisit toward a payment_allocations
            // table if a payment can span multiple invoices.
            $table->foreignId('invoice_id')->constrained()->restrictOnDelete();
            $table->date('paid_at');
            $table->decimal('amount', 15, 2);
            $table->string('method', 20)->nullable();
            $table->string('reference', 60)->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

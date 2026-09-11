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
        Schema::create('truck_fixed_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('truck_id')->constrained()->restrictOnDelete();
            $table->foreignId('cost_type_id')->constrained()->restrictOnDelete();
            // The real billed amount/cycle; weekly figures are derived by
            // proration (docs/finance/financial-model.md §J.3), never stored here.
            $table->decimal('amount', 15, 2);
            $table->string('billing_cycle', 20);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();

            $table->index(['truck_id', 'effective_from', 'effective_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('truck_fixed_costs');
    }
};

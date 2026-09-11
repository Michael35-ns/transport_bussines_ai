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
        // The audit trail behind docs/decisions/0001-overhead-allocation-method.md:
        // one row per truck per computed weekly period, never hand-edited.
        Schema::create('fixed_cost_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('truck_id')->constrained()->restrictOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('method', 20);
            $table->decimal('weight', 9, 6);
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->unique(['truck_id', 'period_start', 'period_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixed_cost_allocations');
    }
};

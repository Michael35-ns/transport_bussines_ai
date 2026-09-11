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
        Schema::create('overhead_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cost_type_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('billing_cycle', 20);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overhead_costs');
    }
};

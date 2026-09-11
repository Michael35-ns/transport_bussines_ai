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
        Schema::create('tires', function (Blueprint $table) {
            $table->id();
            // No serials tracked today — internal label only (owner answer).
            $table->string('label', 40);
            $table->string('status', 20)->default('mounted');
            $table->foreignId('current_truck_id')->nullable()->constrained('trucks')->nullOnDelete();
            $table->string('current_position', 10)->nullable();
            $table->decimal('purchase_cost', 15, 2)->nullable();
            $table->foreignId('provider_id')->nullable()->constrained('maintenance_providers')->nullOnDelete();
            $table->timestamps();

            $table->index('current_truck_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tires');
    }
};

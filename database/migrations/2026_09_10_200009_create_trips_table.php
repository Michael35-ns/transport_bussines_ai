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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('truck_id')->constrained()->restrictOnDelete();
            $table->foreignId('driver_id')->constrained()->restrictOnDelete();
            $table->foreignId('route_id')->constrained()->restrictOnDelete();
            $table->foreignId('rate_agreement_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('planned_start')->nullable();
            $table->dateTime('actual_start')->nullable();
            $table->dateTime('actual_end')->nullable();
            // Defaults from routes.standard_km unless overridden (docs/decisions/0002).
            $table->decimal('distance', 10, 2)->nullable();
            $table->boolean('distance_estimated')->default(true);
            $table->decimal('price', 15, 2)->nullable();
            $table->string('status', 20)->default('planned');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['truck_id', 'status']);
            $table->index(['status', 'actual_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};

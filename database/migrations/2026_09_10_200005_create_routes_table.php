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
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('origin', 120);
            $table->string('destination', 120);
            // Required: the sole trip-distance source while no odometer is
            // captured (docs/decisions/0002-trip-distance-source.md).
            $table->decimal('standard_km', 10, 2);
            $table->decimal('typical_toll_cost', 15, 2)->nullable();
            $table->boolean('is_round_trip')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['origin', 'destination']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};

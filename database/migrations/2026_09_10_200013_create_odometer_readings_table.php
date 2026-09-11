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
        // Optional / future — no odometer is captured today (docs/decisions/0002).
        // Kept for any reading that is captured (service visits now; trip/GPS later).
        Schema::create('odometer_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('truck_id')->constrained()->restrictOnDelete();
            $table->dateTime('read_at');
            $table->decimal('odometer', 10, 2);
            $table->string('source', 20);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamps();

            $table->index(['truck_id', 'read_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odometer_readings');
    }
};

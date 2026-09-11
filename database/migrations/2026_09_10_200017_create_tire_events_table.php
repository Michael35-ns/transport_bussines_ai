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
        Schema::create('tire_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tire_id')->constrained()->cascadeOnDelete();
            $table->foreignId('truck_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type', 20);
            $table->string('position', 10)->nullable();
            $table->dateTime('occurred_at');
            $table->string('notes', 200)->nullable();
            $table->timestamps();

            $table->index(['tire_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tire_events');
    }
};

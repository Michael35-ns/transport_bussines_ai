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
        Schema::create('maintenance_schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('truck_id')->constrained()->cascadeOnDelete();
            $table->string('task_name', 80);
            $table->string('interval_type', 20);
            $table->decimal('interval_value', 10, 2)->nullable();
            $table->decimal('last_done_odometer', 10, 2)->nullable();
            $table->decimal('lead_km', 10, 2)->default(500);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['truck_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedule');
    }
};

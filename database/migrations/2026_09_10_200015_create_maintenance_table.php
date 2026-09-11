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
        Schema::create('maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('truck_id')->constrained()->restrictOnDelete();
            $table->string('type', 20);
            $table->foreignId('cost_type_id')->nullable()->constrained()->nullOnDelete();
            // Always external — the company has no in-house workshop.
            $table->foreignId('provider_id')->constrained('maintenance_providers')->restrictOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained('maintenance_schedule')->nullOnDelete();
            $table->date('entry_date');
            $table->date('completion_date')->nullable();
            // Re-anchors trucks.current_odometer when present (docs/decisions/0002).
            $table->decimal('odometer', 10, 2)->nullable();
            $table->string('description', 255)->nullable();
            $table->decimal('parts_cost', 15, 2)->default(0);
            $table->decimal('labor_cost', 15, 2)->default(0);
            $table->decimal('other_cost', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            // Counts against availability only when type = corrective.
            $table->decimal('downtime_days', 5, 2)->default(0);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['truck_id', 'completion_date']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance');
    }
};

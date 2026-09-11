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
        // Drivers are paid hourly for daily hours and are not attributable to
        // a trip or truck — this feeds the overhead driver-labour pool
        // (docs/decisions/0001-overhead-allocation-method.md).
        Schema::create('driver_worklogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained()->restrictOnDelete();
            $table->date('work_date');
            $table->decimal('hours', 5, 2);
            // Copied from drivers.hourly_rate at entry so later rate changes
            // don't rewrite history.
            $table->decimal('hourly_rate_snapshot', 12, 4);
            $table->decimal('computed_pay', 15, 2);
            $table->string('notes', 200)->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['driver_id', 'work_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_worklogs');
    }
};

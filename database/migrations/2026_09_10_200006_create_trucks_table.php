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
        Schema::create('trucks', function (Blueprint $table) {
            $table->id();
            $table->string('plate', 20)->unique();
            $table->string('internal_no', 20)->nullable()->unique();
            $table->string('vehicle_type', 20);
            $table->string('make', 60)->nullable();
            $table->string('model', 60)->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->date('acquisition_date')->nullable();
            $table->string('acquisition_mode', 20)->default('owned');
            // Financing terms are known to exist (2 of 6 trucks) but are not
            // tracked yet per the owner — left nullable and unused by the
            // cost engine (no capital-cost line, see docs/finance).
            $table->decimal('financing_monthly', 15, 2)->nullable();
            // Maintained estimate, not a live reading (docs/decisions/0002).
            $table->decimal('current_odometer', 10, 2)->default(0);
            $table->string('status', 20)->default('active');
            $table->string('base_yard', 120)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trucks');
    }
};

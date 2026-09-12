<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * `payments` now records a customer transaction rather than settling one
     * invoice directly — see docs/decisions/0004-payment-allocations.md.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropIndex(['invoice_id']);
            $table->dropColumn('invoice_id');
            $table->foreignId('customer_id')->after('id')->constrained()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
            $table->foreignId('invoice_id')->after('id')->constrained()->restrictOnDelete();
            $table->index('invoice_id');
        });
    }
};

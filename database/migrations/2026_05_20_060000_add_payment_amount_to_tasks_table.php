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
        Schema::table('tasks', function (Blueprint $table) {
            $table->decimal('payment_amount', 12, 2)->nullable()->after('amount');
            $table->string('payment_method')->nullable()->after('payment_amount');
            $table->string('reference_number')->nullable()->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['payment_amount', 'payment_method', 'reference_number']);
        });
    }
};

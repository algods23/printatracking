<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pcvs', function (Blueprint $table) {
            $table->id();
            $table->string('pcv_name');
            $table->enum('category', ['Materials', 'Labor', 'Utilities', 'Rent', 'Equipment', 'Transportation', 'Marketing', 'Other']);
            $table->string('other_category')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->text('description')->nullable();
            $table->string('voucher_number')->nullable();
            $table->string('voucher_path')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pcvs');
    }
};
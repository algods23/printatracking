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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_id')->unique();
            $table->string('customer_name');
            $table->string('contact_number');
            $table->enum('product_type', ['Signage', 'Sticker', 'Banner', 'Label', 'Other']);
            $table->enum('signage_type', ['Digital', 'Vinyl', 'Neon', 'LED', 'Wooden', 'Metal', 'Other'])->nullable();
            $table->enum('sticker_type', ['Vinyl', 'Paper', 'Label', 'Die-cut', 'Other'])->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->date('due_date');
            $table->enum('status', ['Pending', 'Designing', 'Printing', 'Installing', 'Completed', 'Received', 'Cancelled'])->default('Pending');
            $table->enum('priority', ['Low', 'Medium', 'High', 'Urgent'])->default('Medium');
            $table->text('notes')->nullable();
            $table->decimal('amount', 12, 2);
            $table->enum('payment_status', ['Unpaid', 'Partial', 'Paid'])->default('Unpaid');
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};

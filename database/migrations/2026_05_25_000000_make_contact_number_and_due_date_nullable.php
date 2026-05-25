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
            $table->string('contact_number')->nullable()->change();
            $table->date('due_date')->nullable()->change();
            $table->enum('priority', ['Low', 'Medium', 'High', 'Urgent'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('contact_number')->change();
            $table->date('due_date')->change();
            $table->enum('priority', ['Low', 'Medium', 'High', 'Urgent'])->default('Medium')->change();
        });
    }
};

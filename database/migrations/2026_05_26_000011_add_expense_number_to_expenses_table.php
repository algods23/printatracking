<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('expense_number')->nullable()->after('id');
        });

        $expenses = DB::table('expenses')
            ->orderBy('id')
            ->get(['id']);

        foreach ($expenses as $index => $expense) {
            DB::table('expenses')
                ->where('id', $expense->id)
                ->update([
                    'expense_number' => 'Expense # ' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                ]);
        }

        Schema::table('expenses', function (Blueprint $table) {
            $table->unique('expense_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropUnique(['expense_number']);
            $table->dropColumn('expense_number');
        });
    }
};

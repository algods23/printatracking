<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'expense_number',
        'expense_name',
        'category',
        'other_category',
        'amount',
        'date',
        'description',
        'receipt_number',
        'receipt_path',
        'recorded_by',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function getDisplayExpenseNumberAttribute(): string
    {
        if (! empty($this->expense_number)) {
            if (preg_match('/(\d+)$/', (string) $this->expense_number, $matches)) {
                return 'Disbursement # ' . str_pad((string) ((int) $matches[1]), 2, '0', STR_PAD_LEFT);
            }

            return str_replace('Expense #', 'Disbursement #', (string) $this->expense_number);
        }

        return 'Disbursement # ' . str_pad((string) $this->id, 2, '0', STR_PAD_LEFT);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}

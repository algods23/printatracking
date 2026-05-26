<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pcv extends Model
{
    protected $fillable = [
        'pcv_number',
        'pcv_name',
        'category',
        'other_category',
        'amount',
        'date',
        'description',
        'voucher_number',
        'voucher_path',
        'recorded_by',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function getDisplayPcvNumberAttribute(): string
    {
        if (! empty($this->pcv_number)) {
            if (preg_match('/(\d+)$/', (string) $this->pcv_number, $matches)) {
                return 'PCV # ' . str_pad((string) ((int) $matches[1]), 2, '0', STR_PAD_LEFT);
            }

            return str_replace('PCV #', 'PCV #', (string) $this->pcv_number);
        }

        return 'PCV # ' . str_pad((string) $this->id, 2, '0', STR_PAD_LEFT);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
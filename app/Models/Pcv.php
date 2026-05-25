<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pcv extends Model
{
    protected $fillable = [
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

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
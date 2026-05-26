<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receipt extends Model
{
    protected $fillable = [
        'receipt_number',
        'task_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'subtotal',
        'discount',
        'tax',
        'total',
        'cash_received',
        'change',
        'payment_method',
        'payment_channel',
        'payment_reference',
        'notes',
        'issued_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'change' => 'decimal:2',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReceiptItem::class);
    }

    public function getDisplayPaymentMethodAttribute(): string
    {
        return $this->payment_channel ?: $this->payment_method;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->receipt_number) {
                $model->receipt_number = 'RCP-' . date('Ymd') . '-' . str_pad(static::count() + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }
}

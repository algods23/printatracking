<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'task_id',
        'customer_name',
        'contact_number',
        'product_type',
        'signage_type',
        'sticker_type',
        'assigned_to',
        'due_date',
        'due_time',
        'status',
        'priority',
        'notes',
        'cancellation_reason',
        'amount',
        'payment_status',
        'payment_amount',
        'payment_method',
        'reference_number',
        'image_path',
        'attachments',
    ];

    protected $casts = [
        'due_date'     => 'date',
        'amount'       => 'decimal:2',
        'attachments'  => 'array',
    ];

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TaskItem::class);
    }

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->receipts()->sum('cash_received');
    }

    public function getBalanceAttribute(): float
    {
        return max((float) $this->amount - $this->paid_amount, 0);
    }

    public function syncPaymentStatus(): void
    {
        $paidAmount = $this->paid_amount;
        $taskAmount = (float) $this->amount;

        $this->payment_status = match (true) {
            $paidAmount <= 0 => 'Unpaid',
            $paidAmount >= $taskAmount => 'Paid',
            default => 'Partial',
        };

        $this->save();
    }

    public function setCustomerNameAttribute($value)
    {
        $this->attributes['customer_name'] = ucwords(strtolower($value));
    }

    public function recordPayment(float $amount, string $paymentMethod, ?string $reference = null, ?int $issuedBy = null): ?Receipt
    {
        $this->load('items');

        $paymentAmount = min($amount, $this->balance);

        if ($paymentAmount <= 0) {
            return null;
        }

        $mappedMethod = $this->mapPaymentMethodForReceipt($paymentMethod);
        $taskAmount = (float) $this->amount;

        $receipt = Receipt::create([
            'task_id'           => $this->id,
            'customer_name'     => $this->customer_name,
            'customer_phone'    => $this->contact_number,
            'customer_email'    => null,
            'subtotal'          => $taskAmount,
            'discount'          => 0,
            'tax'               => 0,
            'total'             => $taskAmount,
            'cash_received'     => $paymentAmount,
            'change'            => 0,
            'payment_method'    => $mappedMethod,
            'payment_channel'   => $paymentMethod,
            'payment_reference' => $mappedMethod === 'Cash' ? null : $reference,
            'notes'             => null,
            'issued_by'         => $issuedBy,
        ]);

        if ($this->items->isNotEmpty()) {
            foreach ($this->items as $item) {
                ReceiptItem::create([
                    'receipt_id'   => $receipt->id,
                    'product_name' => $item->job_order,
                    'description'  => "Payment for {$this->task_id}",
                    'quantity'     => $item->quantity,
                    'unit_price'   => $item->price,
                    'total'        => $item->total,
                ]);
            }
        } else {
            ReceiptItem::create([
                'receipt_id'   => $receipt->id,
                'product_name' => $this->product_type,
                'description'  => "Payment for {$this->task_id}",
                'quantity'     => 1,
                'unit_price'   => $taskAmount,
                'total'        => $taskAmount,
            ]);
        }

        $this->syncPaymentStatus();

        return $receipt;
    }

    private function mapPaymentMethodForReceipt(string $method): string
    {
        return match ($method) {
            'Credit Card' => 'Card',
            'GCash', 'Maya' => 'Other',
            default => in_array($method, ['Cash', 'Card', 'Check', 'Bank Transfer', 'Other'], true)
                ? $method
                : 'Other',
        };
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->task_id) {
                $lastTask = static::where('task_id', 'like', 'P-%')
                    ->orderByRaw("CAST(SUBSTRING(task_id, 3) AS UNSIGNED) DESC")
                    ->first();

                $nextNumber = $lastTask
                    ? (int) substr($lastTask->task_id, 2) + 1
                    : 1;

                $model->task_id = 'P-' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
            }
        });
    }
}

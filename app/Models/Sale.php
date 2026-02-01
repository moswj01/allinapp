<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Sale extends Model
{
    protected $fillable = [
        'sale_number',
        'branch_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'sale_type',
        'subtotal',
        'discount',
        'discount_percent',
        'vat',
        'total',
        'payment_method',
        'cash_received',
        'change_amount',
        'payment_status',
        'credit_due_date',
        'reference_number',
        'notes',
        'status',
        'user_id',
        'voided_at',
        'voided_by',
        'tax_invoice_number',
        'has_tax_invoice',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'vat' => 'decimal:2',
        'total' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'has_tax_invoice' => 'boolean',
        'voided_at' => 'datetime',
        'credit_due_date' => 'date',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->items()->sum('total') + 0; // sum of item totals
        $taxableAmount = max(0, $this->subtotal - ($this->discount ?? 0));
        $this->total = $taxableAmount + ($this->vat ?? 0);
    }

    // Payment methods
    public const PAYMENT_CASH = 'cash';
    public const PAYMENT_TRANSFER = 'transfer';
    public const PAYMENT_CREDIT = 'credit';
    public const PAYMENT_CARD = 'card';
    public const PAYMENT_QR = 'qr';
    public const PAYMENT_MIXED = 'mixed';

    // Payment statuses
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_VOIDED = 'voided';
}

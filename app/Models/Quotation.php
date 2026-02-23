<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;


class Quotation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'quotation_number',
        'branch_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'subject',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'total',
        'terms',
        'notes',
        'valid_until',
        'status',
        'converted_to_sale_id',
        'converted_at',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'valid_until' => 'date',
        'converted_at' => 'datetime',
        'approved_at' => 'datetime',
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
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function convertedToSale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'converted_to_sale_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    // Status constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CONVERTED = 'converted';
    public const STATUS_EXPIRED = 'expired';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'ร่าง',
            self::STATUS_SENT => 'ส่งแล้ว',
            self::STATUS_APPROVED => 'อนุมัติ',
            self::STATUS_REJECTED => 'ปฏิเสธ',
            self::STATUS_CONVERTED => 'สร้างบิลแล้ว',
            self::STATUS_EXPIRED => 'หมดอายุ',
        ];
    }

    public static function getStatusColor(string $status): string
    {
        return match ($status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_SENT => 'blue',
            self::STATUS_APPROVED => 'green',
            self::STATUS_REJECTED => 'red',
            self::STATUS_CONVERTED => 'purple',
            self::STATUS_EXPIRED => 'yellow',
            default => 'gray',
        };
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT]);
    }

    public function canBeConverted(): bool
    {
        return $this->status === self::STATUS_APPROVED && !$this->isExpired();
    }
}

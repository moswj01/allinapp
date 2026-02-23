<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairPart extends Model
{
    protected $attributes = [
        'unit_price' => 0,
        'total_price' => 0,
        'quantity' => 1,
    ];

    protected $fillable = [
        'repair_id',
        'product_id',
        'part_name',
        'quantity',
        'unit_price',
        'total_price',
        'status',
        'requisition_number',
        'requested_by',
        'approved_by',
        'approved_at',
        'issued_by',
        'issued_at',
        'returned_quantity',
        'returned_at',
        'return_reason',
        'rejected_by',
        'rejected_at',
        'reject_reason',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'returned_quantity' => 'integer',
        'approved_at' => 'datetime',
        'issued_at' => 'datetime',
        'returned_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function repair(): BelongsTo
    {
        return $this->belongsTo(Repair::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_ISSUED = 'issued';
    public const STATUS_USED = 'used';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_REJECTED = 'rejected';
}

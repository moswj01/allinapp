<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailySettlement extends Model
{
    protected $fillable = [
        'branch_id',
        'settlement_date',
        'opening_cash',
        'cash_sales',
        'transfer_sales',
        'qr_sales',
        'card_sales',
        'credit_sales',
        'total_sales',
        'cash_in',
        'cash_out',
        'expected_cash',
        'actual_cash',
        'difference',
        'difference_reason',
        'repair_revenue',
        'product_revenue',
        'part_revenue',
        'notes',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'settlement_date' => 'date',
        'opening_cash' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'transfer_sales' => 'decimal:2',
        'qr_sales' => 'decimal:2',
        'card_sales' => 'decimal:2',
        'credit_sales' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'cash_in' => 'decimal:2',
        'cash_out' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'actual_cash' => 'decimal:2',
        'difference' => 'decimal:2',
        'repair_revenue' => 'decimal:2',
        'product_revenue' => 'decimal:2',
        'part_revenue' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function calculateExpectedCash(): void
    {
        $this->expected_cash = $this->opening_cash + $this->cash_sales + $this->cash_in - $this->cash_out;
    }

    public function calculateDifference(): void
    {
        $this->difference = $this->actual_cash - $this->expected_cash;
    }

    // Status constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
}

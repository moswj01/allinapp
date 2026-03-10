<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanChangeRequest extends Model
{
    protected $fillable = [
        'tenant_id',
        'current_plan_id',
        'requested_plan_id',
        'requested_by',
        'type',
        'status',
        'amount',
        'tax_amount',
        'total_amount',
        'is_paid',
        'paid_at',
        'payment_method',
        'payment_proof',
        'approved_by',
        'approved_at',
        'admin_note',
        'tenant_note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function currentPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'current_plan_id');
    }

    public function requestedPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'requested_plan_id');
    }

    public function requestedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeLatestFirst($query)
    {
        return $query->latest();
    }
}

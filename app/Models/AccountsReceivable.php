<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\BelongsToTenant;


class AccountsReceivable extends Model
{
    use BelongsToTenant;

    protected $table = 'accounts_receivable';

    protected $fillable = [
        'branch_id',
        'customer_id',
        'source_type',
        'source_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'amount',
        'paid_amount',
        'balance',
        'overdue_days',
        'status',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo('source');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ARPayment::class, 'accounts_receivable_id');
    }

    public function updateBalance(): void
    {
        $this->paid_amount = $this->payments()->sum('amount');
        $this->balance = $this->amount - $this->paid_amount;

        if ($this->balance <= 0) {
            $this->status = self::STATUS_PAID;

            // Update source sale status to completed when fully paid
            if ($this->source_type === Sale::class) {
                Sale::where('id', $this->source_id)->update([
                    'status' => 'completed',
                    'payment_status' => 'paid',
                ]);
            }
        } elseif ($this->paid_amount > 0) {
            $this->status = self::STATUS_PARTIAL;

            // Update source sale payment_status to partial
            if ($this->source_type === Sale::class) {
                Sale::where('id', $this->source_id)->update([
                    'payment_status' => 'partial',
                ]);
            }
        }
    }

    public function isOverdue(): bool
    {
        return $this->status !== self::STATUS_PAID && $this->due_date->isPast();
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return now()->diffInDays($this->due_date);
    }

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_PAID = 'paid';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_WRITTEN_OFF = 'written_off';
}

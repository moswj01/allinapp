<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantOrder extends Model
{
    protected $fillable = [
        'order_number',
        'tenant_id',
        'branch_id',
        'created_by',
        'status',
        'subtotal',
        'discount_amount',
        'total',
        'notes',
        'shipping_address',
        'shipping_phone',
        'confirmed_by',
        'confirmed_at',
        'shipped_by',
        'shipped_at',
        'tracking_number',
        'received_by',
        'received_at',
        'cancelled_by',
        'cancelled_at',
        'cancel_reason',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'received_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Status constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CANCELLED = 'cancelled';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'แบบร่าง',
            self::STATUS_PENDING => 'รอยืนยัน',
            self::STATUS_CONFIRMED => 'ยืนยันแล้ว',
            self::STATUS_PREPARING => 'กำลังจัดเตรียม',
            self::STATUS_SHIPPED => 'จัดส่งแล้ว',
            self::STATUS_RECEIVED => 'รับสินค้าแล้ว',
            self::STATUS_CANCELLED => 'ยกเลิก',
        ];
    }

    public static function getStatusColor(string $status): string
    {
        return match ($status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_PENDING => 'yellow',
            self::STATUS_CONFIRMED => 'blue',
            self::STATUS_PREPARING => 'indigo',
            self::STATUS_SHIPPED => 'purple',
            self::STATUS_RECEIVED => 'green',
            self::STATUS_CANCELLED => 'red',
            default => 'gray',
        };
    }

    public static function getStatusLabel(string $status): string
    {
        return self::getStatuses()[$status] ?? $status;
    }

    // ─── Relationships ──────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function shippedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shipped_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TenantOrderItem::class);
    }

    // ─── Helpers ────────────────────────────────────

    public function canBeConfirmed(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canBeShipped(): bool
    {
        return in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_PREPARING]);
    }

    public function canBeReceived(): bool
    {
        return $this->status === self::STATUS_SHIPPED;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    public function recalculateTotal(): void
    {
        $this->subtotal = $this->items()->sum('subtotal');
        $this->total = $this->subtotal - $this->discount_amount;
        $this->save();
    }

    // ─── Scopes ─────────────────────────────────────

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Generate next order number: TO-YYMMDD-XXXX
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'TO-' . now()->format('ymd') . '-';
        $lastOrder = static::where('order_number', 'like', $prefix . '%')
            ->orderByDesc('order_number')
            ->first();

        $nextNum = 1;
        if ($lastOrder) {
            $lastNum = (int) substr($lastOrder->order_number, -4);
            $nextNum = $lastNum + 1;
        }

        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }
}

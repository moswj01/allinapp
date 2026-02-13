<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Repair extends Model
{
    protected $fillable = [
        'repair_number',
        'branch_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_line_id',
        'customer_email',
        'customer_address',
        'device_type',
        'device_brand',
        'device_model',
        'device_color',
        'device_serial',
        'device_imei',
        'device_password',
        'device_condition',
        'device_accessories',
        'problem_description',
        'diagnosis',
        'solution',
        'status',
        'received_by',
        'technician_id',
        'estimated_cost',
        'service_cost',
        'parts_cost',
        'discount',
        'total_cost',
        'deposit',
        'paid_amount',
        'payment_status',
        'payment_method',
        'warranty_days',
        'warranty_conditions',
        'warranty_expires_at',
        'received_at',
        'estimated_completion',
        'completed_at',
        'delivered_at',
        'is_claim',
        'original_repair_id',
        'priority',
        'internal_notes',
        'customer_notes',
    ];

    protected $casts = [
        'device_accessories' => 'array',
        'estimated_cost' => 'decimal:2',
        'service_cost' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'deposit' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'warranty_days' => 'integer',
        'warranty_expires_at' => 'date',
        'received_at' => 'datetime',
        'estimated_completion' => 'datetime',
        'completed_at' => 'datetime',
        'delivered_at' => 'datetime',
        'is_claim' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function originalRepair(): BelongsTo
    {
        return $this->belongsTo(Repair::class, 'original_repair_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Repair::class, 'original_repair_id');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(RepairPart::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(RepairLog::class);
    }

    public function communications(): HasMany
    {
        return $this->hasMany(RepairCommunication::class);
    }

    public function calculateTotal(): void
    {
        $this->parts_cost = $this->parts()->where('status', 'used')->sum(DB::raw('quantity * unit_price'));
        $this->total_cost = $this->service_cost + $this->parts_cost - $this->discount;
    }

    public function getBalanceAttribute(): float
    {
        return $this->total_cost - $this->paid_amount;
    }

    public function isUnderWarranty(): bool
    {
        return $this->warranty_expires_at && $this->warranty_expires_at->isFuture();
    }

    // Statuses for Kanban
    public const STATUS_PENDING = 'pending';
    public const STATUS_WAITING_PARTS = 'waiting_parts';
    public const STATUS_QUOTED = 'quoted';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_QC = 'qc';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_CLAIM = 'claim';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'รอซ่อม',
            self::STATUS_WAITING_PARTS => 'รออะไหล่',
            self::STATUS_QUOTED => 'เสนอราคา',
            self::STATUS_CONFIRMED => 'ลูกค้ายืนยัน',
            self::STATUS_IN_PROGRESS => 'กำลังซ่อม',
            self::STATUS_QC => 'ตรวจสอบ QC',
            self::STATUS_COMPLETED => 'ซ่อมเสร็จ',
            self::STATUS_DELIVERED => 'ส่งคืนแล้ว',
            self::STATUS_CANCELLED => 'ยกเลิก',
            self::STATUS_CLAIM => 'เคลม',
        ];
    }

    // Payment statuses
    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PARTIAL = 'partial';
    public const PAYMENT_PAID = 'paid';
}

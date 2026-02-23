<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;


class StockTake extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'stock_take_number',
        'branch_id',
        'type',
        'category_id',
        'status',
        'notes',
        'started_at',
        'completed_at',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTakeItem::class);
    }

    // Type constants
    public const TYPE_FULL = 'full';
    public const TYPE_PARTIAL = 'partial';
    public const TYPE_CATEGORY = 'category';

    // Status constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_CANCELLED = 'cancelled';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'ร่าง',
            self::STATUS_IN_PROGRESS => 'กำลังนับ',
            self::STATUS_COMPLETED => 'นับเสร็จ',
            self::STATUS_APPROVED => 'อนุมัติ',
            self::STATUS_CANCELLED => 'ยกเลิก',
        ];
    }

    public static function getStatusColor(string $status): string
    {
        return match ($status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_IN_PROGRESS => 'blue',
            self::STATUS_COMPLETED => 'yellow',
            self::STATUS_APPROVED => 'green',
            self::STATUS_CANCELLED => 'red',
            default => 'gray',
        };
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_FULL => 'ตรวจนับทั้งหมด',
            self::TYPE_PARTIAL => 'ตรวจนับบางส่วน',
            self::TYPE_CATEGORY => 'ตรวจนับตามหมวดหมู่',
        ];
    }

    public function canBeStarted(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canBeCompleted(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}

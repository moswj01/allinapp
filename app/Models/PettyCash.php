<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PettyCash extends Model
{
    protected $fillable = [
        'branch_id',
        'type',
        'category',
        'amount',
        'description',
        'receipt_number',
        'receipt_image',
        'transaction_date',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
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

    // Type constants
    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';

    // Category constants
    public const CATEGORY_SUPPLIES = 'supplies';
    public const CATEGORY_TRANSPORT = 'transport';
    public const CATEGORY_FOOD = 'food';
    public const CATEGORY_UTILITIES = 'utilities';
    public const CATEGORY_MAINTENANCE = 'maintenance';
    public const CATEGORY_OTHER = 'other';

    public static function getCategories(): array
    {
        return [
            self::CATEGORY_SUPPLIES => 'อุปกรณ์สำนักงาน',
            self::CATEGORY_TRANSPORT => 'ค่าเดินทาง',
            self::CATEGORY_FOOD => 'ค่าอาหาร/เครื่องดื่ม',
            self::CATEGORY_UTILITIES => 'ค่าสาธารณูปโภค',
            self::CATEGORY_MAINTENANCE => 'ค่าซ่อมบำรุง',
            self::CATEGORY_OTHER => 'อื่นๆ',
        ];
    }
}

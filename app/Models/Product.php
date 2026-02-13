<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    protected $fillable = [
        'barcode',
        'sku',
        'name',
        'category_id',
        'branch_id',
        'description',
        'unit',
        'cost',
        'retail_price',
        'wholesale_price',
        'vip_price',
        'partner_price',
        'quantity',
        'min_stock',
        'max_stock',
        'reorder_point',
        'source',
        'image',
        'is_active',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'retail_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'vip_price' => 'decimal:2',
        'partner_price' => 'decimal:2',
        'quantity' => 'integer',
        'min_stock' => 'integer',
        'max_stock' => 'integer',
        'reorder_point' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function branchStocks(): MorphMany
    {
        return $this->morphMany(BranchStock::class, 'stockable');
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'movable');
    }

    public function saleItems(): MorphMany
    {
        return $this->morphMany(SaleItem::class, 'itemable');
    }

    public function needsReorder(): bool
    {
        return $this->quantity <= $this->reorder_point && $this->reorder_point > 0;
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_stock;
    }

    public function getPriceByType(string $type): float
    {
        return match ($type) {
            'wholesale' => $this->wholesale_price,
            'vip' => $this->vip_price,
            'partner' => $this->partner_price,
            default => $this->retail_price,
        };
    }
}

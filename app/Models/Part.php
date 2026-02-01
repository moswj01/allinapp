<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Part extends Model
{
    protected $fillable = [
        'barcode',
        'sku',
        'name',
        'category_id',
        'supplier_id',
        'description',
        'unit',
        'compatible_brands',
        'compatible_models',
        'cost',
        'price',
        'quantity',
        'min_stock',
        'reorder_point',
        'source',
        'image',
        'is_active',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'min_stock' => 'integer',
        'reorder_point' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function repairParts(): HasMany
    {
        return $this->hasMany(RepairPart::class);
    }

    public function branchStocks(): MorphMany
    {
        return $this->morphMany(BranchStock::class, 'stockable');
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'movable');
    }

    public function needsReorder(): bool
    {
        return $this->quantity <= $this->reorder_point && $this->reorder_point > 0;
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_stock;
    }
}

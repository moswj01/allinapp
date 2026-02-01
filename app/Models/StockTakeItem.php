<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockTakeItem extends Model
{
    protected $fillable = [
        'stock_take_id',
        'itemable_type',
        'itemable_id',
        'system_quantity',
        'counted_quantity',
        'difference',
        'unit_cost',
        'difference_value',
        'notes',
        'counted_by',
        'counted_at',
    ];

    protected $casts = [
        'system_quantity' => 'integer',
        'counted_quantity' => 'integer',
        'difference' => 'integer',
        'unit_cost' => 'decimal:2',
        'difference_value' => 'decimal:2',
        'counted_at' => 'datetime',
    ];

    public function stockTake(): BelongsTo
    {
        return $this->belongsTo(StockTake::class);
    }

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }

    public function countedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    public function calculateDifference(): void
    {
        $this->difference = $this->counted_quantity - $this->system_quantity;
        $this->difference_value = $this->difference * $this->unit_cost;
    }
}

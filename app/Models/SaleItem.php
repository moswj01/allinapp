<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'itemable_type',
        'itemable_id',
        'item_name',
        'item_barcode',
        'quantity',
        'unit_price',
        'discount',
        'total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }

    public function calculateSubtotal(): void
    {
        $total = ($this->quantity ?? 0) * ($this->unit_price ?? 0);
        $this->total = max(0, $total - ($this->discount ?? 0));
    }
}

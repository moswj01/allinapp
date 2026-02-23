<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchOrderItem extends Model
{
    protected $fillable = [
        'branch_order_id',
        'product_id',
        'product_name',
        'quantity_requested',
        'quantity_approved',
        'quantity_shipped',
        'quantity_received',
        'unit_cost',
        'subtotal',
        'notes',
    ];

    protected $casts = [
        'quantity_requested' => 'integer',
        'quantity_approved' => 'integer',
        'quantity_shipped' => 'integer',
        'quantity_received' => 'integer',
        'unit_cost' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function branchOrder(): BelongsTo
    {
        return $this->belongsTo(BranchOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

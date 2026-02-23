<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\BelongsToTenant;


class BranchStock extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'branch_id',
        'stockable_type',
        'stockable_id',
        'quantity',
        'min_quantity',
        'reserved_quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'min_quantity' => 'integer',
        'reserved_quantity' => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function stockable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Alias for stockable — since all stockables are products after parts merge.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'stockable_id');
    }

    public function getAvailableQuantityAttribute(): int
    {
        return $this->quantity - $this->reserved_quantity;
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_quantity;
    }
}

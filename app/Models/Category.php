<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }

    public function scopeForProducts($query)
    {
        return $query->where('type', 'product');
    }

    public function scopeForParts($query)
    {
        return $query->where('type', 'part');
    }
}

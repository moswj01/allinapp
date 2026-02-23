<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'yearly_price',
        'billing_cycle',
        'max_users',
        'max_branches',
        'max_products',
        'max_repairs',
        'features',
        'is_active',
        'trial_days',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'max_users' => 'integer',
        'max_branches' => 'integer',
        'max_products' => 'integer',
        'max_repairs' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
        'trial_days' => 'integer',
        'sort_order' => 'integer',
    ];

    // Plan slug constants
    public const FREE = 'free';
    public const BASIC = 'basic';
    public const PRO = 'pro';
    public const ENTERPRISE = 'enterprise';

    // Feature constants
    public const FEATURE_REPAIRS = 'repairs';
    public const FEATURE_POS = 'pos';
    public const FEATURE_STOCK = 'stock';
    public const FEATURE_PURCHASING = 'purchasing';
    public const FEATURE_FINANCE = 'finance';
    public const FEATURE_REPORTS = 'reports';
    public const FEATURE_LINE_OA = 'line_oa';
    public const FEATURE_API = 'api';
    public const FEATURE_QUOTATIONS = 'quotations';
    public const FEATURE_MULTI_BRANCH = 'multi_branch';

    // Relationships
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    // Helpers
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    public function isUnlimited(string $field): bool
    {
        return ($this->$field ?? 0) === -1;
    }

    public function getMonthlyPrice(): float
    {
        return (float) $this->price;
    }

    public function getYearlyPrice(): float
    {
        return (float) $this->yearly_price;
    }

    public function getFormattedPrice(): string
    {
        if ($this->price <= 0) return 'ฟรี';
        return number_format($this->price, 0) . ' ฿/เดือน';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }
}

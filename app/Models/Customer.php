<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'address',
        'line_id',
        'facebook_id',
        'facebook_name',
        'customer_type',
        'credit_limit',
        'credit_days',
        'tax_id',
        'company_name',
        'membership_level',
        'points',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'credit_days' => 'integer',
        'points' => 'integer',
        'is_active' => 'boolean',
    ];

    public function repairs(): HasMany
    {
        return $this->hasMany(Repair::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function accountsReceivable(): HasMany
    {
        return $this->hasMany(AccountsReceivable::class);
    }

    public function getPriceType(): string
    {
        return match ($this->customer_type) {
            'wholesale' => 'wholesale',
            'vip' => 'vip',
            'partner' => 'partner',
            default => 'retail',
        };
    }

    public function hasCredit(): bool
    {
        return $this->credit_limit > 0 && $this->credit_days > 0;
    }

    // Customer types
    public const TYPE_RETAIL = 'retail';
    public const TYPE_WHOLESALE = 'wholesale';
    public const TYPE_VIP = 'vip';
    public const TYPE_PARTNER = 'partner';
    public const TYPE_CORPORATE = 'corporate';
}

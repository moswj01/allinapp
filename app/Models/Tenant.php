<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'email',
        'phone',
        'address',
        'tax_id',
        'logo',
        'plan_id',
        'status',
        'trial_ends_at',
        'subscription_starts_at',
        'subscription_ends_at',
        'settings',
        'suspension_reason',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
        'subscription_starts_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    // Status constants
    public const STATUS_TRIAL = 'trial';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_CANCELLED = 'cancelled';

    // ─── Relationships ─────────────────────────────────

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(TenantInvoice::class);
    }

    // ─── Status Checks ─────────────────────────────────

    public function isActive(): bool
    {
        return $this->is_active && in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_TRIAL]);
    }

    public function isTrial(): bool
    {
        return $this->status === self::STATUS_TRIAL;
    }

    public function isTrialExpired(): bool
    {
        return $this->isTrial() && $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    public function isSubscriptionExpired(): bool
    {
        return $this->subscription_ends_at && $this->subscription_ends_at->isPast();
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function daysLeftInTrial(): int
    {
        if (!$this->trial_ends_at) return 0;
        return max(0, (int) now()->diffInDays($this->trial_ends_at, false));
    }

    public function daysLeftInSubscription(): int
    {
        if (!$this->subscription_ends_at) return 0;
        return max(0, (int) now()->diffInDays($this->subscription_ends_at, false));
    }

    // ─── Plan Limits ───────────────────────────────────

    public function canAddUser(): bool
    {
        if ($this->slug === 'system-admin') return true;
        if (!$this->plan) return false;
        $max = $this->plan->max_users;
        return $max === -1 || $this->users()->count() < $max;
    }

    public function canAddBranch(): bool
    {
        if ($this->slug === 'system-admin') return true;
        if (!$this->plan) return false;
        $max = $this->plan->max_branches;
        return $max === -1 || $this->branches()->count() < $max;
    }

    public function canAddProduct(): bool
    {
        if ($this->slug === 'system-admin') return true;
        if (!$this->plan) return false;
        $max = $this->plan->max_products;
        return $max === -1 || Product::where('tenant_id', $this->id)->count() < $max;
    }

    public function hasFeature(string $feature): bool
    {
        // System admin tenant has all features
        if ($this->slug === 'system-admin') {
            return true;
        }

        $features = $this->plan->features ?? [];
        return in_array($feature, $features);
    }

    public function getUsageSummary(): array
    {
        return [
            'users' => [
                'current' => $this->users()->count(),
                'max' => $this->plan->max_users,
            ],
            'branches' => [
                'current' => $this->branches()->count(),
                'max' => $this->plan->max_branches,
            ],
            'products' => [
                'current' => Product::where('tenant_id', $this->id)->count(),
                'max' => $this->plan->max_products,
            ],
        ];
    }

    // ─── Actions ────────────────────────────────────────

    public function activate(): void
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'is_active' => true,
            'suspension_reason' => null,
        ]);
    }

    public function suspend(string $reason = ''): void
    {
        $this->update([
            'status' => self::STATUS_SUSPENDED,
            'is_active' => false,
            'suspension_reason' => $reason,
        ]);
    }

    public function cancel(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'is_active' => false,
        ]);
    }

    // ─── Scopes ────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeTrialExpired($query)
    {
        return $query->where('status', self::STATUS_TRIAL)
            ->where('trial_ends_at', '<', now());
    }

    // ─── Static Helpers ────────────────────────────────

    private static ?Tenant $currentTenant = null;

    public static function current(): ?Tenant
    {
        return self::$currentTenant;
    }

    public static function setCurrent(?Tenant $tenant): void
    {
        self::$currentTenant = $tenant;
    }

    public static function currentId(): ?int
    {
        return self::$currentTenant?->id;
    }
}

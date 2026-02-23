<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BelongsToTenant trait
 *
 * Add to any model that should be scoped by tenant.
 * Automatically:
 *  - Applies a global scope to filter by current tenant
 *  - Sets tenant_id on creation
 *  - Provides tenant() relationship
 */
trait BelongsToTenant
{
    /**
     * Initialize the trait: add tenant_id to fillable automatically
     */
    public function initializeBelongsToTenant(): void
    {
        if (!in_array('tenant_id', $this->fillable)) {
            $this->fillable[] = 'tenant_id';
        }
    }

    public static function bootBelongsToTenant(): void
    {
        // Global scope: filter by current tenant
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenant = Tenant::current();
            if ($tenant) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenant->id);
            }
        });

        // Auto-set tenant_id when creating
        static::creating(function (Model $model) {
            if (!$model->tenant_id && Tenant::currentId()) {
                $model->tenant_id = Tenant::currentId();
            }
        });
    }

    /**
     * Tenant relationship
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope to filter by a specific tenant
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->withoutGlobalScope('tenant')
            ->where($this->getTable() . '.tenant_id', $tenantId);
    }

    /**
     * Scope to include all tenants (bypass tenant scoping)
     */
    public function scopeAllTenants(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }
}

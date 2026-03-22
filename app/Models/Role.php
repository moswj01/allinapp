<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;


class Role extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];

        // Super wildcard — grants all permissions (e.g. owner role)
        if (in_array('*', $permissions)) {
            return true;
        }

        // Check exact match
        if (in_array($permission, $permissions)) {
            return true;
        }

        // Check wildcard permissions (e.g., repairs.* matches repairs.view)
        foreach ($permissions as $perm) {
            if (str_ends_with($perm, '.*')) {
                $prefix = substr($perm, 0, -1); // Remove the *
                if (str_starts_with($permission, $prefix)) {
                    return true;
                }
            }
        }

        return false;
    }

    // Predefined role slugs
    public const OWNER = 'owner';
    public const ADMIN = 'admin';
    public const MANAGER = 'manager';
    public const SALES = 'sales';
    public const TECHNICIAN = 'technician';
    public const WAREHOUSE = 'warehouse';
    public const ACCOUNTANT = 'accountant';
}

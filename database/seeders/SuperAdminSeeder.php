<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\Role;
use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure plans exist
        $this->call(PlanSeeder::class);

        DB::transaction(function () {
            // Create a "system" tenant for super admin
            $plan = Plan::where('slug', Plan::ENTERPRISE)->first();

            $tenant = Tenant::firstOrCreate(
                ['slug' => 'system-admin'],
                [
                    'name' => 'System Administration',
                    'email' => 'admin@allinmobile.com',
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'is_active' => true,
                    'subscription_starts_at' => now(),
                    'subscription_ends_at' => now()->addYears(10),
                ]
            );

            // Use existing owner role or create one
            $role = Role::withoutGlobalScopes()->where('slug', 'owner')->first();
            if (!$role) {
                $role = Role::withoutGlobalScopes()->create([
                    'name' => 'Owner',
                    'slug' => 'owner',
                    'tenant_id' => $tenant->id,
                    'permissions' => ['*'],
                ]);
            } else {
                // Assign to this tenant if not already assigned
                if (!$role->tenant_id) {
                    $role->update(['tenant_id' => $tenant->id]);
                }
            }

            // Use existing main branch or create one
            $branch = Branch::withoutGlobalScopes()->where('is_main', true)->first();
            if (!$branch) {
                $branch = Branch::withoutGlobalScopes()->create([
                    'name' => 'สำนักงานใหญ่',
                    'code' => 'HQ-SA',
                    'address' => '-',
                    'phone' => '-',
                    'is_main' => true,
                    'is_active' => true,
                    'tenant_id' => $tenant->id,
                ]);
            } else {
                if (!$branch->tenant_id) {
                    $branch->update(['tenant_id' => $tenant->id]);
                }
            }

            // Create the super admin user
            $user = User::withoutGlobalScopes()->updateOrCreate(
                ['email' => 'superadmin@allinmobile.com'],
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make('admin1234'),
                    'tenant_id' => $tenant->id,
                    'is_super_admin' => true,
                    'role_id' => $role->id,
                    'branch_id' => $branch->id,
                    'is_active' => true,
                ]
            );

            $this->command->info('Super Admin created successfully!');
            $this->command->info('Email: superadmin@allinmobile.com');
            $this->command->info('Password: admin1234');
        });
    }
}

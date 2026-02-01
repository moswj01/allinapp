<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('slug', Role::ADMIN)->first();
        $ownerRole = Role::where('slug', Role::OWNER)->first();
        $mainBranch = Branch::where('is_main', true)->first();

        // Create owner account
        User::create([
            'name' => 'Owner',
            'email' => 'owner@allinapp.com',
            'password' => Hash::make('password'),
            'role_id' => $ownerRole->id,
            'branch_id' => $mainBranch->id,
            'employee_code' => 'EMP001',
            'is_active' => true,
        ]);

        // Create admin account
        User::create([
            'name' => 'Admin',
            'email' => 'admin@allinapp.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'branch_id' => $mainBranch->id,
            'employee_code' => 'EMP002',
            'is_active' => true,
        ]);
    }
}

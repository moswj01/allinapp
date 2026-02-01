<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'เจ้าของกิจการ',
                'slug' => Role::OWNER,
                'description' => 'สิทธิ์เต็มรูปแบบ ดูรายงานทุกสาขา',
                'permissions' => [
                    // All permissions
                    'dashboard.view',
                    'dashboard.all_branches',
                    'repairs.*',
                    'products.*',
                    'parts.*',
                    'categories.*',
                    'customers.*',
                    'sales.*',
                    'quotations.*',
                    'purchase_orders.*',
                    'stock_transfers.*',
                    'stock_takes.*',
                    'reports.*',
                    'settings.*',
                    'users.*',
                    'branches.*',
                    'roles.*',
                    'petty_cash.*',
                    'daily_settlement.*',
                    'accounts_receivable.*',
                    'audit_logs.view',
                ],
            ],
            [
                'name' => 'ผู้ดูแลระบบ',
                'slug' => Role::ADMIN,
                'description' => 'จัดการระบบทั้งหมด',
                'permissions' => [
                    'dashboard.view',
                    'dashboard.all_branches',
                    'repairs.*',
                    'products.*',
                    'parts.*',
                    'categories.*',
                    'customers.*',
                    'sales.*',
                    'quotations.*',
                    'purchase_orders.*',
                    'stock_transfers.*',
                    'stock_takes.*',
                    'reports.*',
                    'settings.*',
                    'users.*',
                    'branches.*',
                    'petty_cash.*',
                    'daily_settlement.*',
                    'accounts_receivable.*',
                    'audit_logs.view',
                ],
            ],
            [
                'name' => 'ผู้จัดการสาขา',
                'slug' => Role::MANAGER,
                'description' => 'จัดการสาขา อนุมัติงานภายในสาขา',
                'permissions' => [
                    'dashboard.view',
                    'repairs.*',
                    'products.view',
                    'products.edit',
                    'parts.view',
                    'parts.edit',
                    'customers.*',
                    'sales.*',
                    'quotations.*',
                    'stock_transfers.create',
                    'stock_transfers.view',
                    'stock_takes.*',
                    'reports.branch',
                    'users.view',
                    'petty_cash.*',
                    'daily_settlement.*',
                    'accounts_receivable.view',
                ],
            ],
            [
                'name' => 'พนักงานขาย',
                'slug' => Role::SALES,
                'description' => 'ขายสินค้า รับงานซ่อม',
                'permissions' => [
                    'dashboard.view',
                    'repairs.create',
                    'repairs.view',
                    'repairs.edit',
                    'products.view',
                    'parts.view',
                    'categories.view',
                    'customers.*',
                    'sales.*',
                    'quotations.create',
                    'quotations.view',
                    'petty_cash.create',
                    'petty_cash.view',
                ],
            ],
            [
                'name' => 'ช่างซ่อม',
                'slug' => Role::TECHNICIAN,
                'description' => 'ซ่อมเครื่อง เบิกอะไหล่',
                'permissions' => [
                    'dashboard.view',
                    'repairs.view',
                    'repairs.update_status',
                    'repairs.add_parts',
                    'parts.view',
                    'parts.requisition',
                ],
            ],
            [
                'name' => 'คลังสินค้า',
                'slug' => Role::WAREHOUSE,
                'description' => 'จัดการสต๊อก เบิก-จ่ายอะไหล่',
                'permissions' => [
                    'dashboard.view',
                    'products.*',
                    'parts.*',
                    'categories.*',
                    'purchase_orders.*',
                    'stock_transfers.*',
                    'stock_takes.*',
                    'suppliers.*',
                ],
            ],
            [
                'name' => 'บัญชี',
                'slug' => Role::ACCOUNTANT,
                'description' => 'ตรวจสอบบัญชี รายงานการเงิน',
                'permissions' => [
                    'dashboard.view',
                    'sales.view',
                    'repairs.view',
                    'reports.*',
                    'accounts_receivable.*',
                    'daily_settlement.view',
                    'daily_settlement.approve',
                    'petty_cash.view',
                    'petty_cash.approve',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }
    }
}

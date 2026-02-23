<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FreshStartSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->warn('⚠️  กำลังลบข้อมูลทั้งหมดเพื่อเริ่มต้นใหม่...');

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Tables to truncate (ordered by dependency - child tables first)
        $tables = [
            // Transaction detail tables
            'sale_items',
            'repair_parts',
            'repair_logs',
            'repair_communications',
            'quotation_items',
            'purchase_order_items',
            'branch_order_items',
            'stock_take_items',
            'stock_transfer_items',
            'goods_receipt_items',
            'ar_payments',

            // Transaction tables
            'sales',
            'repairs',
            'quotations',
            'purchase_orders',
            'branch_orders',
            'stock_takes',
            'stock_transfers',
            'goods_receipts',
            'accounts_receivable',

            // Stock & movement tables
            'stock_movements',
            'stock_transactions',
            'branch_stocks',

            // Finance tables
            'daily_settlements',
            'petty_cash',
            'tenant_invoices',

            // System tables
            'audit_logs',
            'notifications',

            // Master data tables
            'products',
            'categories',
            'customers',
            'suppliers',

            // Tenant & user tables
            'users',
            'branches',
            'roles',
            'settings',
            'tenants',
            'plans',

            // Cache & job tables
            'cache',
            'cache_locks',
            'jobs',
            'job_batches',
            'failed_jobs',
            'sessions',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                $this->command->info("  ✓ Truncated: {$table}");
            }
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->newLine();
        $this->command->info('🌱 กำลัง Seed ข้อมูลเริ่มต้น...');

        // Seed essential data
        $this->call(PlanSeeder::class);        // Plans (Free, Basic, Pro, Enterprise)
        $this->call(SuperAdminSeeder::class);  // SuperAdmin + System Tenant + Owner Role + HQ Branch

        $this->command->newLine();
        $this->command->info('✅ ระบบพร้อมใช้งานแล้ว!');
        $this->command->newLine();
        $this->command->info('📋 ข้อมูลเริ่มต้น:');
        $this->command->info('   - แผนบริการ 4 แผน (Free, Basic, Pro, Enterprise)');
        $this->command->info('   - Super Admin: superadmin@allinmobile.com / admin1234');
        $this->command->info('   - ลูกค้าใหม่สามารถสมัครได้ที่หน้า Register');
    }
}

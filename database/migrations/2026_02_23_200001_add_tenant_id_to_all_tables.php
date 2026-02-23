<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add tenant_id to all existing tables for multi-tenancy
     */
    public function up(): void
    {
        $tablesWithCascade = [
            'branches',
            'roles',
            'categories',
            'suppliers',
            'products',
            'customers',
            'repairs',
            'sales',
            'quotations',
            'purchase_orders',
            'goods_receipts',
            'stock_takes',
            'stock_transfers',
            'stock_movements',
            'branch_stocks',
            'branch_orders',
            'accounts_receivable',
            'daily_settlements',
            'petty_cash',
            'audit_logs',
            'notifications',
            'settings',
        ];

        // Users - special case: add tenant_id AND is_super_admin
        if (!Schema::hasColumn('users', 'tenant_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
                $table->index('tenant_id');
            });
        }
        if (!Schema::hasColumn('users', 'is_super_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_super_admin')->default(false)->after('is_active');
            });
        }

        // All other tables - add tenant_id with cascade delete
        foreach ($tablesWithCascade as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
                    $table->index('tenant_id');
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'users',
            'branches',
            'roles',
            'categories',
            'suppliers',
            'products',
            'customers',
            'repairs',
            'sales',
            'quotations',
            'purchase_orders',
            'goods_receipts',
            'stock_takes',
            'stock_transfers',
            'stock_movements',
            'branch_stocks',
            'branch_orders',
            'accounts_receivable',
            'daily_settlements',
            'petty_cash',
            'audit_logs',
            'notifications',
            'settings',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropForeign([$tableName === 'users' ? 'tenant_id' : 'tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_super_admin');
        });
    }
};

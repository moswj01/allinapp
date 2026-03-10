<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Change global unique indexes to tenant-scoped (composite) unique indexes.
     * This is required for multi-tenancy so that each tenant can have their own
     * 'HQ' branch code, 'SKU001' products, etc.
     */
    public function up(): void
    {
        // branches.code: unique → tenant_id + code
        Schema::table('branches', function (Blueprint $table) {
            $table->dropUnique('branches_code_unique');
            $table->unique(['tenant_id', 'code'], 'branches_tenant_code_unique');
        });

        // suppliers.code
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropUnique('suppliers_code_unique');
            $table->unique(['tenant_id', 'code'], 'suppliers_tenant_code_unique');
        });

        // customers.code
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_code_unique');
            $table->unique(['tenant_id', 'code'], 'customers_tenant_code_unique');
        });

        // products.barcode
        if ($this->hasIndex('products', 'products_barcode_unique')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropUnique('products_barcode_unique');
                $table->unique(['tenant_id', 'barcode'], 'products_tenant_barcode_unique');
            });
        }

        // products.sku
        if ($this->hasIndex('products', 'products_sku_unique')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropUnique('products_sku_unique');
                $table->unique(['tenant_id', 'sku'], 'products_tenant_sku_unique');
            });
        }

        // repairs.repair_number
        Schema::table('repairs', function (Blueprint $table) {
            $table->dropUnique('repairs_repair_number_unique');
            $table->unique(['tenant_id', 'repair_number'], 'repairs_tenant_repair_number_unique');
        });

        // sales.sale_number
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique('sales_sale_number_unique');
            $table->unique(['tenant_id', 'sale_number'], 'sales_tenant_sale_number_unique');
        });

        // quotations.quotation_number
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropUnique('quotations_quotation_number_unique');
            $table->unique(['tenant_id', 'quotation_number'], 'quotations_tenant_quotation_number_unique');
        });

        // purchase_orders.po_number
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropUnique('purchase_orders_po_number_unique');
            $table->unique(['tenant_id', 'po_number'], 'purchase_orders_tenant_po_number_unique');
        });

        // goods_receipts.gr_number
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropUnique('goods_receipts_gr_number_unique');
            $table->unique(['tenant_id', 'gr_number'], 'goods_receipts_tenant_gr_number_unique');
        });

        // stock_takes.stock_take_number
        Schema::table('stock_takes', function (Blueprint $table) {
            $table->dropUnique('stock_takes_stock_take_number_unique');
            $table->unique(['tenant_id', 'stock_take_number'], 'stock_takes_tenant_stock_take_number_unique');
        });

        // stock_transfers.transfer_number
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropUnique('stock_transfers_transfer_number_unique');
            $table->unique(['tenant_id', 'transfer_number'], 'stock_transfers_tenant_transfer_number_unique');
        });

        // branch_orders.order_number
        Schema::table('branch_orders', function (Blueprint $table) {
            $table->dropUnique('branch_orders_order_number_unique');
            $table->unique(['tenant_id', 'order_number'], 'branch_orders_tenant_order_number_unique');
        });
    }

    public function down(): void
    {
        // Revert to global unique indexes
        Schema::table('branches', function (Blueprint $table) {
            $table->dropUnique('branches_tenant_code_unique');
            $table->unique('code', 'branches_code_unique');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropUnique('suppliers_tenant_code_unique');
            $table->unique('code', 'suppliers_code_unique');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_tenant_code_unique');
            $table->unique('code', 'customers_code_unique');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_tenant_barcode_unique');
            $table->unique('barcode', 'products_barcode_unique');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_tenant_sku_unique');
            $table->unique('sku', 'products_sku_unique');
        });

        Schema::table('repairs', function (Blueprint $table) {
            $table->dropUnique('repairs_tenant_repair_number_unique');
            $table->unique('repair_number', 'repairs_repair_number_unique');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique('sales_tenant_sale_number_unique');
            $table->unique('sale_number', 'sales_sale_number_unique');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropUnique('quotations_tenant_quotation_number_unique');
            $table->unique('quotation_number', 'quotations_quotation_number_unique');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropUnique('purchase_orders_tenant_po_number_unique');
            $table->unique('po_number', 'purchase_orders_po_number_unique');
        });

        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropUnique('goods_receipts_tenant_gr_number_unique');
            $table->unique('gr_number', 'goods_receipts_gr_number_unique');
        });

        Schema::table('stock_takes', function (Blueprint $table) {
            $table->dropUnique('stock_takes_tenant_stock_take_number_unique');
            $table->unique('stock_take_number', 'stock_takes_stock_take_number_unique');
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropUnique('stock_transfers_tenant_transfer_number_unique');
            $table->unique('transfer_number', 'stock_transfers_transfer_number_unique');
        });

        Schema::table('branch_orders', function (Blueprint $table) {
            $table->dropUnique('branch_orders_tenant_order_number_unique');
            $table->unique('order_number', 'branch_orders_order_number_unique');
        });
    }

    /**
     * Check if an index exists on a table
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = Schema::getIndexes($table);
        foreach ($indexes as $index) {
            if ($index['name'] === $indexName) {
                return true;
            }
        }
        return false;
    }
};

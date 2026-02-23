<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing columns to daily_settlements
        Schema::table('daily_settlements', function (Blueprint $table) {
            $table->decimal('qr_sales', 12, 2)->default(0)->after('transfer_sales');
            $table->decimal('cash_in', 12, 2)->default(0)->after('petty_cash_expenses');
            $table->decimal('cash_out', 12, 2)->default(0)->after('cash_in');
            $table->text('difference_reason')->nullable()->after('difference');
            $table->decimal('repair_revenue', 12, 2)->default(0)->after('repair_income');
            $table->decimal('product_revenue', 12, 2)->default(0)->after('repair_revenue');
            $table->decimal('part_revenue', 12, 2)->default(0)->after('product_revenue');
            $table->foreignId('created_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
        });

        // Copy existing data: user_id -> created_by, repair_income -> repair_revenue
        DB::table('daily_settlements')->update([
            'created_by' => DB::raw('user_id'),
            'repair_revenue' => DB::raw('repair_income'),
        ]);

        // Add missing columns to petty_cash
        Schema::table('petty_cash', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->datetime('approved_at')->nullable()->after('approved_by');
        });

        // Copy user_id -> created_by for petty_cash
        DB::table('petty_cash')->update([
            'created_by' => DB::raw('user_id'),
        ]);
    }

    public function down(): void
    {
        Schema::table('daily_settlements', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['qr_sales', 'cash_in', 'cash_out', 'difference_reason', 'repair_revenue', 'product_revenue', 'part_revenue', 'created_by']);
        });

        Schema::table('petty_cash', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['created_by', 'approved_by', 'approved_at']);
        });
    }
};

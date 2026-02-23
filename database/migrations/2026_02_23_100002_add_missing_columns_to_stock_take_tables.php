<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing columns to stock_takes
        Schema::table('stock_takes', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_takes', 'started_at')) {
                $table->datetime('started_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('stock_takes', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('stock_takes', 'approved_at')) {
                $table->datetime('approved_at')->nullable()->after('approved_by');
            }
        });

        // Add missing columns to stock_take_items
        Schema::table('stock_take_items', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_take_items', 'unit_cost')) {
                $table->decimal('unit_cost', 12, 2)->default(0)->after('difference');
            }
            if (!Schema::hasColumn('stock_take_items', 'difference_value')) {
                $table->decimal('difference_value', 12, 2)->default(0)->after('unit_cost');
            }
            if (!Schema::hasColumn('stock_take_items', 'counted_by')) {
                $table->foreignId('counted_by')->nullable()->after('is_adjusted')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('stock_take_items', 'counted_at')) {
                $table->datetime('counted_at')->nullable()->after('counted_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_takes', function (Blueprint $table) {
            $table->dropColumn(['started_at']);
            if (Schema::hasColumn('stock_takes', 'approved_by')) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn(['approved_by', 'approved_at']);
            }
        });

        Schema::table('stock_take_items', function (Blueprint $table) {
            $table->dropColumn(['unit_cost', 'difference_value']);
            if (Schema::hasColumn('stock_take_items', 'counted_by')) {
                $table->dropForeign(['counted_by']);
                $table->dropColumn(['counted_by', 'counted_at']);
            }
        });
    }
};

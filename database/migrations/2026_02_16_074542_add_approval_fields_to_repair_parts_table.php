<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repair_parts', function (Blueprint $table) {
            if (!Schema::hasColumn('repair_parts', 'part_name')) {
                $table->string('part_name')->nullable()->after('product_id');
            }
            if (!Schema::hasColumn('repair_parts', 'total_price')) {
                $table->decimal('total_price', 12, 2)->default(0)->after('unit_price');
            }
            if (!Schema::hasColumn('repair_parts', 'requisition_number')) {
                $table->string('requisition_number')->nullable()->after('status');
            }
            if (!Schema::hasColumn('repair_parts', 'issued_by')) {
                $table->foreignId('issued_by')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('repair_parts', 'issued_at')) {
                $table->datetime('issued_at')->nullable()->after('issued_by');
            }
            if (!Schema::hasColumn('repair_parts', 'returned_quantity')) {
                $table->integer('returned_quantity')->default(0)->after('issued_at');
            }
            if (!Schema::hasColumn('repair_parts', 'returned_at')) {
                $table->datetime('returned_at')->nullable()->after('returned_quantity');
            }
            if (!Schema::hasColumn('repair_parts', 'return_reason')) {
                $table->text('return_reason')->nullable()->after('returned_at');
            }
            if (!Schema::hasColumn('repair_parts', 'rejected_by')) {
                $table->foreignId('rejected_by')->nullable()->after('return_reason');
            }
            if (!Schema::hasColumn('repair_parts', 'rejected_at')) {
                $table->datetime('rejected_at')->nullable()->after('rejected_by');
            }
            if (!Schema::hasColumn('repair_parts', 'reject_reason')) {
                $table->text('reject_reason')->nullable()->after('rejected_at');
            }
        });

        // Rename part_id to product_id if needed
        if (Schema::hasColumn('repair_parts', 'part_id') && !Schema::hasColumn('repair_parts', 'product_id')) {
            Schema::table('repair_parts', function (Blueprint $table) {
                $table->renameColumn('part_id', 'product_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('repair_parts', function (Blueprint $table) {
            $columns = [
                'part_name',
                'total_price',
                'requisition_number',
                'issued_by',
                'issued_at',
                'returned_quantity',
                'returned_at',
                'return_reason',
                'rejected_by',
                'rejected_at',
                'reject_reason'
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('repair_parts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

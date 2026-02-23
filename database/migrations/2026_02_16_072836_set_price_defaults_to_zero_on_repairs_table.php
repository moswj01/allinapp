<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update existing null values to 0 in repairs
        $repairCols = ['estimated_cost', 'service_cost', 'parts_cost', 'discount', 'total_cost', 'deposit', 'paid_amount'];
        foreach ($repairCols as $col) {
            DB::table('repairs')->whereNull($col)->update([$col => 0]);
        }

        // Update existing null values to 0 in repair_parts
        DB::table('repair_parts')->whereNull('unit_price')->update(['unit_price' => 0]);
        DB::table('repair_parts')->whereNull('unit_cost')->update(['unit_cost' => 0]);
    }

    public function down(): void
    {
        // No need to reverse - defaults of 0 are safe
    }
};

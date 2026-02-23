<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update existing null values to 0
        DB::table('products')->whereNull('cost')->update(['cost' => 0]);
        DB::table('products')->whereNull('retail_price')->update(['retail_price' => 0]);
        DB::table('products')->whereNull('wholesale_price')->update(['wholesale_price' => 0]);
        DB::table('products')->whereNull('vip_price')->update(['vip_price' => 0]);
        DB::table('products')->whereNull('partner_price')->update(['partner_price' => 0]);

        // Set column defaults
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost', 12, 2)->default(0)->change();
            $table->decimal('retail_price', 12, 2)->default(0)->change();
            $table->decimal('wholesale_price', 12, 2)->default(0)->change();
            $table->decimal('vip_price', 12, 2)->default(0)->change();
            $table->decimal('partner_price', 12, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        // No need to reverse - defaults of 0 are safe
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('repair_parts', function (Blueprint $table) {
            // Drop the old foreign key that references the defunct `parts` table
            $table->dropForeign('repair_parts_part_id_foreign');

            // Add new foreign key referencing `products` table
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('repair_parts', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
    }
};

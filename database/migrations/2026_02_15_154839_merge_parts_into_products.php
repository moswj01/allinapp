<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add part-specific columns to products table (idempotent)
        if (!Schema::hasColumn('products', 'compatible_brands')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('supplier_id')->nullable()->after('branch_id')->constrained()->nullOnDelete();
                $table->string('compatible_brands')->nullable()->after('description');
                $table->string('compatible_models')->nullable()->after('compatible_brands');
            });
        }

        // 2. Migrate data from parts → products (only if parts table exists)
        if (Schema::hasTable('parts')) {
            // Check if column is still named part_id (not yet renamed)
            $partIdColumn = Schema::hasColumn('repair_parts', 'part_id') ? 'part_id' : 'product_id';

            $parts = DB::table('parts')->get();
            foreach ($parts as $part) {
                $newId = DB::table('products')->insertGetId([
                    'barcode' => $part->barcode,
                    'sku' => $part->sku,
                    'name' => $part->name,
                    'category_id' => $part->category_id,
                    'supplier_id' => $part->supplier_id,
                    'description' => $part->description,
                    'unit' => $part->unit,
                    'compatible_brands' => $part->compatible_brands,
                    'compatible_models' => $part->compatible_models,
                    'cost' => $part->cost,
                    'retail_price' => $part->price,
                    'quantity' => $part->quantity,
                    'min_stock' => $part->min_stock,
                    'reorder_point' => $part->reorder_point,
                    'source' => $part->source,
                    'image' => $part->image,
                    'is_active' => $part->is_active,
                    'created_at' => $part->created_at,
                    'updated_at' => $part->updated_at,
                ]);

                // Update repair_parts references
                DB::table('repair_parts')
                    ->where($partIdColumn, $part->id)
                    ->update([$partIdColumn => $newId]);

                // Update branch_stocks morph references
                DB::table('branch_stocks')
                    ->where('stockable_type', 'App\\Models\\Part')
                    ->where('stockable_id', $part->id)
                    ->update([
                        'stockable_type' => 'App\\Models\\Product',
                        'stockable_id' => $newId,
                    ]);

                // Update stock_movements morph references
                DB::table('stock_movements')
                    ->where('movable_type', 'App\\Models\\Part')
                    ->where('movable_id', $part->id)
                    ->update([
                        'movable_type' => 'App\\Models\\Product',
                        'movable_id' => $newId,
                    ]);

                // Update sale_items morph references
                DB::table('sale_items')
                    ->where('itemable_type', 'App\\Models\\Part')
                    ->where('itemable_id', $part->id)
                    ->update([
                        'itemable_type' => 'App\\Models\\Product',
                        'itemable_id' => $newId,
                    ]);
            }
        }

        // 3. Rename repair_parts.part_id → product_id (idempotent)
        if (Schema::hasColumn('repair_parts', 'part_id')) {
            Schema::table('repair_parts', function (Blueprint $table) {
                $table->renameColumn('part_id', 'product_id');
            });
        }

        // 4. Update category types: convert 'part' to 'product'
        DB::table('categories')
            ->where('type', 'part')
            ->update(['type' => 'product']);

        // 5. Drop parts table (disable FK checks)
        if (Schema::hasTable('parts')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            Schema::dropIfExists('parts');
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void
    {
        // Re-create parts table
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->string('barcode')->nullable();
            $table->string('sku')->nullable();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('unit')->nullable();
            $table->string('compatible_brands')->nullable();
            $table->string('compatible_models')->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('quantity')->default(0);
            $table->integer('min_stock')->default(0);
            $table->integer('reorder_point')->default(0);
            $table->string('source')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Rename product_id back to part_id
        Schema::table('repair_parts', function (Blueprint $table) {
            $table->renameColumn('product_id', 'part_id');
        });

        // Remove added columns from products
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['supplier_id', 'compatible_brands', 'compatible_models']);
        });
    }
};

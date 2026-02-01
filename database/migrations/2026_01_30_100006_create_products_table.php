<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('barcode')->nullable()->unique();
                $table->string('sku')->nullable()->unique();
                $table->string('name');
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
                $table->text('description')->nullable();
                $table->string('unit')->default('ชิ้น');
                // Pricing
                $table->decimal('cost', 12, 2)->default(0); // ต้นทุน
                $table->decimal('retail_price', 12, 2)->default(0); // ราคาปลีก
                $table->decimal('wholesale_price', 12, 2)->default(0); // ราคาส่ง
                $table->decimal('vip_price', 12, 2)->default(0); // ราคา VIP
                $table->decimal('partner_price', 12, 2)->default(0); // ราคา Partner VVVIP
                // Stock
                $table->integer('quantity')->default(0);
                $table->integer('min_stock')->default(0);
                $table->integer('max_stock')->default(0);
                $table->integer('reorder_point')->default(0); // จุดสั่งซื้อ
                // Meta
                $table->string('source')->nullable(); // ที่มาสินค้า
                $table->string('image')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

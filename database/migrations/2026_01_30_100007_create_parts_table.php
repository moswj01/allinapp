<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('parts')) {
            Schema::create('parts', function (Blueprint $table) {
                $table->id();
                $table->string('barcode')->nullable()->unique();
                $table->string('sku')->nullable()->unique();
                $table->string('name');
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
                $table->text('description')->nullable();
                $table->string('unit')->default('ชิ้น');
                // สำหรับอะไหล่
                $table->string('compatible_brands')->nullable(); // ยี่ห้อที่ใช้ได้
                $table->string('compatible_models')->nullable(); // รุ่นที่ใช้ได้
                // Pricing
                $table->decimal('cost', 12, 2)->default(0);
                $table->decimal('price', 12, 2)->default(0); // ราคาขาย/เบิก
                // Stock
                $table->integer('quantity')->default(0);
                $table->integer('min_stock')->default(0);
                $table->integer('reorder_point')->default(0);
                // Meta
                $table->string('source')->nullable();
                $table->string('image')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};

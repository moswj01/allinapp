<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ใบสั่งซื้อจากสาขา → สาขาใหญ่
        Schema::create('branch_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('branch_id')->constrained()->comment('สาขาที่สั่ง');
            $table->unsignedBigInteger('main_branch_id')->comment('สาขาใหญ่');
            $table->foreign('main_branch_id')->references('id')->on('branches');
            $table->enum('status', ['draft', 'pending', 'approved', 'preparing', 'shipped', 'received', 'cancelled'])
                ->default('draft');
            $table->text('notes')->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users');
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('shipped_by')->nullable();
            $table->foreign('shipped_by')->references('id')->on('users');
            $table->timestamp('shipped_at')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            $table->foreign('received_by')->references('id')->on('users');
            $table->timestamp('received_at')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->foreign('cancelled_by')->references('id')->on('users');
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->timestamps();
        });

        // รายการสินค้าในใบสั่งซื้อ
        Schema::create('branch_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->string('product_name');
            $table->integer('quantity_requested');
            $table->integer('quantity_approved')->default(0);
            $table->integer('quantity_shipped')->default(0);
            $table->integer('quantity_received')->default(0);
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_order_items');
        Schema::dropIfExists('branch_orders');
    }
};

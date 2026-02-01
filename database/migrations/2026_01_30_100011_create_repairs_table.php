<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('repairs')) {
            Schema::create('repairs', function (Blueprint $table) {
                $table->id();
                $table->string('repair_number')->unique();
                $table->foreignId('branch_id')->constrained('branches');
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();

                // Customer Info (สำหรับ Walk-in)
                $table->string('customer_name');
                $table->string('customer_phone');
                $table->string('customer_line_id')->nullable();

                // Device Info
                $table->string('device_type'); // โทรศัพท์, แท็บเล็ต, โน๊ตบุ๊ค, etc
                $table->string('device_brand');
                $table->string('device_model')->nullable();
                $table->string('device_color')->nullable();
                $table->string('device_serial')->nullable();
                $table->string('device_imei')->nullable();
                $table->string('device_password')->nullable(); // รหัสเครื่อง (เก็บเข้ารหัส)
                $table->text('device_condition')->nullable(); // สภาพเครื่องตอนรับ
                $table->json('device_accessories')->nullable(); // อุปกรณ์เสริมที่รับมาด้วย

                // Problem & Diagnosis
                $table->text('problem_description'); // อาการเสีย
                $table->text('diagnosis')->nullable(); // การวินิจฉัย
                $table->text('solution')->nullable(); // วิธีแก้ไข

                // Status (Kanban)
                $table->string('status')->default('pending');
                // pending, waiting_parts, quoted, confirmed, in_progress, qc, completed, delivered, cancelled, claim

                // Assignment
                $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();

                // Pricing
                $table->decimal('estimated_cost', 12, 2)->default(0);
                $table->decimal('service_cost', 12, 2)->default(0);
                $table->decimal('parts_cost', 12, 2)->default(0);
                $table->decimal('discount', 12, 2)->default(0);
                $table->decimal('total_cost', 12, 2)->default(0);

                // Payment
                $table->decimal('deposit', 12, 2)->default(0); // มัดจำ
                $table->decimal('paid_amount', 12, 2)->default(0);
                $table->string('payment_status')->default('unpaid'); // unpaid, partial, paid
                $table->string('payment_method')->nullable(); // cash, transfer, credit_card

                // Warranty
                $table->integer('warranty_days')->default(0);
                $table->text('warranty_conditions')->nullable();
                $table->date('warranty_expires_at')->nullable();

                // Dates
                $table->datetime('received_at');
                $table->datetime('estimated_completion')->nullable();
                $table->datetime('completed_at')->nullable();
                $table->datetime('delivered_at')->nullable();

                // Claim Info
                $table->boolean('is_claim')->default(false);
                $table->unsignedBigInteger('original_repair_id')->nullable();

                // Priority & Notes
                $table->string('priority')->default('normal'); // low, normal, high, urgent
                $table->text('internal_notes')->nullable();
                $table->text('customer_notes')->nullable();

                $table->timestamps();

                $table->index('status');
                $table->index('device_imei');
                $table->index('device_serial');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('repairs');
    }
};

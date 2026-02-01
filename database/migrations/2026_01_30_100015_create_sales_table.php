<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sales / POS
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_number')->unique();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users'); // Cashier

            $table->string('sale_type')->default('retail'); // retail, wholesale, vip, partner

            // Amounts
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('vat', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // Payment
            $table->string('payment_method'); // cash, transfer, credit_card, mixed, credit
            $table->decimal('cash_received', 12, 2)->default(0);
            $table->decimal('change_amount', 12, 2)->default(0);
            $table->string('payment_status')->default('paid'); // paid, partial, credit
            $table->date('credit_due_date')->nullable();

            $table->string('reference_number')->nullable(); // สลิปโอนเงิน
            $table->text('notes')->nullable();

            // Tax Invoice
            $table->boolean('has_tax_invoice')->default(false);
            $table->string('tax_invoice_number')->nullable();

            $table->string('status')->default('completed'); // pending, completed, cancelled, refunded
            $table->datetime('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index('sale_number');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};

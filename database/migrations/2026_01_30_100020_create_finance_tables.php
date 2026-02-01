<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Petty Cash - รายจ่ายจิปาถะ
        Schema::create('petty_cash', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('user_id')->constrained('users');
            $table->string('type'); // expense, income, withdrawal, deposit
            $table->string('category'); // utilities, supplies, transportation, food, other
            $table->decimal('amount', 12, 2);
            $table->text('description');
            $table->string('receipt_number')->nullable();
            $table->string('receipt_image')->nullable();
            $table->date('transaction_date');
            $table->timestamps();
        });

        // Daily Settlement - ปิดยอดวัน
        Schema::create('daily_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('user_id')->constrained('users'); // Closed by
            $table->date('settlement_date');

            // Sales Summary
            $table->decimal('total_sales', 12, 2)->default(0);
            $table->decimal('cash_sales', 12, 2)->default(0);
            $table->decimal('transfer_sales', 12, 2)->default(0);
            $table->decimal('card_sales', 12, 2)->default(0);
            $table->decimal('credit_sales', 12, 2)->default(0);

            // Repair Summary
            $table->decimal('repair_income', 12, 2)->default(0);

            // Expenses
            $table->decimal('petty_cash_expenses', 12, 2)->default(0);

            // Cash Count
            $table->decimal('opening_cash', 12, 2)->default(0);
            $table->decimal('expected_cash', 12, 2)->default(0);
            $table->decimal('actual_cash', 12, 2)->default(0);
            $table->decimal('difference', 12, 2)->default(0);

            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending, approved, discrepancy
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('approved_at')->nullable();

            $table->timestamps();

            $table->unique(['branch_id', 'settlement_date']);
        });

        // Accounts Receivable - ลูกหนี้
        Schema::create('accounts_receivable', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('customer_id')->constrained('customers');
            $table->string('source_type'); // sale, repair
            $table->unsignedBigInteger('source_id');
            $table->string('invoice_number');
            $table->decimal('amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2);
            $table->date('invoice_date');
            $table->date('due_date');
            $table->integer('overdue_days')->default(0);
            $table->string('status')->default('pending'); // pending, partial, paid, overdue, written_off
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
            $table->index('due_date');
        });

        // AR Payments
        Schema::create('ar_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accounts_receivable_id')->constrained('accounts_receivable')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->date('payment_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ar_payments');
        Schema::dropIfExists('accounts_receivable');
        Schema::dropIfExists('daily_settlements');
        Schema::dropIfExists('petty_cash');
    }
};

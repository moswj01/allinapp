<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('current_plan_id')->constrained('plans')->onDelete('cascade');
            $table->foreignId('requested_plan_id')->constrained('plans')->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->string('type'); // upgrade, downgrade
            $table->string('status')->default('pending'); // pending, approved, rejected, cancelled
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_proof')->nullable(); // file path for payment slip
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->text('tenant_note')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_change_requests');
    }
};

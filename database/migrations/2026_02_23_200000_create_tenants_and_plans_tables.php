<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Plans / Packages
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');            // e.g. "Basic", "Pro", "Enterprise"
            $table->string('slug')->unique();  // e.g. "basic", "pro", "enterprise"
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);        // monthly price
            $table->decimal('yearly_price', 10, 2)->default(0); // yearly price
            $table->string('billing_cycle')->default('monthly'); // monthly, yearly
            $table->integer('max_users')->default(5);
            $table->integer('max_branches')->default(1);
            $table->integer('max_products')->default(500);
            $table->integer('max_repairs')->default(-1);  // -1 = unlimited
            $table->json('features')->nullable(); // ["repairs","pos","stock","purchasing","finance","reports","line_oa","api"]
            $table->boolean('is_active')->default(true);
            $table->integer('trial_days')->default(14);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Tenants (ร้าน/บริษัท)
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');             // ชื่อร้าน/บริษัท
            $table->string('slug')->unique();   // URL slug
            $table->string('domain')->nullable()->unique(); // custom domain
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_id', 20)->nullable();
            $table->string('logo')->nullable();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['active', 'suspended', 'cancelled', 'trial'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_starts_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->json('settings')->nullable(); // tenant-specific settings
            $table->text('suspension_reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Subscription history / invoices
        Schema::create('tenant_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->string('billing_cycle')->default('monthly');
            $table->date('period_start');
            $table->date('period_end');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_invoices');
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('plans');
    }
};

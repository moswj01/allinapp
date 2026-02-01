<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                // Social Media Linkage
                $table->string('line_id')->nullable();
                $table->string('facebook_id')->nullable();
                $table->string('facebook_name')->nullable();
                // Customer Type & Credit
                $table->string('customer_type')->default('retail'); // retail, wholesale, vip, partner, corporate
                $table->decimal('credit_limit', 12, 2)->default(0);
                $table->integer('credit_days')->default(0); // 7, 15, 30 days
                $table->string('tax_id')->nullable(); // สำหรับลูกค้าบริษัท
                $table->string('company_name')->nullable();
                // Membership
                $table->string('membership_level')->nullable(); // silver, gold, platinum
                $table->integer('points')->default(0);
                // Meta
                $table->text('notes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('phone');
                $table->index('line_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

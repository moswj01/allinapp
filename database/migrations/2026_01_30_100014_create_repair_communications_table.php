<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_id')->constrained('repairs')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type'); // quote, status_update, reminder, custom
            $table->string('channel'); // line, sms, phone, in_person
            $table->text('message');
            $table->text('customer_response')->nullable();
            $table->datetime('responded_at')->nullable();
            $table->boolean('is_confirmed')->default(false); // ลูกค้ายืนยันหรือยัง
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_communications');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('stockable_type'); // Product or Part
            $table->unsignedBigInteger('stockable_id');
            $table->integer('quantity')->default(0);
            $table->integer('min_quantity')->default(0);
            $table->integer('reserved_quantity')->default(0); // จองไว้สำหรับงานซ่อม
            $table->timestamps();

            $table->unique(['branch_id', 'stockable_type', 'stockable_id']);
            $table->index(['stockable_type', 'stockable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_stocks');
    }
};

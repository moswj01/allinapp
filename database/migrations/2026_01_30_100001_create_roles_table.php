<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Display name (Thai)
            $table->string('slug')->unique(); // System identifier: owner, admin, manager, sales, technician, warehouse, accountant
            $table->text('description')->nullable();
            $table->json('permissions')->nullable(); // JSON array of permissions
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};

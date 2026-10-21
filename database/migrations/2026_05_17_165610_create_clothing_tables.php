<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20)->unique(); // S, M, L, XL
            $table->timestamps();
        });

        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50); // Beige, Navy, etc.
            $table->string('hex_code', 7)->nullable(); // #F5F5DC
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('colors');
        Schema::dropIfExists('sizes');
        Schema::dropIfExists('categories');
    }
};
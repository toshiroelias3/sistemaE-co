<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->string('name', 150)->index();
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2);
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('size_id')->constrained()->onDelete('restrict');
            $table->foreignId('color_id')->constrained()->onDelete('restrict');
            $table->string('sku', 50)->unique();
            $table->integer('stock')->default(0);
            $table->timestamps();
            
            $table->index(['product_id', 'size_id', 'color_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
    }
};
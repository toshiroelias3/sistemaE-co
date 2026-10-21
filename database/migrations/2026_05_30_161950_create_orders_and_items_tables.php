<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Tablas de Pedidos Globales
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reference_number')->unique(); // Código único de pedido (ej: TZN-2026-XXXX)
            $table->decimal('total_amount', 10, 2);
            $table->string('shipping_address');
            $table->string('phone');
            $table->string('status')->default('pending'); // pending, processing, shipped, completed, cancelled
            $table->string('payment_status')->default('unpaid'); // unpaid, paid, refunded
            $table->timestamps();
        });

        // Detalle de cada prenda dentro del pedido
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->foreignId('product_variant_id')->constrained();
            $table->integer('quantity');
            $table->decimal('price', 10, 2); // Precio histórico de compra
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
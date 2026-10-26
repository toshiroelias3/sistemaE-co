<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Medio de pago del pedido y datos de Mercado Pago (Checkout Pro).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method', 20)->default('yape')->after('payment_status'); // yape (Yape o Plin), mercadopago
            $table->string('mp_preference_id')->nullable()->after('payment_method');
            $table->text('mp_init_point')->nullable()->after('mp_preference_id');
            $table->string('mp_payment_id')->nullable()->index()->after('mp_init_point');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'mp_preference_id', 'mp_init_point', 'mp_payment_id']);
        });
    }
};

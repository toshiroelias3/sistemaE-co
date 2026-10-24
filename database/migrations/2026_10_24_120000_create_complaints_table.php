<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Libro de Reclamaciones virtual (Código de Protección y Defensa del Consumidor)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->nullable();       // Hoja correlativa: LR-2026-000001
            $table->string('token', 40)->unique();               // Enlace privado a la constancia
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // 1. Consumidor
            $table->string('consumer_name');
            $table->string('document_type', 20);                 // DNI, CE, Pasaporte
            $table->string('document_number', 20);
            $table->string('address');
            $table->string('phone', 30);
            $table->string('email');
            $table->boolean('is_minor')->default(false);
            $table->string('guardian_name')->nullable();         // Padre, madre o apoderado si es menor

            // 2. Bien contratado
            $table->string('item_type', 20);                     // producto, servicio
            $table->string('order_reference')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('item_description');

            // 3. Detalle
            $table->string('type', 20);                          // reclamo, queja
            $table->text('detail');
            $table->text('consumer_request');

            // 4. Respuesta del proveedor
            $table->string('status', 20)->default('pendiente');  // pendiente, respondido
            $table->text('response')->nullable();
            $table->timestamp('responded_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};

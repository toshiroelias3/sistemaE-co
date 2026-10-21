<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            // Si el producto se elimina, sus imágenes registradas también se borran en cascada
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->boolean('is_primary')->default(false)->index(); // Para saber cuál es la foto de portada
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('product_images');
    }
};
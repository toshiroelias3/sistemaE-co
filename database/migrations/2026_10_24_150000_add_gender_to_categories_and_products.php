<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Secciones Varones / Mujeres: cada categoría indica para quién es y cada prenda su género
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('audience', 20)->default('ambos')->after('slug')->index(); // ambos, varones, mujeres
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('gender', 20)->default('unisex')->after('category_id')->index(); // varones, mujeres, unisex
        });
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn('gender'));
        Schema::table('categories', fn (Blueprint $table) => $table->dropColumn('audience'));
    }
};

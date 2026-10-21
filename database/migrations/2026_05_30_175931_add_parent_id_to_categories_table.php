<?php

use Illuminate\Database\Migrations\Migration;

// La columna parent_id y su llave foránea ya se crean en
// 2026_05_17_165610_create_clothing_tables, por lo que esta migración
// se mantiene vacía para no duplicar la restricción.
return new class extends Migration
{
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};

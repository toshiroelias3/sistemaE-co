<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Categorías del catálogo TEZAN (definidas por el dueño el 2026-10-24).
 * Idempotente: se puede volver a ejecutar sin duplicar.
 */
class CatalogCategoriesSeeder extends Seeder
{
    public const CATEGORIES = [
        ['name' => 'Polos clásicos', 'audience' => 'ambos'],
        ['name' => 'Poleras', 'audience' => 'ambos'],
        ['name' => 'Conjuntos polera y pantalón', 'audience' => 'ambos'],
        ['name' => 'Conjuntos casaca y pantalón buzo', 'audience' => 'ambos'],
        ['name' => 'Tops', 'audience' => 'mujeres'],
        ['name' => 'Polos piqué con cuello', 'audience' => 'ambos'],
        ['name' => 'Polos waffle', 'audience' => 'varones'],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $category) {
            Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                ['name' => $category['name'], 'audience' => $category['audience'], 'is_active' => true, 'parent_id' => null]
            );
        }
    }
}

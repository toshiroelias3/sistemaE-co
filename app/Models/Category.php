<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Para quién es la categoría (define en qué sección del menú aparece)
    public const AUDIENCES = [
        'ambos'   => 'Varones y mujeres',
        'varones' => 'Solo varones',
        'mujeres' => 'Solo mujeres',
    ];

    protected $fillable = ['name', 'slug', 'audience', 'is_active', 'parent_id'];

    /**
     * Categorías visibles en una sección (varones o mujeres)
     */
    public function scopeForGender($query, string $gender)
    {
        return $query->whereIn('audience', ['ambos', $gender]);
    }

    /**
     * Géneros de prenda que admite la categoría
     */
    public function allowedGenders(): array
    {
        return match ($this->audience) {
            'varones' => ['varones'],
            'mujeres' => ['mujeres'],
            default   => array_keys(Product::GENDERS),
        };
    }

    // Relación: Una categoría puede pertenecer a una categoría Padre
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Relación: Una categoría puede tener muchas subcategorías Hijas
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Relación con Productos
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
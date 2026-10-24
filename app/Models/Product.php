<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    // Para quién es la prenda: una unisex aparece en Varones y en Mujeres
    public const GENDERS = [
        'varones' => 'Varones',
        'mujeres' => 'Mujeres',
        'unisex'  => 'Unisex',
    ];

    // Secciones de la tienda (en la URL: ?genero=varones)
    public const SECTIONS = [
        'varones' => 'Varones',
        'mujeres' => 'Mujeres',
    ];

    protected $fillable = ['category_id', 'gender', 'name', 'slug', 'description', 'base_price', 'is_featured', 'is_active'];

    /**
     * Prendas de una sección: las de ese género y las unisex
     */
    public function scopeForGender($query, string $gender)
    {
        return $query->whereIn('gender', [$gender, 'unisex']);
    }

    // Un producto pertenece a una categoría
    public function category() {
        return $this->belongsTo(Category::class);
    }

    // Un producto tiene muchas variantes de stock (Talla/Color)
    public function variants() {
        return $this->hasMany(ProductVariant::class);
    }

    // Un producto puede tener muchas imágenes en su galería
    public function images() {
        return $this->hasMany(ProductImage::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = ['category_id', 'name', 'slug', 'description', 'base_price', 'is_featured', 'is_active'];

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
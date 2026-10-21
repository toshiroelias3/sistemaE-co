<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'is_active', 'parent_id']; // Habilitamos parent_id

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
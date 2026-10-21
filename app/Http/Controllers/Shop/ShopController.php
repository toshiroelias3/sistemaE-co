<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Catálogo Principal / Página de Inicio de la Tienda
     */
    public function index(Request $request)
    {
        // 1. Traer categorías principales activas con sus subcategorías
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->get();

        // 2. Consulta base de productos activos ordenando las imágenes por Portada (is_primary)
        $query = Product::where('is_active', true)
            ->with([
                'category',
                'images' => function ($q) {
                    $q->orderBy('is_primary', 'desc');
                }
            ]);

        // 3. Motor de búsqueda interno (Filtro por texto en Nombre o Descripción)
        if ($request->filled('q')) {
            $search = strip_tags($request->q);
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // 4. Filtro por Categoría
        if ($request->filled('category')) {
            $selectedId = $request->category;
            $subCategoryIds = Category::where('parent_id', $selectedId)->pluck('id')->toArray();

            if (!empty($subCategoryIds)) {
                $allCategoryIds = array_merge([$selectedId], $subCategoryIds);
                $query->whereIn('category_id', $allCategoryIds);
            } else {
                $query->where('category_id', $selectedId);
            }
        }

        // 5. Motor de Ordenamiento
        switch ($request->get('sort')) {
            case 'price_asc':
                $query->orderBy('base_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('base_price', 'desc');
                break;
            case 'oldest':
                $query->oldest();
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        // 6. Paginación optimizada manteniendo todos los parámetros de la URL
        $products = $query->paginate(8);

        return view('shop.home', compact('products', 'categories'));
    }

    /**
     * Vista detallada de una prenda específica basada en su Slug (Ficha de Producto)
     */
    public function show($slug)
    {
        // Buscamos el producto por su slug único ordenando la foto principal al inicio
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'category',
                'images' => function ($q) {
                    $q->orderBy('is_primary', 'desc');
                },
                'variants.size',
                'variants.color'
            ])
            ->firstOrFail();

        // Extraemos solo las tallas disponibles en las variantes físicas de esta prenda
        $sizes = $product->variants->pluck('size')->filter()->unique('id')->sortBy('id');

        // Matriz Talla/Color/Stock para filtrar colores y mostrar disponibilidad en el navegador
        $variantMatrix = $product->variants->map(fn ($v) => [
            'size_id'    => $v->size_id,
            'color_id'   => $v->color_id,
            'color_name' => $v->color?->name,
            'stock'      => $v->stock,
        ])->values();

        // Sugerencias de la misma categoría
        $relatedProducts = Product::where('is_active', true)
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->with(['images' => fn ($q) => $q->orderBy('is_primary', 'desc')])
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('shop.product', compact('product', 'sizes', 'variantMatrix', 'relatedProducts'));
    }
}
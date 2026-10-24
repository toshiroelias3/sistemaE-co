<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;

class SeoController extends Controller
{
    /**
     * Mapa del sitio con la portada, las categorías activas y las prendas activas
     */
    public function sitemap()
    {
        $categories = Category::where('is_active', true)->get(['id', 'updated_at']);
        $products = Product::where('is_active', true)->latest('updated_at')->get(['slug', 'updated_at']);

        return response()
            ->view('seo.sitemap', compact('categories', 'products'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * robots.txt: deja fuera las páginas privadas y apunta al mapa del sitio
     */
    public function robots()
    {
        $lines = [
            'User-agent: *',
            'Disallow: /carrito',
            'Disallow: /checkout',
            'Disallow: /mis-pedidos',
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /register',
            '',
            'Sitemap: ' . route('seo.sitemap'),
        ];

        return response(implode("\n", $lines) . "\n")->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}

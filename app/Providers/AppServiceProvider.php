<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // El frontend usa Bootstrap 5, no Tailwind
        Paginator::useBootstrapFive();

        // Categorías principales para el menú de navegación de la tienda
        View::composer('partials.navbar', function ($view) {
            $view->with('navCategories', Category::where('is_active', true)
                ->whereNull('parent_id')
                ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
                ->orderBy('name')
                ->get());
        });
    }
}

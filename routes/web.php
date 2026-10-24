<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Shop\ShopController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\CustomerOrderController;
use App\Http\Controllers\Shop\SeoController;
use App\Http\Controllers\Shop\ComplaintController;
use App\Http\Controllers\Admin\ComplaintController as AdminComplaintController;

/*
|--------------------------------------------------------------------------
| Web Routes - TEZAN E-Commerce
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. TIENDA PÚBLICA, CARRITO Y CHECKOUT
// ==========================================
Route::get('/', [ShopController::class, 'index'])->name('home');
Route::get('/producto/{slug}', [ShopController::class, 'show'])->name('shop.product');

// Libro de Reclamaciones virtual
Route::get('/libro-de-reclamaciones', [ComplaintController::class, 'create'])->name('complaints.create');
Route::post('/libro-de-reclamaciones', [ComplaintController::class, 'store'])->middleware('throttle:5,1')->name('complaints.store');
Route::get('/libro-de-reclamaciones/constancia/{token}', [ComplaintController::class, 'show'])->name('complaints.show');

// Páginas de ayuda
Route::view('/preguntas-frecuentes', 'shop.pages.faq')->name('pages.faq');
Route::view('/envios-y-pagos', 'shop.pages.shipping')->name('pages.shipping');

// SEO: mapa del sitio y reglas para buscadores
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');

// Carrito de Compras
Route::prefix('carrito')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::post('/update', [CartController::class, 'update'])->name('update');
    Route::post('/remove', [CartController::class, 'remove'])->name('remove');
});

// Proceso de Checkout Híbrido (Invitados / Autenticados)
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/', [CheckoutController::class, 'store'])->name('store');
    Route::get('/exito/{reference_number}', [CheckoutController::class, 'success'])->name('success');
});


// ==========================================
// 2. AUTENTICACIÓN Y REGISTRO (GUESTS)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    // Límite de intentos para frenar ataques de fuerza bruta
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
});


// ==========================================
// 3. ZONA PRIVADA DE CLIENTES (AUTH)
// ==========================================
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Historial personal de pedidos del cliente
    Route::get('/mis-pedidos', [CustomerOrderController::class, 'index'])->name('customer.orders.index');
    Route::get('/mis-pedidos/{id}', [CustomerOrderController::class, 'show'])->name('customer.orders.show');
});


// ==========================================
// 4. PANEL DE ADMINISTRACIÓN (ADMIN)
// ==========================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard Principal
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    
    // CRUDs de Catálogo
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('products', ProductController::class)->except(['show']);

    // Utilidades de Productos (Imágenes y Colores AJAX)
    Route::delete('/products/images/{image}', [ProductController::class, 'destroyImage'])->name('products.images.destroy');
    Route::post('/colors/ajax-store', [ProductController::class, 'ajaxStoreColor'])->name('colors.ajax.store');


    // Definir foto principal/portada del producto
    Route::post('/products/{product}/images/{image}/set-primary', [ProductController::class, 'setPrimaryImage'])->name('products.images.setPrimary');

    // Libro de Reclamaciones
    Route::get('/reclamaciones', [AdminComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/reclamaciones/{complaint}', [AdminComplaintController::class, 'show'])->name('complaints.show');
    Route::put('/reclamaciones/{complaint}', [AdminComplaintController::class, 'update'])->name('complaints.update');

    // Clientes registrados
    Route::get('/customers', [AdminController::class, 'customers'])->name('customers.index');

    // Gestión e Historial de Pedidos
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{id}', [AdminOrderController::class, 'update'])->name('orders.update');
});
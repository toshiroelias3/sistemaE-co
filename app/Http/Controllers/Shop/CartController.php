<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Support\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Muestra la vista principal del Carrito de Compras
     */
    public function index()
    {
        $cart = Cart::items();
        $subtotal = Cart::subtotal();
        $shippingCost = Cart::shippingFor($subtotal);

        return view('shop.cart', compact('cart', 'subtotal', 'shippingCost'));
    }

    /**
     * Añade un producto con variante específica a la bolsa vía AJAX o Web
     */
    public function add(Request $request)
    {
        // 1. Validar datos de entrada
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'size_id'    => 'required|exists:sizes,id',
            'color_id'   => 'required|exists:colors,id',
            'quantity'   => 'required|integer|min:1'
        ]);

        // 2. Verificar existencia de la variante combinada y su stock
        $variant = ProductVariant::where([
            'product_id' => $request->product_id,
            'size_id'    => $request->size_id,
            'color_id'   => $request->color_id,
        ])->with(['product', 'size', 'color', 'product.images'])->first();

        if (!$variant || !$variant->product?->is_active || $variant->stock < $request->quantity) {
            return $this->fail($request, 'Lo sentimos, no hay stock disponible para esta combinación.');
        }

        // 3. Obtener la bolsa de la sesión
        $cart = Cart::items();
        $cartKey = $request->product_id . '-' . $request->size_id . '-' . $request->color_id;

        // Capturar foto primaria (o la primera disponible como fallback)
        $primaryImage = $variant->product->images->where('is_primary', true)->first()
                        ?? $variant->product->images->first();

        $imagePath = $primaryImage?->url(400);

        // 4. Actualizar cantidad o registrar nuevo ítem
        if (isset($cart[$cartKey])) {
            $newQuantity = $cart[$cartKey]['quantity'] + $request->quantity;
            if ($newQuantity > $variant->stock) {
                return $this->fail($request, 'No puedes añadir más unidades de las disponibles en stock.');
            }
            $cart[$cartKey]['quantity'] = $newQuantity;
            $cart[$cartKey]['price'] = $variant->product->base_price;
            $cart[$cartKey]['max_stock'] = $variant->stock;
        } else {
            $cart[$cartKey] = [
                'cart_key'   => $cartKey,
                'product_id' => $variant->product_id,
                'variant_id' => $variant->id,
                'name'       => $variant->product->name,
                'slug'       => $variant->product->slug,
                'price'      => $variant->product->base_price,
                'size_id'    => $variant->size_id,
                'size_name'  => $variant->size->name,
                'color_id'   => $variant->color_id,
                'color_name' => $variant->color->name,
                'image'      => $imagePath,
                'quantity'   => (int)$request->quantity,
                'max_stock'  => $variant->stock
            ];
        }

        // 5. Guardar estado en la sesión
        session()->put('cart', $cart);

        // Si la petición NO viene por AJAX
        if (!$request->ajax() && !$request->wantsJson()) {
            if ($request->get('buy_now') == '1') {
                return redirect()->route('checkout.index');
            }
            return redirect()->route('cart.index')->with('success', 'Prenda agregada a tu bolsa con éxito.');
        }

        return $this->cartResponse('Prenda agregada a tu bolsa con éxito.');
    }

    /**
     * Cambia la cantidad de un ítem respetando el stock disponible
     */
    public function update(Request $request)
    {
        $request->validate([
            'cart_key' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::items();
        $key = $request->cart_key;

        if (!isset($cart[$key])) {
            return $this->fail($request, 'Identificador de ítem inválido.');
        }

        $stock = ProductVariant::whereKey($cart[$key]['variant_id'])->value('stock') ?? 0;
        if ($request->quantity > $stock) {
            return $this->fail($request, "Solo quedan {$stock} unidades disponibles de {$cart[$key]['name']}.");
        }

        $cart[$key]['quantity'] = (int) $request->quantity;
        $cart[$key]['max_stock'] = $stock;
        session()->put('cart', $cart);

        if ($request->ajax() || $request->wantsJson()) {
            return $this->cartResponse('Cantidad actualizada.');
        }

        return redirect()->route('cart.index')->with('success', 'Cantidad actualizada.');
    }

    /**
     * Elimina un ítem específico de la bolsa vía AJAX o formulario tradicional
     */
    public function remove(Request $request)
    {
        $key = $request->get('key') ?? $request->get('cart_key');

        if (!$key) {
            return $this->fail($request, 'Identificador de ítem inválido.', 400);
        }

        $cart = Cart::items();

        if (isset($cart[$key])) {
            unset($cart[$key]);
            session()->put('cart', $cart);
        }

        // Si la petición viene por AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return $this->cartResponse('Prenda quitada de tu bolsa.');
        }

        // Redirección tradicional
        return redirect()->back()->with('success', 'Prenda quitada de tu bolsa.');
    }

    /**
     * Respuesta JSON con el contador y el HTML renderizado del mini-bolsa
     */
    private function cartResponse(string $message)
    {
        return response()->json([
            'success'    => $message,
            'cart_count' => Cart::count(),
            'html'       => view('shop.partials.mini-cart-items')->render(),
        ]);
    }

    /**
     * Error en formato JSON (AJAX) o redirección con mensaje flash (Web)
     */
    private function fail(Request $request, string $message, int $status = 422)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['error' => $message], $status);
        }

        return redirect()->back()->with('error', $message);
    }
}

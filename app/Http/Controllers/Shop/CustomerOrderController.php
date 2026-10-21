<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class CustomerOrderController extends Controller
{
    // Listado de pedidos del cliente autenticado
    public function index()
    {
        // Filtramos las órdenes por el ID del usuario logueado
        $orders = Order::where('user_id', Auth::id())->latest()->get();
        return view('shop.orders.index', compact('orders'));
    }

    // Detalle de un pedido específico para el cliente
    public function show($id)
    {
        // Nos aseguramos de que la orden le pertenezca al usuario para evitar brechas de seguridad
        $order = Order::with(['items.product', 'items.variant.size', 'items.variant.color'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('shop.orders.show', compact('order'));
    }
}
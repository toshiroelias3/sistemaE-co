<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminOrderController extends Controller
{
    /**
     * Muestra el listado general de órdenes registradas en la tienda
     */
    public function index(Request $request)
    {
        $orders = Order::with('user')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->q;
                $q->where(fn ($q) => $q->where('reference_number', 'LIKE', "%{$search}%")
                    ->orWhere('customer_name', 'LIKE', "%{$search}%")
                    ->orWhere('customer_email', 'LIKE', "%{$search}%"));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Muestra el desglose detallado de un pedido específico
     */
    public function show($id)
    {
        $order = Order::with([
            'user',
            'items.product',
            'items.variant.size',
            'items.variant.color'
        ])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Actualiza el estado logístico y de pago de un pedido.
     * Al cancelar se devuelve el stock al inventario; al reactivar se vuelve a descontar.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status'         => ['required', Rule::in(array_keys(Order::STATUSES))],
            'payment_status' => ['required', Rule::in(array_keys(Order::PAYMENT_STATUSES))],
        ]);

        $order = Order::with('items')->findOrFail($id);
        $wasCancelled = $order->status === 'cancelled';
        $isCancelled = $request->status === 'cancelled';

        try {
            DB::transaction(function () use ($order, $request, $wasCancelled, $isCancelled) {
                if ($wasCancelled !== $isCancelled) {
                    foreach ($order->items as $item) {
                        $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);
                        if (!$variant) {
                            continue;
                        }

                        if ($isCancelled) {
                            $variant->increment('stock', $item->quantity);
                        } elseif ($variant->stock < $item->quantity) {
                            throw new \RuntimeException("No hay stock suficiente (SKU {$variant->sku}) para reactivar este pedido.");
                        } else {
                            $variant->decrement('stock', $item->quantity);
                        }
                    }
                }

                $order->update($request->only(['status', 'payment_status']));
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $message = $isCancelled && !$wasCancelled
            ? 'Pedido cancelado. Las prendas fueron devueltas al inventario.'
            : 'Estados del pedido actualizados correctamente.';

        return redirect()->route('admin.orders.show', $order->id)->with('success', $message);
    }
}

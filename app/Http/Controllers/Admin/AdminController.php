<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        // Contadores que ya tenías mapeados
        $totalProducts = Product::count();
        $totalClients = User::where('role', 'client')->count(); // Filtro por rol de cliente

        // Nuevos KPIs integrados en caliente
        $totalOrders = Order::count();
        $totalEarnings = Order::where('status', '!=', 'cancelled')->where('payment_status', 'paid')->sum('total_amount');

        // Registro de actividad reciente para la tabla inferior
        $recentOrders = Order::with('user')->latest()->take(5)->get();

        // Variantes con poco inventario para reponer
        $lowStockVariants = ProductVariant::with(['product', 'size', 'color'])
            ->whereHas('product', fn ($q) => $q->where('is_active', true))
            ->where('stock', '<=', 3)
            ->orderBy('stock')
            ->take(8)
            ->get();

        // Pedidos esperando que se valide el Yape, y aviso de seguridad si la clave sigue siendo la de ejemplo
        $pendingPayments = Order::where('payment_method', 'yape')->where('payment_status', 'unpaid')->where('status', '!=', 'cancelled')->count();
        $usesDefaultPassword = Hash::check('admin1234', auth()->user()->password);

        return view('admin.dashboard', compact(
            'pendingPayments',
            'usesDefaultPassword',
            'totalProducts',
            'totalClients',
            'totalOrders',
            'totalEarnings',
            'recentOrders',
            'lowStockVariants'
        ));
    }

    /**
     * Listado de clientes registrados con su actividad de compra
     */
    public function customers(Request $request)
    {
        $customers = User::where('role', 'client')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'LIKE', "%{$request->q}%")
                ->orWhere('email', 'LIKE', "%{$request->q}%")))
            ->withCount('orders')
            ->withSum(['orders as total_spent' => fn ($q) => $q->where('status', '!=', 'cancelled')], 'total_amount')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }
}

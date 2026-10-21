@extends('layouts.app')

@section('title', 'Tu bolsa')

@section('content')
<div class="py-4">
    <h1 class="h2 mb-4">Tu bolsa</h1>

    @if(empty($cart))
        <div class="text-center py-5 border">
            <i class="bi bi-bag text-muted display-4 d-block mb-3"></i>
            <p class="text-muted mb-4">Tu bolsa está vacía</p>
            <a href="{{ route('home') }}" class="btn btn-dark px-4 py-2">
                Ver el catálogo
            </a>
        </div>
    @else
        <div class="row g-5">
            <!-- Tabla de Ítems -->
            <div class="col-12 col-lg-8">
                <div class="table-responsive">
                    <table class="table align-middle border-bottom">
                        <thead class="table-light small">
                            <tr>
                                <th>Prenda</th>
                                <th>Detalle</th>
                                <th>Precio</th>
                                <th>Cant.</th>
                                <th>Subtotal</th>
                                <th><span class="visually-hidden">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cart as $item)
                                <tr>
                                    <td style="width: 80px;">
                                        @if(!empty($item['image']))
                                            <img src="{{ Str::startsWith($item['image'], 'http') ? $item['image'] : asset('storage/' . $item['image']) }}" class="object-fit-cover" style="width: 70px; height: 85px;" alt="{{ $item['name'] }}">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center" style="width: 70px; height: 85px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <h2 class="h6 mb-1" style="font-size: 0.9rem;">
                                            @if(!empty($item['slug']))
                                                <a href="{{ route('shop.product', $item['slug']) }}" class="text-dark text-decoration-none">{{ $item['name'] }}</a>
                                            @else
                                                {{ $item['name'] }}
                                            @endif
                                        </h2>
                                        <span class="badge bg-light text-dark border" style="font-size: 0.65rem;">Talla: {{ $item['size_name'] }}</span>
                                        <span class="badge bg-light text-dark border" style="font-size: 0.65rem;">Color: {{ $item['color_name'] }}</span>
                                    </td>
                                    <td class="fw-medium text-nowrap">S/ {{ number_format($item['price'], 2) }}</td>
                                    <td style="width: 90px;">
                                        <form action="{{ route('cart.update') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="cart_key" value="{{ $item['cart_key'] }}">
                                            <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="{{ $item['max_stock'] ?? '' }}"
                                                   class="form-control form-control-sm text-center border-dark-subtle"
                                                   aria-label="Cantidad de {{ $item['name'] }}"
                                                   onchange="this.form.submit()">
                                        </form>
                                    </td>
                                    <td class="fw-bold text-nowrap">S/ {{ number_format($item['price'] * $item['quantity'], 2) }}</td>
                                    <td class="text-end">
                                        <form action="{{ route('cart.remove') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="cart_key" value="{{ $item['cart_key'] }}">
                                            <button type="submit" class="btn btn-link text-danger p-0" title="Eliminar" aria-label="Eliminar {{ $item['name'] }}">
                                                <i class="bi bi-trash fs-5"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <a href="{{ route('home') }}" class="btn btn-link text-decoration-none text-muted p-0 small">
                    <i class="bi bi-arrow-left-short"></i> Seguir comprando
                </a>
            </div>

            <!-- Resumen de Pedido -->
            <div class="col-12 col-lg-4">
                <div class="card border p-4 bg-light">
                    <h2 class="h5 mb-4 border-bottom pb-2">Resumen de Compra</h2>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-bold">S/ {{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                        <span class="text-muted">Envío</span>
                        @if($shippingCost > 0)
                            <span class="fw-bold">S/ {{ number_format($shippingCost, 2) }}</span>
                        @else
                            <span class="small fw-bold text-success">Gratis</span>
                        @endif
                    </div>

                    @if($shippingCost > 0)
                        <p class="small text-muted mb-3">
                            Agrega S/ {{ number_format(\App\Support\Cart::FREE_SHIPPING_FROM - $subtotal, 2) }} más para obtener envío gratis.
                        </p>
                    @endif

                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold h5 mb-0">Total</span>
                        <span class="fw-bold text-dark h5 mb-0">S/ {{ number_format($subtotal + $shippingCost, 2) }}</span>
                    </div>

                    <a href="{{ route('checkout.index') }}" class="btn btn-dark w-100 fw-bold py-3">
                        Ir a pagar
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

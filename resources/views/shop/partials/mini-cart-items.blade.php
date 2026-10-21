@php
    $cart = \App\Support\Cart::items();
    $total = \App\Support\Cart::subtotal();
    $missingForFree = max(0, \App\Support\Cart::FREE_SHIPPING_FROM - $total);
@endphp

@if(empty($cart))
    <div class="text-center my-auto py-5">
        <p class="tz-display fs-3 mb-2">Tu bolsa está vacía</p>
        <p class="text-muted mb-4">Elige una prenda y aparecerá aquí.</p>
        <a href="{{ route('home') }}#catalogo" class="btn btn-dark px-4" data-bs-dismiss="offcanvas">Ver el catálogo</a>
    </div>
@else
    <!-- Progreso hacia el envío gratis -->
    <div class="mb-4">
        <p class="small mb-2">
            @if($missingForFree > 0)
                Te faltan <strong class="tz-price">S/ {{ number_format($missingForFree, 2) }}</strong> para el envío gratis.
            @else
                <i class="bi bi-truck me-1"></i> Tu pedido tiene envío gratis.
            @endif
        </p>
        <div class="progress free-shipping-progress rounded-0" role="progressbar" aria-label="Avance hacia el envío gratis" aria-valuenow="{{ (int) min(100, $total / \App\Support\Cart::FREE_SHIPPING_FROM * 100) }}" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar" style="width: {{ min(100, $total / \App\Support\Cart::FREE_SHIPPING_FROM * 100) }}%"></div>
        </div>
    </div>

    @foreach($cart as $item)
        <div class="d-flex align-items-center gap-3 border-bottom border-dark pb-3 mb-3">
            <div class="flex-shrink-0 bg-light" style="width: 72px; height: 72px;">
                @if(!empty($item['image']))
                    <img src="{{ Str::startsWith($item['image'], 'http') ? $item['image'] : asset('storage/' . $item['image']) }}" class="w-100 h-100 object-fit-cover" alt="">
                @endif
            </div>
            <div class="flex-grow-1 min-w-0">
                <p class="fw-semibold mb-1">{{ $item['name'] }}</p>
                <p class="small text-muted mb-1">Talla {{ $item['size_name'] }}, {{ $item['color_name'] }}</p>
                <p class="small tz-price mb-0">{{ $item['quantity'] }} × S/ {{ number_format($item['price'], 2) }}</p>
            </div>
            <button type="button" class="btn btn-sm btn-link link-dark p-1 remove-cart-item-btn" data-key="{{ $item['cart_key'] }}" aria-label="Quitar {{ $item['name'] }} de la bolsa">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endforeach

    <div class="mt-auto pt-3">
        <div class="d-flex justify-content-between align-items-baseline mb-3">
            <span class="fw-semibold">Subtotal</span>
            <span class="tz-price fs-5">S/ {{ number_format($total, 2) }}</span>
        </div>
        <a href="{{ route('checkout.index') }}" class="btn btn-dark w-100 py-3 mb-2">Ir a pagar</a>
        <a href="{{ route('cart.index') }}" class="btn btn-outline-dark w-100 py-2">Ver bolsa completa</a>
    </div>
@endif

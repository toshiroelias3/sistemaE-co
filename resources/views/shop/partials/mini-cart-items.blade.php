@php
    $cart = \App\Support\Cart::items();
    $total = \App\Support\Cart::subtotal();
    $missingForFree = max(0, \App\Support\Cart::FREE_SHIPPING_FROM - $total);
@endphp

@if(!empty($cart))
    <!-- Progreso hacia el envío gratis -->
    <div class="mb-4">
        <p class="small mb-2 {{ $missingForFree > 0 ? 'text-muted' : 'text-success fw-semibold' }}">
            @if($missingForFree > 0)
                Te faltan <strong class="text-dark">S/ {{ number_format($missingForFree, 2) }}</strong> para el envío gratis.
            @else
                <i class="bi bi-truck me-1"></i> ¡Tienes envío gratis!
            @endif
        </p>
        <div class="progress free-shipping-progress rounded-0">
            <div class="progress-bar" style="width: {{ min(100, $total / \App\Support\Cart::FREE_SHIPPING_FROM * 100) }}%"></div>
        </div>
    </div>
@endif

@forelse($cart as $item)
    <div class="d-flex align-items-center gap-3 border-bottom pb-3 mb-3 position-relative">
        <div class="flex-shrink-0" style="width: 70px; height: 80px; background-color: #f8f9fa;">
            @if(!empty($item['image']))
                <img src="{{ Str::startsWith($item['image'], 'http') ? $item['image'] : asset('storage/' . $item['image']) }}"
                     class="w-100 h-100 object-fit-cover"
                     alt="{{ $item['name'] }}">
            @else
                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                    <i class="bi bi-image"></i>
                </div>
            @endif
        </div>
        <div class="flex-grow-1">
            <h6 class="small fw-bold text-uppercase tracking-wider text-dark mb-1 m-0" style="font-size: 0.85rem;">{{ $item['name'] }}</h6>
            <span class="d-block text-muted mb-1" style="font-size: 0.75rem;">Talla: {{ $item['size_name'] }} | Color: {{ $item['color_name'] }}</span>
            <span class="small fw-semibold text-dark">{{ $item['quantity'] }} x S/ {{ number_format($item['price'], 2) }}</span>
        </div>
        <!-- Botón con data-key para JS -->
        <button type="button" class="btn btn-sm text-muted p-0 remove-cart-item-btn" data-key="{{ $item['cart_key'] }}" title="Eliminar prenda" aria-label="Eliminar {{ $item['name'] }}">
            <i class="bi bi-x-lg" style="font-size: 0.85rem;"></i>
        </button>
    </div>
@empty
    <div class="text-center py-4 text-muted">
        <i class="bi bi-bag-x fs-2 d-block mb-2"></i>
        <span class="small text-uppercase tracking-wider">Tu carrito está vacío</span>
    </div>
@endforelse

@if(!empty($cart))
    <div class="mt-auto border-top pt-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <span class="text-uppercase tracking-wider small fw-bold text-muted">Subtotal:</span>
            <span class="fs-5 fw-bold text-dark">S/ {{ number_format($total, 2) }}</span>
        </div>
        <a href="{{ route('checkout.index') }}" class="btn btn-dark rounded-0 w-100 text-uppercase tracking-wider py-3 fw-bold small mb-2">
            Proceder al Pago
        </a>
        <a href="{{ route('cart.index') }}" class="btn btn-outline-dark rounded-0 w-100 text-uppercase tracking-wider py-2 small">
            Ver bolsa completa
        </a>
    </div>
@endif

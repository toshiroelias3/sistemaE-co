{{-- Tarjeta de prenda. Las imágenes deben venir ordenadas con la portada primero. --}}
<a href="{{ route('shop.product', $product->slug) }}" class="tz-card">
    <div class="tz-card-img product-image-container">
        @forelse($product->images as $index => $img)
            <img src="{{ asset('storage/' . $img->image_path) }}"
                 class="hover-slide-img {{ $index === 0 ? 'opacity-100' : 'opacity-0' }}"
                 loading="lazy"
                 alt="{{ $index === 0 ? $product->name : '' }}">
        @empty
            <div class="position-absolute top-50 start-50 translate-middle text-muted small text-center w-75">Foto próximamente</div>
        @endforelse
        @if($product->is_featured)
            <span class="tz-card-tag tz-label">Esencial</span>
        @endif
    </div>
    <div class="tz-card-info">
        <span class="tz-card-name">{{ $product->name }}</span>
        <span class="tz-card-price tz-price">S/ {{ number_format($product->base_price, 2) }}</span>
    </div>
</a>

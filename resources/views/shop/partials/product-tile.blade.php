{{-- Tarjeta de prenda. Las imágenes deben venir ordenadas con la portada primero. --}}
<a href="{{ route('shop.product', $product->slug) }}" class="tz-card">
    <div class="tz-card-img product-image-container">
        @forelse($product->images as $index => $img)
            @if($index === 0)
                <img src="{{ $img->url(800) }}"
                     srcset="{{ $img->srcset() }}"
                     sizes="(min-width: 992px) 25vw, 50vw"
                     class="hover-slide-img opacity-100"
                     loading="lazy" decoding="async"
                     alt="{{ $product->name }}">
            @else
                {{-- Las fotos alternativas se descargan recién al pasar el cursor --}}
                <img data-src="{{ $img->url(800) }}"
                     data-srcset="{{ $img->srcset() }}"
                     sizes="(min-width: 992px) 25vw, 50vw"
                     class="hover-slide-img opacity-0"
                     decoding="async"
                     alt="">
            @endif
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

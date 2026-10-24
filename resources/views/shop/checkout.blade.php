@extends('layouts.app')

@section('noindex', '1')

@section('title', 'Finalizar Compra')

@section('content')
<div class="py-4">
    <h1 class="h2 mb-4">Proceso de Pago</h1>

    @if ($errors->any())
        <div class="alert alert-danger small mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Banner Opcional para Invitados -->
    @guest
        <div class="alert alert-light border d-flex justify-content-between align-items-center mb-4 py-3">
            <span class="small text-muted">
                <i class="bi bi-person-circle me-2"></i> ¿Ya tienes una cuenta registrada en TEZAN?
            </span>
            <a href="{{ route('login') }}" class="btn btn-outline-dark btn-sm fw-bold">
                Iniciar Sesión
            </a>
        </div>
    @endguest

    <div class="row g-5">
        <!-- Formulario de Checkout y Pago -->
        <div class="col-12 col-lg-7">
            <form action="{{ route('checkout.store') }}" method="POST" class="card border p-4 bg-white">
                @csrf

                <!-- Sección 1: Contacto -->
                <h5 class="fw-bold mb-3 border-bottom pb-2">1. Información de Contacto</h5>

                @auth
                    <div class="mb-3">
                        <label for="auth_name" class="form-label small text-muted fw-semibold">Nombre completo</label>
                        <input type="text" id="auth_name" class="form-control border-dark-subtle bg-light" value="{{ Auth::user()->name }}" readonly>
                        <input type="hidden" name="customer_name" value="{{ Auth::user()->name }}">
                    </div>
                    <div class="mb-4">
                        <label for="auth_email" class="form-label small text-muted fw-semibold">Correo electrónico</label>
                        <input type="email" id="auth_email" class="form-control border-dark-subtle bg-light" value="{{ Auth::user()->email }}" readonly>
                        <input type="hidden" name="customer_email" value="{{ Auth::user()->email }}">
                    </div>
                @else
                    <div class="mb-3">
                        <label for="customer_name" class="form-label small text-muted fw-semibold">Nombre completo *</label>
                        <input type="text" id="customer_name" name="customer_name" class="form-control border-dark-subtle" placeholder="Ej: Andrea García" value="{{ old('customer_name') }}" required>
                    </div>
                    <div class="mb-4">
                        <label for="customer_email" class="form-label small text-muted fw-semibold">Correo electrónico *</label>
                        <input type="email" id="customer_email" name="customer_email" class="form-control border-dark-subtle" placeholder="Ej: cliente@correo.com" value="{{ old('customer_email') }}" required>
                        <div class="form-text small text-muted">Te enviaremos la confirmación del pedido y número de seguimiento a este correo.</div>
                    </div>
                @endauth

                <!-- Sección 2: Envío -->
                <h5 class="fw-bold mb-3 border-bottom pb-2 pt-2">2. Dirección de Entrega</h5>

                <div class="mb-3">
                    <label for="shipping_address" class="form-label small text-muted fw-semibold">Dirección Exacta *</label>
                    <input type="text" id="shipping_address" name="shipping_address" class="form-control border-dark-subtle" placeholder="Ej: Av. Las Palmeras 123, Dpto 402" value="{{ old('shipping_address') }}" required>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label for="city" class="form-label small text-muted fw-semibold">Ciudad / Distrito *</label>
                        <input type="text" id="city" name="city" class="form-control border-dark-subtle" placeholder="Ej: San Isidro, Lima" value="{{ old('city') }}" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="phone" class="form-label small text-muted fw-semibold">Teléfono Celular *</label>
                        <input type="tel" id="phone" name="phone" class="form-control border-dark-subtle" placeholder="Ej: 987654321" value="{{ old('phone') }}" required>
                    </div>
                </div>

                <!-- Sección 3: Pago con Yape -->
                <h5 class="fw-bold mb-3 border-bottom pb-2 pt-2">3. Pago con Yape</h5>
                <input type="hidden" name="payment_method" value="yape">

                <div class="p-3 mb-4" style="background: var(--tz-warm);">
                    <p class="fw-semibold mb-2"><i class="bi bi-phone me-2"></i>Pagas con Yape después de confirmar tu pedido</p>
                    <ol class="small mb-0 ps-3">
                        <li>Confirma tu pedido con el botón de abajo.</li>
                        <li>Te mostramos el monto exacto y el número de Yape{{ config('tienda.yape') ? ' (' . config('tienda.yape') . ')' : '' }} a nombre de {{ config('tienda.yape_titular') }}.</li>
                        <li>Yapea y envíanos la captura por WhatsApp con tu número de pedido.</li>
                        <li>Validamos el pago y preparamos tu envío.</li>
                    </ol>
                </div>

                <!-- Términos y Política de privacidad -->
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="terms" name="terms" value="1" required>
                    <label class="form-check-label small text-muted" for="terms">
                        Acepto los <a href="{{ route('pages.terms') }}" target="_blank" class="text-dark text-decoration-underline">términos y condiciones</a>
                        y la <a href="{{ route('pages.privacy') }}" target="_blank" class="text-dark text-decoration-underline">política de privacidad</a> *
                    </label>
                </div>

                <!-- Botón Confirmar (se deshabilita al enviar para evitar pedidos duplicados) -->
                <button type="submit" class="btn btn-dark w-100 fw-bold py-3" onclick="setTimeout(() => this.disabled = true, 0)">
                    Confirmar pedido y pagar con Yape
                </button>
            </form>
        </div>

        <!-- Resumen del Pedido -->
        <div class="col-12 col-lg-5">
            <div class="card border p-4 bg-light">
                <h5 class="fw-bold mb-4 border-bottom pb-2">Resumen de la Orden</h5>

                @if(empty($cart))
                    <p class="text-muted small">No hay prendas en tu bolsa.</p>
                @else
                    <div class="mb-4">
                        @foreach($cart as $item)
                            <div class="d-flex align-items-center gap-3 border-bottom pb-3 mb-3">
                                <div style="width: 60px; height: 75px; background-color: #e9ecef;">
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
                                    <h6 class="small mb-1" style="font-size: 0.85rem;">{{ $item['name'] }}</h6>
                                    <span class="d-block text-muted small" style="font-size: 0.75rem;">Talla: {{ $item['size_name'] }} | Color: {{ $item['color_name'] }}</span>
                                    <span class="small fw-semibold text-dark">{{ $item['quantity'] }} x S/ {{ number_format($item['price'], 2) }}</span>
                                </div>
                                <span class="fw-bold small">S/ {{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                            </div>
                        @endforeach
                    </div>

                    <!-- Desglose de Precios -->
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-bold">S/ {{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                        <span class="text-muted">Envío</span>
                        @if($shippingCost > 0)
                            <span class="fw-bold text-dark">S/ {{ number_format($shippingCost, 2) }}</span>
                        @else
                            <span class="fw-bold text-success small">Gratis</span>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between">
                        <span class="fw-bold h5 mb-0">Total</span>
                        <span class="fw-bold text-dark h5 mb-0">S/ {{ number_format($subtotal + $shippingCost, 2) }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

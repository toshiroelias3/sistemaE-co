@extends('layouts.admin')

@section('admin_content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-light text-uppercase tracking-wider m-0">Detalle de Pedido: {{ $order->reference_number }}</h3>
        <span class="text-muted small">Registrado el {{ $order->created_at->format('d/m/Y H:i') }}</span>
    </div>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary rounded-0 text-uppercase small tracking-wider px-3">
        &larr; Volver
    </a>
</div>

<div class="row g-4">
    <!-- Columna Izquierda: Detalle de Productos y Datos de Entrega -->
    <div class="col-12 col-lg-8">
        <!-- Tabla de Productos -->
        <div class="card border-0 shadow-sm rounded-0 bg-white p-4 mb-4">
            <h5 class="fw-bold text-uppercase tracking-wider small border-bottom pb-2 mb-3">Prendas Empacadas</h5>
            <div class="table-responsive">
                <table class="table table-borderless align-middle mb-0 small">
                    <thead class="table-light text-uppercase tracking-wider text-muted" style="font-size: 0.7rem;">
                        <tr>
                            <th>Prenda</th>
                            <th class="text-center">Variante</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-end">Precio Un.</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr class="border-bottom">
                                <td class="fw-bold text-dark py-3">
                                    {{ $item->product->name ?? 'Producto no disponible' }}
                                </td>
                                <td class="text-center text-muted">
                                    Talla: {{ $item->variant->size->name ?? 'N/A' }} <br> 
                                    Color: {{ $item->variant->color->name ?? 'N/A' }}
                                </td>
                                <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                <td class="text-end">S/. {{ number_format($item->price, 2) }}</td>
                                <td class="text-end fw-bold text-dark">S/. {{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Desglose de Totales -->
            <div class="border-top pt-3 mt-3 text-end">
                <div class="text-muted small mb-1">
                    <span>Subtotal Prendas: </span>
                    <strong>S/ {{ number_format($order->subtotal, 2) }}</strong>
                </div>
                <div class="text-muted small mb-2">
                    <span>Costo Envío: </span>
                    <strong>{{ $order->shipping_cost > 0 ? 'S/ ' . number_format($order->shipping_cost, 2) : 'Gratis' }}</strong>
                </div>
                <div class="fw-bold text-dark fs-5">
                    <span class="text-uppercase tracking-wider fs-6 text-muted me-2">Total del Pedido:</span>
                    S/. {{ number_format($order->total_amount, 2) }}
                </div>
            </div>
        </div>

        <!-- Información de Entrega y Cliente -->
        <div class="card border-0 shadow-sm rounded-0 bg-white p-4">
            <h5 class="fw-bold text-uppercase tracking-wider small border-bottom pb-2 mb-3">Datos de Entrega</h5>
            <div class="row g-3 small">
                <div class="col-sm-6">
                    <span class="d-block text-muted text-uppercase fw-semibold" style="font-size: 0.75rem;">Cliente Comprador:</span>
                    <span class="fs-6 fw-medium text-dark">
                        {{ $order->customer_name ?? $order->user->name ?? 'N/A' }}
                    </span>
                    @if(!$order->user_id)
                        <span class="badge bg-secondary ms-1" style="font-size: 0.65rem;">Invitado</span>
                    @endif
                    <span class="d-block text-muted small mt-1">
                        <i class="bi bi-envelope me-1"></i> {{ $order->customer_email ?? $order->user->email ?? 'N/A' }}
                    </span>
                </div>
                <div class="col-sm-6">
                    <span class="d-block text-muted text-uppercase fw-semibold" style="font-size: 0.75rem;">Teléfono de Contacto:</span>
                    <span class="fs-6 fw-medium text-dark"><i class="bi bi-telephone me-1"></i> {{ $order->phone }}</span>
                </div>
                <div class="col-12 mt-2 border-top pt-2">
                    <span class="d-block text-muted text-uppercase fw-semibold" style="font-size: 0.75rem;">Dirección Física de Destino:</span>
                    <span class="fs-6 fw-medium text-dark"><i class="bi bi-geo-alt me-1"></i> {{ $order->shipping_address }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Acciones y Cambio de Estado -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-0 bg-white p-4">
            <h5 class="fw-bold text-uppercase tracking-wider small border-bottom pb-2 mb-4">Acciones de Despacho</h5>
            
            <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="status" class="form-label small fw-medium text-uppercase text-muted">Estado Logístico</label>
                    <select class="form-select rounded-0 border-dark-subtle small" id="status" name="status" required>
                        @foreach(\App\Models\Order::STATUSES as $value => $label)
                            <option value="{{ $value }}" {{ $order->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="form-text small">Al cancelar, las prendas vuelven automáticamente al inventario.</div>
                </div>

                <div class="mb-4">
                    <label for="payment_status" class="form-label small fw-medium text-uppercase text-muted">Estado Transaccional</label>
                    <select class="form-select rounded-0 border-dark-subtle small" id="payment_status" name="payment_status" required>
                        @foreach(\App\Models\Order::PAYMENT_STATUSES as $value => $label)
                            <option value="{{ $value }}" {{ $order->payment_status === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-dark rounded-0 w-100 text-uppercase tracking-wider py-2 fw-bold small">
                    Actualizar Estados
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
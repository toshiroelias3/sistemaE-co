@extends('layouts.admin')

@section('admin_content')
<div class="mb-4">
    <h2 class="fw-light text-uppercase tracking-wider m-0">Libro de Reclamaciones</h2>
    <p class="text-muted small mb-0 mt-1">Plazo legal de respuesta: {{ \App\Models\Complaint::RESPONSE_DAYS }} días hábiles desde la fecha de la hoja.</p>
</div>

@unless(config('tienda.ruc') && config('tienda.direccion'))
    <div class="alert alert-warning rounded-0 small">
        <i class="bi bi-exclamation-triangle me-2"></i>Faltan el RUC o la dirección de la empresa en la hoja de reclamación. Complétalos en <code>.env</code> (<code>TIENDA_RUC</code>, <code>TIENDA_DIRECCION</code>).
    </div>
@endunless

<div class="card border-0 shadow-sm rounded-0 bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center small">
                <thead class="table-light text-uppercase tracking-wider text-muted" style="font-size: 0.75rem;">
                    <tr>
                        <th class="py-3">Hoja</th>
                        <th class="py-3">Fecha</th>
                        <th class="py-3 text-start">Consumidor</th>
                        <th class="py-3">Tipo</th>
                        <th class="py-3">Estado</th>
                        <th class="py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($complaints as $complaint)
                        @php $overdue = $complaint->status === 'pendiente' && $complaint->created_at->diffInWeekdays(now()) > \App\Models\Complaint::RESPONSE_DAYS; @endphp
                        <tr>
                            <td class="fw-bold">{{ $complaint->code }}</td>
                            <td class="text-muted">{{ $complaint->created_at->timezone('America/Lima')->format('d/m/Y H:i') }}</td>
                            <td class="text-start">
                                <span class="d-block fw-bold">{{ $complaint->consumer_name }}</span>
                                <span class="text-muted" style="font-size: 0.75rem;">{{ $complaint->email }}</span>
                            </td>
                            <td>{{ $complaint->type_label }}</td>
                            <td>
                                @if($complaint->status === 'respondido')
                                    <span class="badge bg-success-subtle text-success-emphasis rounded-0 px-2 text-uppercase">Respondido</span>
                                @elseif($overdue)
                                    <span class="badge bg-danger rounded-0 px-2 text-uppercase">Vencido</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-0 px-2 text-uppercase">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.complaints.show', $complaint) }}" class="btn btn-sm btn-dark rounded-0 text-uppercase tracking-wider px-3" style="font-size: 0.7rem;">Ver y responder</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted py-4">No hay hojas de reclamación registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">{{ $complaints->links() }}</div>
@endsection

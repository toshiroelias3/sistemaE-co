<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * Estados logísticos y de pago con su etiqueta visible.
     */
    public const STATUSES = [
        'pending' => 'Pendiente',
        'processing' => 'Procesando',
        'shipped' => 'En ruta',
        'completed' => 'Entregado',
        'cancelled' => 'Cancelado',
    ];

    public const PAYMENT_STATUSES = [
        'unpaid' => 'Por pagar',
        'paid' => 'Pagado',
        'failed' => 'Fallido',
        'refunded' => 'Reembolsado',
    ];

    /**
     * Medios de pago aceptados. Yape y Plin usan el mismo número y se validan a mano;
     * Mercado Pago se confirma solo con su webhook.
     */
    public const PAYMENT_METHODS = [
        'mercadopago' => 'Mercado Pago',
        'yape' => 'Yape o Plin',
    ];

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_email',
        'reference_number',
        'total_amount',
        'shipping_address',
        'phone',
        'status',
        'payment_status',
        'payment_method',
        'mp_preference_id',
        'mp_init_point',
        'mp_payment_id',
    ];

    /**
     * Relación: Un pedido pertenece a un usuario (Opcional para compras de Invitados).
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => $this->customer_name ?? 'Cliente Invitado',
            'email' => $this->customer_email ?? 'N/A',
        ]);
    }

    /**
     * Relación: Un pedido tiene múltiples ítems/prendas (OrderItems).
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Momento en que vence el tiempo para pagar el pedido
     */
    public function paymentDeadline(): CarbonInterface
    {
        return $this->created_at->copy()->addHours(config('tienda.horas_para_pagar'));
    }

    /**
     * Devuelve las prendas al inventario. Debe llamarse dentro de una transacción.
     */
    public function releaseStock(): void
    {
        foreach ($this->items as $item) {
            ProductVariant::lockForUpdate()->find($item->product_variant_id)?->increment('stock', $item->quantity);
        }
    }

    /**
     * Vuelve a reservar las prendas de un pedido cancelado. Si falta stock de alguna,
     * no reserva ninguna y devuelve false. Debe llamarse dentro de una transacción.
     */
    public function reserveStock(): bool
    {
        $variants = ProductVariant::whereIn('id', $this->items->pluck('product_variant_id'))
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($this->items as $item) {
            if (($variants[$item->product_variant_id]->stock ?? 0) < $item->quantity) {
                return false;
            }
        }

        foreach ($this->items as $item) {
            $variants[$item->product_variant_id]->decrement('stock', $item->quantity);
        }

        return true;
    }

    /**
     * Suma de las prendas (sin envío), calculada desde los ítems históricos.
     */
    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(fn ($item) => $item->price * $item->quantity);
    }

    public function getShippingCostAttribute(): float
    {
        return max(0, $this->total_amount - $this->subtotal);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? ucfirst($this->payment_status);
    }
}

<?php

namespace App\Models;

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

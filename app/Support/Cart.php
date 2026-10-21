<?php

namespace App\Support;

/**
 * Acceso centralizado al carrito guardado en sesión y a las reglas de envío.
 */
class Cart
{
    public const SHIPPING_COST = 12.00;
    public const FREE_SHIPPING_FROM = 200.00;

    public static function items(): array
    {
        return session('cart', []);
    }

    public static function count(): int
    {
        return array_sum(array_column(self::items(), 'quantity'));
    }

    public static function subtotal(): float
    {
        return array_reduce(self::items(), fn ($sum, $item) => $sum + $item['price'] * $item['quantity'], 0.0);
    }

    public static function shippingFor(float $subtotal): float
    {
        return $subtotal >= self::FREE_SHIPPING_FROM ? 0.0 : self::SHIPPING_COST;
    }
}

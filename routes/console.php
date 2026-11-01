<?php

use App\Mail\OrderExpired;
use App\Models\Order;
use App\Models\ProductImage;
use App\Support\MercadoPago;
use App\Support\ProductImages;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('images:optimize {--force : Regenera también las fotos que ya tienen versiones}', function () {
    $images = ProductImage::all();
    $done = 0;

    foreach ($images as $image) {
        if (! $this->option('force') && ProductImages::availableWidths($image->image_path)) {
            continue;
        }

        $widths = ProductImages::generate($image->image_path);
        $this->line(($widths ? '✓ ' : '✗ ').$image->image_path.($widths ? ' → '.implode(', ', $widths).' px' : ' (no se pudo leer)'));
        $done += $widths ? 1 : 0;
    }

    $this->info("Listo: {$done} fotos optimizadas de {$images->count()}.");
})->purpose('Genera versiones WebP livianas de las fotos de producto');

Artisan::command('orders:cancel-unpaid', function () {
    $expired = Order::where('status', 'pending')
        ->whereIn('payment_status', ['unpaid', 'failed'])
        ->where('created_at', '<=', now()->subHours(config('tienda.horas_para_pagar')))
        ->get();
    $cancelled = 0;

    foreach ($expired as $order) {
        // Si Mercado Pago ya aprobó el pago y el aviso no llegó, se registra en vez de cancelar
        if ($order->mp_payment_id && MercadoPago::isEnabled()) {
            MercadoPago::syncPayment($order->mp_payment_id);
        }

        $wasCancelled = DB::transaction(function () use ($order) {
            $locked = Order::with('items')->lockForUpdate()->find($order->id);
            if ($locked->status !== 'pending' || $locked->payment_status === 'paid') {
                return false;
            }

            $locked->releaseStock();
            $locked->update(['status' => 'cancelled']);

            return true;
        });

        if ($wasCancelled) {
            $cancelled++;

            try {
                Mail::to($order->customer_email)->send(new OrderExpired($order));
            } catch (Throwable $e) {
                Log::error("No se pudo avisar la cancelación del pedido {$order->reference_number}: {$e->getMessage()}");
            }
        }
    }

    $this->info("Pedidos cancelados por falta de pago: {$cancelled}.");
})->purpose('Cancela los pedidos sin pagar dentro del plazo y libera su stock');

Schedule::command('orders:cancel-unpaid')->everyFifteenMinutes()->withoutOverlapping();

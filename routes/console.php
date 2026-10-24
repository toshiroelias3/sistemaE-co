<?php

use App\Models\ProductImage;
use App\Support\ProductImages;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('images:optimize {--force : Regenera también las fotos que ya tienen versiones}', function () {
    $images = ProductImage::all();
    $done = 0;

    foreach ($images as $image) {
        if (!$this->option('force') && ProductImages::availableWidths($image->image_path)) {
            continue;
        }

        $widths = ProductImages::generate($image->image_path);
        $this->line(($widths ? '✓ ' : '✗ ') . $image->image_path . ($widths ? ' → ' . implode(', ', $widths) . ' px' : ' (no se pudo leer)'));
        $done += $widths ? 1 : 0;
    }

    $this->info("Listo: {$done} fotos optimizadas de {$images->count()}.");
})->purpose('Genera versiones WebP livianas de las fotos de producto');

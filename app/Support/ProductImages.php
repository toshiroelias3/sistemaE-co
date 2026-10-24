<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Versiones WebP livianas de las fotos de producto (400, 800 y 1200 px de ancho).
 * El original subido nunca se toca: las versiones son desechables y se pueden regenerar.
 */
class ProductImages
{
    public const WIDTHS = [400, 800, 1200];
    private const QUALITY = 80;

    /**
     * Ruta de la versión de un ancho dado: products/abc.jpg -> products/abc-800.webp
     */
    public static function derivativePath(string $path, int $width): string
    {
        return preg_replace('/\.[^.\/]+$/', '', $path) . "-{$width}.webp";
    }

    /**
     * Genera las versiones que falten. Devuelve los anchos generados.
     * Si GD no está disponible o la imagen no se puede leer, no rompe la subida.
     */
    public static function generate(string $path): array
    {
        $disk = Storage::disk('public');

        if (!function_exists('imagewebp') || !$disk->exists($path)) {
            return [];
        }

        try {
            $source = @imagecreatefromstring($disk->get($path));
            if (!$source) {
                return [];
            }

            $originalWidth = imagesx($source);
            $originalHeight = imagesy($source);
            $generated = [];

            foreach (self::WIDTHS as $width) {
                // No se agranda: si el original es más angosto, esta versión usa su ancho real y es la última
                $targetWidth = min($width, $originalWidth);
                $targetHeight = (int) round($originalHeight * $targetWidth / $originalWidth);
                $resized = imagecreatetruecolor($targetWidth, $targetHeight);
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                imagecopyresampled($resized, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $originalWidth, $originalHeight);

                ob_start();
                imagewebp($resized, null, self::QUALITY);
                $disk->put(self::derivativePath($path, $width), ob_get_clean());
                imagedestroy($resized);

                $generated[] = $width;

                if ($targetWidth < $width) {
                    break;
                }
            }

            imagedestroy($source);

            return $generated;
        } catch (\Throwable $e) {
            Log::warning("No se pudieron generar las versiones de {$path}: {$e->getMessage()}");

            return [];
        }
    }

    /**
     * Borra las versiones generadas de una foto (el original lo borra quien lo subió)
     */
    public static function deleteDerivatives(string $path): void
    {
        Storage::disk('public')->delete(array_map(fn ($w) => self::derivativePath($path, $w), self::WIDTHS));
    }

    /**
     * Anchos disponibles en disco para una foto
     */
    public static function availableWidths(string $path): array
    {
        $disk = Storage::disk('public');

        return array_values(array_filter(self::WIDTHS, fn ($w) => $disk->exists(self::derivativePath($path, $w))));
    }
}

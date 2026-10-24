<?php

namespace App\Models;

use App\Support\ProductImages;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'image_path', 'is_primary'];

    /** Anchos WebP disponibles, calculados una vez por instancia */
    private ?array $widths = null;

    public function product() {
        return $this->belongsTo(Product::class);
    }

    /**
     * URL de la foto: la versión WebP más cercana al ancho pedido, o el original si no hay versiones
     */
    public function url(?int $width = null): string
    {
        $widths = $this->widths();

        if ($width && $widths) {
            $best = collect($widths)->first(fn ($w) => $w >= $width) ?? end($widths);

            return asset('storage/' . ProductImages::derivativePath($this->image_path, $best));
        }

        return asset('storage/' . $this->image_path);
    }

    /**
     * Valor del atributo srcset (vacío si la foto aún no tiene versiones)
     */
    public function srcset(): string
    {
        return collect($this->widths())
            ->map(fn ($w) => asset('storage/' . ProductImages::derivativePath($this->image_path, $w)) . " {$w}w")
            ->implode(', ');
    }

    private function widths(): array
    {
        return $this->widths ??= ProductImages::availableWidths($this->image_path);
    }
}

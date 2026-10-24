<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProductRequest extends FormRequest
{
    /**
     * Determinar si el usuario tiene autorización para realizar la petición.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Preparar los datos antes de la validación.
     * El slug se genera en el servidor y los switches desmarcados se envían como false.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug'        => Str::slug((string) $this->name),
            'is_active'   => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
        ]);
    }

    /**
     * Reglas de validación para crear y editar prendas.
     */
    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'name'        => 'required|string|max:150',
            'slug'        => ['required', 'string', 'max:180', Rule::unique('products', 'slug')->ignore($productId)],
            'category_id' => 'required|exists:categories,id',
            'gender'      => ['required', Rule::in(array_keys(Product::GENDERS))],
            'base_price'  => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_featured' => 'boolean',
            'is_active'   => 'boolean',

            // Regla optimizada para carga múltiple de imágenes
            'images'      => 'nullable|array',
            'images.*'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',

            // Regla para variantes (Talla, Color, Stock)
            'variants'             => 'required|array|min:1',
            'variants.*.size_id'   => 'required|exists:sizes,id',
            'variants.*.color_id'  => 'required|exists:colors,id',
            'variants.*.stock'     => 'required|integer|min:0',
        ];
    }

    /**
     * Evitar combinaciones Talla + Color repetidas (generarían SKUs duplicados).
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $combos = collect($this->input('variants', []))
                    ->map(fn ($v) => ($v['size_id'] ?? '') . '-' . ($v['color_id'] ?? ''));

                if ($combos->count() !== $combos->unique()->count()) {
                    $validator->errors()->add('variants', 'Hay variantes repetidas: cada combinación de talla y color debe ser única.');
                }

                // El género de la prenda debe calzar con la sección de su categoría (ej. Tops solo mujeres)
                $category = Category::find($this->input('category_id'));
                if ($category && $this->input('gender') && !in_array($this->input('gender'), $category->allowedGenders(), true)) {
                    $validator->errors()->add('gender', "La categoría «{$category->name}» es " . strtolower(Category::AUDIENCES[$category->audience]) . '. Elige ese género para la prenda.');
                }
            },
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'name.required'        => 'El nombre de la prenda es obligatorio.',
            'slug.unique'          => 'Ya existe otra prenda con un nombre equivalente. Usa un nombre distinto.',
            'category_id.required' => 'Debes seleccionar una categoría.',
            'base_price.required'  => 'El precio base es obligatorio.',
            'variants.required'    => 'Debes agregar al menos una variante de talla y color.',
            'images.*.image'       => 'El archivo adjunto debe ser una imagen válida (JPG, PNG, WEBP).',
            'images.*.mimes'       => 'Las imágenes deben estar en formato JPEG, PNG, JPG o WEBP.',
            'images.*.max'         => 'Las imágenes no deben pesar más de 10 MB.',
        ];
    }
}

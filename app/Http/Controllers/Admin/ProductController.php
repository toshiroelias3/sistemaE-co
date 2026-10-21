<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Size;
use App\Models\Color;
use App\Models\OrderItem;
use App\Models\ProductImage;
use App\Http\Requests\Admin\ProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Listado global de productos en el panel de administración
     */
    public function index()
    {
        $products = Product::with(['category.parent', 'images'])
            ->withSum('variants as total_stock', 'stock')
            ->latest()
            ->paginate(10);

        return view('admin.products.index', compact('products'));
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        return view('admin.products.create', $this->formData());
    }

    /**
     * Guardar producto, variantes e imágenes de forma atómica
     */
    public function store(ProductRequest $request)
    {
        DB::beginTransaction();

        try {
            // 1. Guardar los datos del producto base
            $product = Product::create($request->safe()->except(['images', 'variants']));

            // 2. Guardar la matriz de variantes (Talla, Color, Stock)
            $this->syncVariants($product, $request->validated('variants'));

            // 3. Procesamiento seguro de imágenes de la galería
            $this->storeImages($product, $request);

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Producto, inventario y galería registrados con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar producto: ' . $e->getMessage());
            return back()->with('error', 'Error crítico al registrar el producto. Revisa los datos e inténtalo nuevamente.')->withInput();
        }
    }

    /**
     * Formulario de edición con datos precargados
     */
    public function edit(Product $product)
    {
        $product->load('variants', 'images');

        return view('admin.products.edit', array_merge($this->formData(), compact('product')));
    }

    /**
     * Procesar la actualización en la Base de Datos
     */
    public function update(ProductRequest $request, Product $product)
    {
        DB::beginTransaction();

        try {
            // 1. Actualizar datos base de la prenda
            $product->update($request->safe()->except(['images', 'variants']));

            // 2. Sincronizar variantes de inventario
            $this->syncVariants($product, $request->validated('variants'));

            // 3. Procesar nuevas imágenes (ACUMULATIVO)
            $this->storeImages($product, $request);

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Prenda, inventario y fotos actualizados con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar producto: ' . $e->getMessage());
            return back()->with('error', 'Error al actualizar el producto. Revisa los datos e inténtalo nuevamente.')->withInput();
        }
    }

    /**
     * Establecer una imagen como la principal (portada) del producto
     */
    public function setPrimaryImage($productId, $imageId)
    {
        $image = ProductImage::where('product_id', $productId)->where('id', $imageId)->firstOrFail();

        DB::transaction(function () use ($image) {
            // Desmarcar todas las fotos del producto y marcar la seleccionada
            ProductImage::where('product_id', $image->product_id)->update(['is_primary' => false]);
            $image->update(['is_primary' => true]);
        });

        return back()->with('success', 'Imagen de portada actualizada correctamente.');
    }

    /**
     * Eliminar prenda permanentemente
     */
    public function destroy(Product $product)
    {
        $variantIds = $product->variants()->pluck('id');

        if (OrderItem::whereIn('product_variant_id', $variantIds)->exists()) {
            return back()->with('error', 'No se puede eliminar la prenda porque algunas de sus variantes ya cuentan con órdenes de compra asociadas. Te sugerimos cambiar su estado a "Inactivo".');
        }

        DB::beginTransaction();
        try {
            $paths = $product->images->pluck('image_path')->all();

            $product->variants()->delete();
            $product->images()->delete();
            $product->delete();

            DB::commit();

            // Los archivos se borran solo cuando la base de datos confirmó la eliminación
            Storage::disk('public')->delete($paths);

            return redirect()->route('admin.products.index')->with('success', 'Prenda y sus archivos multimedia eliminados correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar producto: ' . $e->getMessage());
            return back()->with('error', 'Error al intentar eliminar la prenda.');
        }
    }

    /**
     * Eliminar una imagen individual de la galería del producto
     */
    public function destroyImage(ProductImage $image)
    {
        $productId = $image->product_id;

        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        // Si era la portada, asignar la primera foto disponible como nueva portada
        if ($image->is_primary) {
            ProductImage::where('product_id', $productId)->first()?->update(['is_primary' => true]);
        }

        return back()->with('success', 'Imagen eliminada de la galería correctamente.');
    }

    /**
     * Guardar color al vuelo desde el formulario vía AJAX
     */
    public function ajaxStoreColor(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:50|unique:colors,name',
            'hex_code' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        try {
            $color = Color::create([
                'name'     => trim($request->name),
                'hex_code' => $request->hex_code,
            ]);

            return response()->json([
                'success' => true,
                'id'      => $color->id,
                'name'    => $color->name
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ajaxStoreColor: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'No se pudo guardar el color en la base de datos.'
            ], 500);
        }
    }

    /**
     * Catálogos compartidos por los formularios de creación y edición
     */
    private function formData(): array
    {
        return [
            'categories' => Category::with('parent')->where('is_active', true)->orderBy('name')->get(),
            'sizes'      => Size::all(),
            'colors'     => Color::orderBy('name')->get(),
        ];
    }

    /**
     * Sincroniza la matriz de variantes sin romper el historial de pedidos:
     * las variantes existentes se actualizan, las nuevas se crean y las retiradas
     * se eliminan, salvo que tengan ventas (en ese caso se dejan con stock 0).
     */
    private function syncVariants(Product $product, array $variants): void
    {
        $keptIds = [];

        foreach ($variants as $variantData) {
            $variant = $product->variants()->updateOrCreate(
                ['size_id' => $variantData['size_id'], 'color_id' => $variantData['color_id']],
                [
                    'sku'   => strtoupper('TZN-' . $product->id . '-' . $variantData['size_id'] . '-' . $variantData['color_id']),
                    'stock' => $variantData['stock'],
                ]
            );
            $keptIds[] = $variant->id;
        }

        $removedIds = $product->variants()->whereNotIn('id', $keptIds)->pluck('id');
        $soldIds = OrderItem::whereIn('product_variant_id', $removedIds)->distinct()->pluck('product_variant_id');

        $product->variants()->whereIn('id', $removedIds->diff($soldIds))->delete();
        $product->variants()->whereIn('id', $soldIds)->update(['stock' => 0]);
    }

    /**
     * Guarda las imágenes subidas; la primera se vuelve portada si el producto no tiene una
     */
    private function storeImages(Product $product, Request $request): void
    {
        if (!$request->hasFile('images')) {
            return;
        }

        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        foreach ($request->file('images') as $index => $imageFile) {
            $product->images()->create([
                'image_path' => $imageFile->store('products', 'public'),
                'is_primary' => !$hasPrimary && $index === 0,
            ]);
        }
    }
}

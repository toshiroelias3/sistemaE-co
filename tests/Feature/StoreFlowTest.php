<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Color;
use App\Models\Order;
use App\Models\Product;
use App\Models\Size;
use App\Models\User;
use App\Support\ProductImages;
use Database\Seeders\InitialStoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoreFlowTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(InitialStoreSeeder::class);

        $this->product = Product::create([
            'category_id' => Category::first()->id,
            'name'        => 'Polo Básico',
            'slug'        => 'polo-basico',
            'base_price'  => 120,
            'is_active'   => true,
        ]);
        $this->product->variants()->create([
            'size_id'  => Size::first()->id,
            'color_id' => Color::first()->id,
            'sku'      => 'TZN-TEST-1',
            'stock'    => 5,
        ]);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@tezan.com')->first();
    }

    private function addToCart(int $quantity): void
    {
        $this->postJson(route('cart.add'), [
            'product_id' => $this->product->id,
            'size_id'    => Size::first()->id,
            'color_id'   => Color::first()->id,
            'quantity'   => $quantity,
        ])->assertOk()->assertJson(['cart_count' => $quantity]);
    }

    private function checkout(): Order
    {
        $this->post(route('checkout.store'), [
            'customer_name'    => 'Ana Test',
            'customer_email'   => 'ana@example.com',
            'shipping_address' => 'Av. Siempre Viva 123',
            'city'             => 'Lima',
            'phone'            => '987654321',
            'payment_method'   => 'card',
            'terms'            => '1',
        ])->assertRedirect();

        return Order::latest('id')->firstOrFail();
    }

    public function test_seeder_creates_a_real_admin(): void
    {
        $this->assertSame('admin', $this->admin()->role);
        $this->assertSame('#F5F5DC', Color::where('name', 'Beige')->value('hex_code'));
    }

    public function test_public_pages_render(): void
    {
        $this->get(route('home'))->assertOk()->assertSee('Polo Básico');
        $this->get(route('shop.product', 'polo-basico'))->assertOk()->assertSee('Añadir a la bolsa');
        $this->get(route('cart.index'))->assertOk();
    }

    public function test_checkout_uses_free_shipping_and_current_price(): void
    {
        $this->addToCart(2);

        // Si el precio cambia después de agregar al carrito, se cobra el precio vigente
        $this->product->update(['base_price' => 110]);

        $order = $this->checkout();

        $this->assertEquals(220, $order->total_amount); // 2 x 110, envío gratis (>= 200)
        $this->assertEquals(3, $this->product->variants()->first()->stock);
    }

    public function test_small_orders_pay_shipping(): void
    {
        $this->addToCart(1);
        $order = $this->checkout();

        $this->assertEquals(132, $order->total_amount); // 120 + 12 de envío
    }

    public function test_cart_quantity_cannot_exceed_stock(): void
    {
        $this->addToCart(1);
        $key = array_key_first(session('cart'));

        $this->postJson(route('cart.update'), ['cart_key' => $key, 'quantity' => 6])->assertStatus(422);
        $this->postJson(route('cart.update'), ['cart_key' => $key, 'quantity' => 4])->assertOk()->assertJson(['cart_count' => 4]);
    }

    public function test_cancelling_an_order_restocks_inventory(): void
    {
        $this->addToCart(2);
        $order = $this->checkout();

        $this->actingAs($this->admin())
            ->put(route('admin.orders.update', $order->id), ['status' => 'cancelled', 'payment_status' => 'refunded'])
            ->assertRedirect();

        $this->assertEquals(5, $this->product->variants()->first()->stock);
    }

    public function test_admin_can_edit_a_product_with_sales_and_toggle_switches(): void
    {
        $this->addToCart(1);
        $this->checkout();

        $this->actingAs($this->admin())->put(route('admin.products.update', $this->product), [
            'name'        => 'Polo Básico',
            'category_id' => $this->product->category_id,
            'base_price'  => 99,
            // is_active omitido = switch desmarcado
            'variants'    => [
                ['size_id' => Size::skip(1)->first()->id, 'color_id' => Color::first()->id, 'stock' => 7],
            ],
        ])->assertRedirect(route('admin.products.index'));

        $this->product->refresh();
        $this->assertFalse((bool) $this->product->is_active);
        // La variante vendida se conserva (con stock 0) para no romper el historial
        $this->assertEquals(2, $this->product->variants()->count());
        $this->assertEquals(7, $this->product->variants()->sum('stock'));
    }

    public function test_duplicate_variants_are_rejected(): void
    {
        $variant = ['size_id' => Size::first()->id, 'color_id' => Color::first()->id, 'stock' => 1];

        $this->actingAs($this->admin())->post(route('admin.products.store'), [
            'name'        => 'Casaca',
            'category_id' => $this->product->category_id,
            'base_price'  => 50,
            'variants'    => [$variant, $variant],
        ])->assertSessionHasErrors('variants');
    }

    public function test_admin_pages_render(): void
    {
        $this->actingAs($this->admin());

        foreach (['admin.dashboard', 'admin.products.index', 'admin.products.create', 'admin.categories.index', 'admin.orders.index', 'admin.customers.index'] as $route) {
            $this->get(route($route))->assertOk();
        }
        $this->get(route('admin.products.edit', $this->product))->assertOk();
    }

    public function test_product_page_has_share_preview_and_structured_data(): void
    {
        $this->get(route('shop.product', 'polo-basico'))
            ->assertOk()
            ->assertSee('<meta property="og:title" content="Polo Básico | TEZAN">', false)
            ->assertSee('<meta property="og:type" content="product">', false)
            ->assertSee('<link rel="canonical" href="' . route('shop.product', 'polo-basico') . '">', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('"priceCurrency":"PEN"', false)
            ->assertSee('"price":"120.00"', false)
            ->assertSee('https://schema.org/InStock', false);
    }

    public function test_private_pages_and_searches_are_not_indexed(): void
    {
        $this->get(route('cart.index'))->assertSee('<meta name="robots" content="noindex, follow">', false);
        $this->get(route('login'))->assertSee('<meta name="robots" content="noindex, follow">', false);
        $this->get(route('home', ['q' => 'polo']))->assertSee('<meta name="robots" content="noindex, follow">', false);
        $this->get(route('home'))->assertDontSee('noindex', false);
    }

    public function test_sitemap_lists_active_products_and_robots_points_to_it(): void
    {
        Product::create([
            'category_id' => $this->product->category_id,
            'name' => 'Oculto',
            'slug' => 'oculto',
            'base_price' => 10,
            'is_active' => false,
        ]);

        $this->get(route('seo.sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('shop.product', 'polo-basico'), false)
            ->assertDontSee(route('shop.product', 'oculto'), false);

        $this->get(route('seo.robots'))
            ->assertOk()
            ->assertSee('Disallow: /checkout')
            ->assertSee('Sitemap: ' . route('seo.sitemap'));
    }

    public function test_uploaded_photos_get_light_webp_versions_that_are_cleaned_up(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->put(route('admin.products.update', $this->product), [
            'name'        => 'Polo Básico',
            'category_id' => $this->product->category_id,
            'base_price'  => 120,
            'is_active'   => 1,
            'variants'    => [['size_id' => Size::first()->id, 'color_id' => Color::first()->id, 'stock' => 5]],
            'images'      => [UploadedFile::fake()->image('polo.jpg', 1600, 2000)],
        ])->assertRedirect(route('admin.products.index'));

        $image = $this->product->images()->firstOrFail();
        foreach ([400, 800, 1200] as $width) {
            Storage::disk('public')->assertExists(ProductImages::derivativePath($image->image_path, $width));
        }
        $this->assertStringContainsString('-800.webp 800w', $image->srcset());

        $this->actingAs($this->admin())->delete(route('admin.products.images.destroy', $image));
        Storage::disk('public')->assertMissing($image->image_path);
        Storage::disk('public')->assertMissing(ProductImages::derivativePath($image->image_path, 400));
    }

    public function test_clients_cannot_open_the_admin_panel(): void
    {
        $client = User::factory()->create();

        $this->actingAs($client)->get(route('admin.dashboard'))->assertRedirect('/');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Color;
use App\Models\Order;
use App\Models\Product;
use App\Models\Size;
use App\Models\User;
use App\Mail\ComplaintRegistered;
use App\Models\Complaint;
use App\Support\ProductImages;
use Illuminate\Support\Facades\Mail;
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
        $this->addToCart(3);

        // Si el precio cambia después de agregar al carrito, se cobra el precio vigente
        $this->product->update(['base_price' => 110]);

        $order = $this->checkout();

        $this->assertEquals(330, $order->total_amount); // 3 x 110, envío gratis (>= 250)
        $this->assertEquals(2, $this->product->variants()->first()->stock);
    }

    public function test_orders_below_250_pay_shipping(): void
    {
        $this->addToCart(2);
        $this->product->update(['base_price' => 110]);

        $this->assertEquals(232, $this->checkout()->total_amount); // 2 x 110 + 12 de envío
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
            'gender'      => 'unisex',
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
            'gender'      => 'unisex',
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
            'gender'      => 'unisex',
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

    private function complaintData(array $overrides = []): array
    {
        return array_merge([
            'consumer_name' => 'Ana Test',
            'document_type' => 'DNI',
            'document_number' => '12345678',
            'address' => 'Av. Siempre Viva 123, Lima',
            'phone' => '987654321',
            'email' => 'ana@example.com',
            'item_type' => 'producto',
            'order_reference' => 'TZN-20261024-ABC',
            'amount' => 120,
            'item_description' => 'Polo Básico talla S',
            'type' => 'reclamo',
            'detail' => 'La prenda llegó con una costura abierta.',
            'consumer_request' => 'Cambio por una prenda nueva.',
            'accepted' => '1',
        ], $overrides);
    }

    public function test_complaint_book_registers_a_numbered_sheet_and_emails_a_copy(): void
    {
        Mail::fake();

        $response = $this->post(route('complaints.store'), $this->complaintData());

        $complaint = Complaint::firstOrFail();
        $this->assertSame('LR-' . now()->format('Y') . '-' . str_pad($complaint->id, 6, '0', STR_PAD_LEFT), $complaint->code);
        $this->assertSame('pendiente', $complaint->status);
        $response->assertRedirect(route('complaints.show', $complaint->token));
        Mail::assertSent(ComplaintRegistered::class, fn ($mail) => $mail->hasTo('ana@example.com'));

        $this->get(route('complaints.show', $complaint->token))->assertOk()->assertSee($complaint->code)->assertSee('costura abierta');

        // El administrador responde y el consumidor lo ve en su constancia
        $this->actingAs($this->admin())
            ->put(route('admin.complaints.update', $complaint), ['response' => 'Te enviamos una prenda nueva.'])
            ->assertRedirect(route('admin.complaints.show', $complaint));
        $this->assertSame('respondido', $complaint->fresh()->status);
        $this->get(route('complaints.show', $complaint->token))->assertSee('Te enviamos una prenda nueva.');
    }

    public function test_minors_must_name_a_guardian_in_the_complaint_book(): void
    {
        $this->post(route('complaints.store'), $this->complaintData(['is_minor' => '1']))
            ->assertSessionHasErrors('guardian_name');
        $this->assertSame(0, Complaint::count());
    }

    public function test_help_pages_render_and_whatsapp_shows_only_when_configured(): void
    {
        $this->get(route('pages.faq'))->assertOk()->assertSee('FAQPage', false)->assertSee('S/ 250');
        $this->get(route('pages.shipping'))->assertOk();
        $this->get(route('complaints.create'))->assertOk();

        config(['tienda.whatsapp' => null]);
        $this->get(route('home'))->assertSee('Libro de Reclamaciones')->assertDontSee('wa.me', false);

        config(['tienda.whatsapp' => '51900000000']);
        $this->get(route('home'))->assertSee('https://wa.me/51900000000', false);
    }

    public function test_home_shows_category_rows_only_without_filters(): void
    {
        Product::create(['category_id' => $this->product->category_id, 'name' => 'Polo Dos', 'slug' => 'polo-dos', 'base_price' => 50, 'is_active' => true]);

        $this->get(route('home'))->assertSee('class="tz-row"', false)->assertSee('Novedades');
        $this->get(route('home', ['q' => 'polo']))->assertDontSee('class="tz-row"', false);
    }

    public function test_sections_show_their_gender_plus_unisex_and_their_categories(): void
    {
        $waffle = Category::where('slug', 'polos-waffle')->firstOrFail(); // solo varones
        $tops = Category::where('slug', 'tops')->firstOrFail();           // solo mujeres

        Product::create(['category_id' => $waffle->id, 'gender' => 'varones', 'name' => 'Waffle Varón', 'slug' => 'waffle-varon', 'base_price' => 60, 'is_active' => true]);
        Product::create(['category_id' => $tops->id, 'gender' => 'mujeres', 'name' => 'Top Mujer', 'slug' => 'top-mujer', 'base_price' => 40, 'is_active' => true]);
        // $this->product (Polo Básico) es unisex por defecto

        $this->get(route('home', ['genero' => 'varones']))
            ->assertOk()
            ->assertSee('Waffle Varón')->assertSee('Polo Básico')->assertDontSee('Top Mujer')
            ->assertSee('Polos waffle')
            ->assertDontSee(route('home', ['genero' => 'varones', 'category' => $tops->id]), false);

        $this->get(route('home', ['genero' => 'mujeres']))
            ->assertSee('Top Mujer')->assertSee('Polo Básico')->assertDontSee('Waffle Varón');

        // El menú principal tiene las dos secciones
        $this->get(route('home'))->assertSee('Varones')->assertSee('Mujeres');
    }

    public function test_product_gender_must_match_its_category_section(): void
    {
        $tops = Category::where('slug', 'tops')->firstOrFail();

        $this->actingAs($this->admin())->post(route('admin.products.store'), [
            'name'        => 'Top Equivocado',
            'category_id' => $tops->id,
            'gender'      => 'varones',
            'base_price'  => 40,
            'variants'    => [['size_id' => Size::first()->id, 'color_id' => Color::first()->id, 'stock' => 1]],
        ])->assertSessionHasErrors('gender');
    }

    public function test_clients_cannot_open_the_admin_panel(): void
    {
        $client = User::factory()->create();

        $this->actingAs($client)->get(route('admin.dashboard'))->assertRedirect('/');
    }
}

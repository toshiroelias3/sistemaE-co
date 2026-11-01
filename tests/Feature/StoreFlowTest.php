<?php

namespace Tests\Feature;

use App\Mail\ComplaintRegistered;
use App\Mail\OrderExpired;
use App\Mail\OrderPlaced;
use App\Mail\PaymentConfirmed;
use App\Models\Category;
use App\Models\Color;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\Product;
use App\Models\Size;
use App\Models\User;
use App\Support\ProductImages;
use Database\Seeders\InitialStoreSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
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
            'name' => 'Polo Básico',
            'slug' => 'polo-basico',
            'base_price' => 120,
            'is_active' => true,
        ]);
        $this->product->variants()->create([
            'size_id' => Size::first()->id,
            'color_id' => Color::first()->id,
            'sku' => 'TZN-TEST-1',
            'stock' => 5,
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
            'size_id' => Size::first()->id,
            'color_id' => Color::first()->id,
            'quantity' => $quantity,
        ])->assertOk()->assertJson(['cart_count' => $quantity]);
    }

    private function checkout(string $paymentMethod = 'yape'): Order
    {
        $this->post(route('checkout.store'), [
            'customer_name' => 'Ana Test',
            'customer_email' => 'ana@example.com',
            'shipping_address' => 'Av. Siempre Viva 123',
            'city' => 'Lima',
            'phone' => '987654321',
            'payment_method' => $paymentMethod,
            'terms' => '1',
        ])->assertRedirect();

        return Order::latest('id')->firstOrFail();
    }

    /**
     * Pedido pagado con Mercado Pago y respuesta falsa de la API para su pago.
     */
    private function mercadoPagoOrder(string $paymentStatus, float $amount = 132.0): Order
    {
        config(['services.mercadopago.access_token' => 'APP_USR-prueba']);
        Http::preventStrayRequests();
        Http::fake([
            'api.mercadopago.com/checkout/preferences' => Http::response(['id' => 'pref-1', 'init_point' => 'https://www.mercadopago.com.pe/checkout/v1/redirect?pref_id=pref-1']),
            'api.mercadopago.com/v1/payments/555' => fn () => Http::response([
                'id' => 555,
                'status' => $paymentStatus,
                'external_reference' => Order::latest('id')->value('reference_number'),
                'transaction_amount' => $amount,
                'currency_id' => 'PEN',
            ]),
        ]);

        $this->addToCart(1);

        return $this->checkout('mercadopago');
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
            'name' => 'Polo Básico',
            'category_id' => $this->product->category_id,
            'gender' => 'unisex',
            'base_price' => 99,
            // is_active omitido = switch desmarcado
            'variants' => [
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
            'name' => 'Casaca',
            'category_id' => $this->product->category_id,
            'gender' => 'unisex',
            'base_price' => 50,
            'variants' => [$variant, $variant],
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
            ->assertSee('<link rel="canonical" href="'.route('shop.product', 'polo-basico').'">', false)
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
            ->assertSee('Sitemap: '.route('seo.sitemap'));
    }

    public function test_uploaded_photos_get_light_webp_versions_that_are_cleaned_up(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->put(route('admin.products.update', $this->product), [
            'name' => 'Polo Básico',
            'category_id' => $this->product->category_id,
            'gender' => 'unisex',
            'base_price' => 120,
            'is_active' => 1,
            'variants' => [['size_id' => Size::first()->id, 'color_id' => Color::first()->id, 'stock' => 5]],
            'images' => [UploadedFile::fake()->image('polo.jpg', 1600, 2000)],
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
        $this->assertSame('LR-'.now()->format('Y').'-'.str_pad($complaint->id, 6, '0', STR_PAD_LEFT), $complaint->code);
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
            'name' => 'Top Equivocado',
            'category_id' => $tops->id,
            'gender' => 'varones',
            'base_price' => 40,
            'variants' => [['size_id' => Size::first()->id, 'color_id' => Color::first()->id, 'stock' => 1]],
        ])->assertSessionHasErrors('gender');
    }

    public function test_yape_orders_start_unpaid_email_instructions_and_confirm_by_admin(): void
    {
        Mail::fake();
        config(['tienda.yape' => '942817550']);

        $this->addToCart(1);
        $order = $this->checkout();

        $this->assertSame('pending', $order->status);
        $this->assertSame('unpaid', $order->payment_status);
        Mail::assertSent(OrderPlaced::class, fn ($mail) => $mail->hasTo('ana@example.com'));

        $this->get(route('checkout.success', $order->reference_number))
            ->assertSee('942 817 550')
            ->assertSee('S/ 132.00');

        // Las tarjetas ya no se aceptan
        $this->addToCart(1);
        $this->post(route('checkout.store'), ['payment_method' => 'card'])->assertSessionHasErrors('payment_method');

        // El admin valida el Yape y el cliente recibe la confirmación
        $this->actingAs($this->admin())
            ->put(route('admin.orders.update', $order->id), ['status' => 'processing', 'payment_status' => 'paid'])
            ->assertRedirect();
        Mail::assertSent(PaymentConfirmed::class, fn ($mail) => $mail->hasTo('ana@example.com'));
    }

    public function test_customers_can_reset_a_forgotten_password(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'cliente@example.com']);

        $this->post(route('password.email'), ['email' => 'cliente@example.com'])->assertSessionHas('success');

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $this->post(route('password.update'), [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'nueva-clave-123',
                'password_confirmation' => 'nueva-clave-123',
            ])->assertRedirect(route('login'));

            return Hash::check('nueva-clave-123', $user->fresh()->password);
        });
    }

    public function test_validation_messages_and_legal_pages_are_in_spanish(): void
    {
        $this->addToCart(1);
        $this->post(route('checkout.store'), [])->assertSessionHasErrors();
        $this->assertStringContainsString('obligatorio', session('errors')->first('customer_email'));

        $this->get(route('pages.terms'))->assertOk()->assertSee('Términos y condiciones')->assertSee('Yape');
        $this->get(route('pages.privacy'))->assertOk()->assertSee('29733');
        $this->get(route('checkout.index'))->assertSee(route('pages.terms'), false);
    }

    public function test_admin_loads_stock_as_a_color_by_size_table(): void
    {
        [$s, $m] = Size::orderBy('id')->take(2)->get()->all();
        [$beige, $navy] = Color::orderBy('id')->take(2)->get()->all();

        $this->actingAs($this->admin())->post(route('admin.products.store'), [
            'name' => 'Polera Tabla',
            'category_id' => $this->product->category_id,
            'gender' => 'unisex',
            'base_price' => 80,
            'stock' => [
                $beige->id => [$s->id => '5', $m->id => ''],   // M vacío: no se vende en beige
                $navy->id => [$s->id => '0', $m->id => '3'],  // S en 0: existe pero agotada
            ],
        ])->assertRedirect(route('admin.products.index'));

        $variants = Product::where('slug', 'polera-tabla')->firstOrFail()->variants;
        $this->assertCount(3, $variants);
        $this->assertEquals(5, $variants->firstWhere(fn ($v) => $v->color_id == $beige->id && $v->size_id == $s->id)->stock);
        $this->assertEquals(0, $variants->firstWhere(fn ($v) => $v->color_id == $navy->id && $v->size_id == $s->id)->stock);
        $this->assertNull($variants->firstWhere(fn ($v) => $v->color_id == $beige->id && $v->size_id == $m->id));

        $this->get(route('admin.products.edit', Product::where('slug', 'polera-tabla')->first()))
            ->assertOk()->assertSee('id="stock-matrix"', false)->assertSee('name="stock['.$navy->id.']['.$m->id.']"', false);

        // Sin ninguna celda con stock no se puede guardar
        $this->actingAs($this->admin())->post(route('admin.products.store'), [
            'name' => 'Vacía', 'category_id' => $this->product->category_id, 'gender' => 'unisex', 'base_price' => 10,
            'stock' => [$beige->id => [$s->id => '', $m->id => '']],
        ])->assertSessionHasErrors('variants');
    }

    public function test_product_text_cannot_break_out_of_structured_data_script(): void
    {
        $this->product->update([
            'name' => 'Polo </script><script>alert(1)</script>',
            'description' => '</script><img src=x onerror=alert(2)>',
        ]);

        $html = $this->get(route('shop.product', 'polo-basico'))->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringNotContainsString('<img src=x onerror=alert(2)>', $html);
        $this->assertStringContainsString('</script>', $html); // escapado dentro del JSON-LD
    }

    public function test_responses_carry_security_headers(): void
    {
        $this->get(route('home'))
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Content-Security-Policy');
    }

    public function test_weak_passwords_are_rejected_and_checkout_is_rate_limited(): void
    {
        $this->post(route('register'), [
            'name' => 'Ana', 'email' => 'ana2@example.com', 'password' => 'solo-letras', 'password_confirmation' => 'solo-letras',
        ])->assertSessionHasErrors('password');

        for ($i = 0; $i < 10; $i++) {
            $this->post(route('checkout.store'), []);
        }
        $this->post(route('checkout.store'), [])->assertStatus(429);
    }

    public function test_mercado_pago_checkout_sends_the_order_to_pay_on_mercado_pago(): void
    {
        Mail::fake();
        $order = $this->mercadoPagoOrder('approved');

        $this->assertSame('mercadopago', $order->payment_method);
        $this->assertSame('unpaid', $order->payment_status);
        $this->assertSame('pref-1', $order->mp_preference_id);

        Http::assertSent(fn ($request) => $request->url() === 'https://api.mercadopago.com/checkout/preferences'
            && $request['external_reference'] === $order->reference_number
            && $request['expires'] === true
            && $request['date_of_expiration'] === $order->paymentDeadline()->toIso8601String()
            && collect($request['items'])->sum(fn ($item) => $item['unit_price'] * $item['quantity']) == 132.0 // 120 + 12 de envío
            && collect($request['items'])->every(fn ($item) => $item['currency_id'] === 'PEN'));

        // Retomar el pago reutiliza el flujo y vuelve a Mercado Pago
        $this->get(route('checkout.mercadopago.pay', $order->reference_number))
            ->assertRedirect('https://www.mercadopago.com.pe/checkout/v1/redirect?pref_id=pref-1');
    }

    public function test_mercado_pago_webhook_confirms_the_payment_once(): void
    {
        Mail::fake();
        $order = $this->mercadoPagoOrder('approved');

        $this->postJson(route('webhooks.mercadopago').'?data.id=555&type=payment', ['type' => 'payment', 'data' => ['id' => '555']])->assertOk();
        $this->postJson(route('webhooks.mercadopago').'?data.id=555&type=payment', ['type' => 'payment', 'data' => ['id' => '555']])->assertOk();

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('processing', $order->status);
        $this->assertSame('555', $order->mp_payment_id);
        Mail::assertSent(PaymentConfirmed::class, 1);
    }

    public function test_mercado_pago_webhook_rejects_a_forged_signature(): void
    {
        Mail::fake();
        $order = $this->mercadoPagoOrder('approved');
        config(['services.mercadopago.webhook_secret' => 'secreto']);

        $this->postJson(route('webhooks.mercadopago').'?data.id=555&type=payment', ['type' => 'payment', 'data' => ['id' => '555']], [
            'x-signature' => 'ts=1700000000,v1=firma-falsa',
            'x-request-id' => 'req-1',
        ])->assertStatus(401);
        $this->assertSame('unpaid', $order->fresh()->payment_status);

        $valid = hash_hmac('sha256', 'id:555;request-id:req-1;ts:1700000000;', 'secreto');
        $this->postJson(route('webhooks.mercadopago').'?data.id=555&type=payment', ['type' => 'payment', 'data' => ['id' => '555']], [
            'x-signature' => "ts=1700000000,v1={$valid}",
            'x-request-id' => 'req-1',
        ])->assertOk();
        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_mercado_pago_payment_for_less_than_the_total_is_not_accepted(): void
    {
        Mail::fake();
        $order = $this->mercadoPagoOrder('approved', amount: 1.0);

        $this->postJson(route('webhooks.mercadopago').'?data.id=555&type=payment', ['type' => 'payment', 'data' => ['id' => '555']])->assertOk();

        $this->assertSame('unpaid', $order->fresh()->payment_status);
        Mail::assertNotSent(PaymentConfirmed::class);
    }

    public function test_returning_from_mercado_pago_uses_the_api_status_not_the_url(): void
    {
        Mail::fake();
        $order = $this->mercadoPagoOrder('rejected');

        // La URL dice "approved", pero la API dice "rejected": manda la API
        $this->get(route('checkout.mercadopago.return', $order->reference_number).'?payment_id=555&status=approved')
            ->assertRedirect(route('checkout.success', $order->reference_number))
            ->assertSessionHas('error');

        $this->assertSame('failed', $order->fresh()->payment_status);
        $this->get(route('checkout.success', $order->reference_number))->assertSee('Intentar el pago de nuevo');
    }

    public function test_mercado_pago_is_not_offered_without_credentials(): void
    {
        config(['services.mercadopago.access_token' => null]);
        $this->addToCart(1);

        $this->get(route('checkout.index'))->assertDontSee('value="mercadopago"', false);
        $this->post(route('checkout.store'), ['payment_method' => 'mercadopago'])->assertSessionHasErrors('payment_method');
    }

    public function test_seeding_again_keeps_the_admin_password(): void
    {
        $this->assertFalse(Hash::check('admin1234', $this->admin()->password));

        $this->admin()->update(['password' => Hash::make('una-clave-propia-larga')]);
        $this->seed(InitialStoreSeeder::class);

        $this->assertTrue(Hash::check('una-clave-propia-larga', $this->admin()->password));
    }

    public function test_expired_unpaid_orders_are_cancelled_and_restocked(): void
    {
        Mail::fake();

        $this->addToCart(1);
        $expired = $this->checkout();
        $this->addToCart(1);
        $paid = $this->checkout();
        $paid->update(['payment_status' => 'paid']);

        $this->travel(config('tienda.horas_para_pagar') + 1)->hours();

        $this->addToCart(1);
        $recent = $this->checkout();

        $this->artisan('orders:cancel-unpaid')->assertSuccessful();

        $this->assertSame('cancelled', $expired->refresh()->status);
        $this->assertSame('pending', $paid->refresh()->status);
        $this->assertSame('pending', $recent->refresh()->status);
        $this->assertSame(3, $this->product->variants()->first()->stock);
        Mail::assertSent(OrderExpired::class, 1);
        Mail::assertSent(OrderExpired::class, fn ($mail) => $mail->order->is($expired));

        $this->artisan('orders:cancel-unpaid')->assertSuccessful();
        Mail::assertSent(OrderExpired::class, 1);
    }

    public function test_expiring_order_with_an_approved_payment_is_confirmed_not_cancelled(): void
    {
        Mail::fake();
        $order = $this->mercadoPagoOrder('approved');
        $order->update(['mp_payment_id' => '555']);

        $this->travel(config('tienda.horas_para_pagar') + 1)->hours();
        $this->artisan('orders:cancel-unpaid')->assertSuccessful();

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('processing', $order->status);
        $this->assertSame(4, $this->product->variants()->first()->stock);
        Mail::assertNotSent(OrderExpired::class);
    }

    public function test_late_payment_reactivates_a_cancelled_order_when_stock_remains(): void
    {
        Mail::fake();
        $order = $this->mercadoPagoOrder('approved');
        $this->travel(config('tienda.horas_para_pagar') + 1)->hours();
        $this->artisan('orders:cancel-unpaid')->assertSuccessful();
        $this->assertSame(5, $this->product->variants()->first()->stock);

        $this->postJson(route('webhooks.mercadopago').'?data.id=555&type=payment', ['type' => 'payment', 'data' => ['id' => '555']])->assertOk();

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('processing', $order->status);
        $this->assertSame(4, $this->product->variants()->first()->stock);
        Mail::assertSent(PaymentConfirmed::class, 1);
    }

    public function test_late_payment_without_stock_stays_cancelled_and_alerts_the_admin(): void
    {
        Mail::fake();
        $order = $this->mercadoPagoOrder('approved');
        $this->travel(config('tienda.horas_para_pagar') + 1)->hours();
        $this->artisan('orders:cancel-unpaid')->assertSuccessful();
        $this->product->variants()->update(['stock' => 0]);

        $this->postJson(route('webhooks.mercadopago').'?data.id=555&type=payment', ['type' => 'payment', 'data' => ['id' => '555']])->assertOk();

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('cancelled', $order->status);
        $this->assertSame(0, $this->product->variants()->first()->stock);
        Mail::assertNotSent(PaymentConfirmed::class);
        $this->actingAs($this->admin())->get(route('admin.dashboard'))->assertSee('1 pago tardío');
    }

    public function test_confirmation_shows_the_payment_deadline_and_delivery_times(): void
    {
        Mail::fake();
        $this->addToCart(1);
        $order = $this->checkout();
        $deadline = $order->paymentDeadline()->timezone('America/Lima')->format('d/m/Y');

        $this->get(route('checkout.success', $order->reference_number))
            ->assertOk()
            ->assertSee($deadline)
            ->assertSee(config('tienda.entrega_lima'))
            ->assertSee(config('tienda.entrega_provincias'));
    }

    public function test_clients_cannot_open_the_admin_panel(): void
    {
        $client = User::factory()->create();

        $this->actingAs($client)->get(route('admin.dashboard'))->assertRedirect('/');
    }
}

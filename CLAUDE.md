# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

TEZAN — a clothing e-commerce store (Spanish-language UI, routes, and code comments) built on Laravel 13 / PHP 8.3 with server-rendered Blade views. The README is the stock Laravel README and contains nothing project-specific.

## Commands

```bash
composer setup                 # first-time install: deps, .env, key, migrate, npm build
composer dev                   # runs artisan serve + queue:listen + pail logs + vite concurrently
npm run dev / npm run build    # Vite only
php artisan migrate
php artisan db:seed --class=InitialStoreSeeder   # admin user (admin@tezan.com / admin1234) + catalog base data
php artisan storage:link       # required for product images (public/storage -> storage/app/public)

composer test                  # clears config cache, then php artisan test
php artisan test --filter=SomeTest               # single test class/method
vendor/bin/pint                # code style (Laravel Pint)
```

- Local `.env` uses **MySQL** (`tienda_ropa_db`); `.env.example` defaults to SQLite. Tests run on in-memory SQLite (phpunit.xml).
- The local XAMPP PHP (`C:\xampp\php83`) does not enable `pdo_sqlite`, and `php artisan test` spawns a subprocess that drops `-d` flags. Run tests with:
  `php -d extension_dir=C:/xampp/php83/ext -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit [--filter=name]`
- End-to-end store/admin flows are covered in `tests/Feature/StoreFlowTest.php`.

## Architecture

**Routing** — everything lives in [routes/web.php](routes/web.php), split into four zones: public shop/cart/checkout, guest auth, authenticated customer area (`/mis-pedidos`), and `/admin` (middleware `['auth','admin']`). The `admin` alias is registered in [bootstrap/app.php](bootstrap/app.php) and maps to `AdminMiddleware`, which checks `users.role === 'admin'` (column added by a migration; no roles/permissions package).

**Controllers** are namespaced by area: `Admin/` (dashboard, categories, products, colors, orders), `Shop/` (catalog, cart, checkout, customer orders), `Auth/` (hand-rolled login/register — no Breeze/Fortify). Admin form validation uses FormRequests in `app/Http/Requests/Admin/`.

**Catalog model** — `Product` has many `ProductVariant`s; a variant is the unique (product, `Size`, `Color`) combination and holds `sku` and `stock`. Price lives on the product (`base_price`), not the variant. `Category` is self-referencing via `parent_id` (parent/children). `ProductImage` rows store paths on the `public` disk (`products/...`) with one `is_primary` flag; render with `asset('storage/'.$path)`. Colors can be created inline from the product form via an AJAX endpoint.

**Cart** — session-only (`session('cart')`), read through `App\Support\Cart` (items/count/subtotal and the shipping rule: S/ 12, free from S/ 200). Keyed by `"{product_id}-{size_id}-{color_id}"`. Each entry snapshots name, price, size/color names, image URL, and `max_stock`. Cart add/remove endpoints serve both normal form posts (redirect) and AJAX (JSON with `cart_count` and re-rendered `shop.partials.mini-cart-items` HTML) — keep both paths working when changing them.

**Checkout** — supports guests and logged-in users (`orders.user_id` is nullable; `customer_name`/`customer_email` stored on the order). `CheckoutController@store` runs in a DB transaction, `lockForUpdate()`s each variant, takes the **current** price from the DB (not the session snapshot), decrements stock, and writes `OrderItem`s with that historical price. Shipping isn't stored; `Order::subtotal`/`shipping_cost` accessors derive it from the items. Status/payment values and labels live in `Order::STATUSES` / `PAYMENT_STATUSES` (render with `partials.order-badges`). Cancelling an order in admin restocks its variants; un-cancelling decrements again.

**Variants with sales** — `order_items.product_variant_id` is a FK, so variants are never bulk-deleted on product update: `ProductController::syncVariants` upserts by (size, color) and zeroes the stock of removed variants that have sales. Payment is simulated (`payment_status = 'paid'`, methods `card`/`yape`). Orders are identified publicly by `reference_number` (`TZN-YYYYMMDD-XXXXXXXXXXXX`).

**Frontend** — Bootstrap 5 + jQuery + Bootstrap Icons, compiled by Vite from [resources/sass/app.scss](resources/sass/app.scss) and [resources/js/app.js](resources/js/app.js) (which exposes `window.bootstrap` and `window.$`). Layouts: `layouts/app` (shop) and `layouts/admin`; both render the `success`/`error` flash messages, so views must not repeat them. Pagination uses Bootstrap 5 views (`AppServiceProvider`), which also feeds top-level categories to the navbar. Tailwind is installed and `resources/css/app.css` exists but is **not** a Vite input — styling is Bootstrap/SCSS.

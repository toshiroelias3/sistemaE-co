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
php artisan images:optimize    # generate 400/800/1200px WebP versions for existing product photos (--force to redo)

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

**Sections Varones / Mujeres** — `categories.audience` (`ambos`/`varones`/`mujeres`, `Category::AUDIENCES`) says which section a category appears in; `products.gender` (`varones`/`mujeres`/`unisex`, `Product::GENDERS`) says who the garment is for. Section URLs use `?genero=varones|mujeres`; `Product::forGender()` returns that gender **plus unisex**, `Category::forGender()` returns `ambos` + that audience. `ProductRequest` rejects a gender the category doesn't allow (e.g. a Tops garment for varones). The canonical category list lives in `CatalogCategoriesSeeder` (idempotent).

**Catalog model** — `Product` has many `ProductVariant`s; a variant is the unique (product, `Size`, `Color`) combination and holds `sku` and `stock`. Price lives on the product (`base_price`), not the variant. `Category` is self-referencing via `parent_id` (parent/children). `ProductImage` rows store paths on the `public` disk (`products/...`) with one `is_primary` flag; render with `asset('storage/'.$path)`. Colors can be created inline from the product form via an AJAX endpoint. Uploads also get WebP derivatives (`App\Support\ProductImages`, needs PHP GD — enabled in the local XAMPP php.ini); render photos with `$image->url($width)` / `$image->srcset()`, which fall back to the original when no derivatives exist.

**Store settings** — `config/tienda.php` reads `TIENDA_*` from `.env` (razón social, RUC, dirección, email, WhatsApp, social URLs). Anything that depends on a setting (WhatsApp button, social icons, RUC in the footer/complaint sheet) only renders when it is set — never hardcode these values in views.

**Payments (Yape only, manual)** — checkout accepts only `payment_method=yape`; orders are created `pending` + `unpaid` (stock is reserved). The customer gets `App\Mail\OrderPlaced` with the Yape number/amount (`config('tienda.yape')`) and sees `shop.partials.yape-instructions` on the success page and in "Mis pedidos". When the admin switches `payment_status` to `paid`, `AdminOrderController` sends `App\Mail\PaymentConfirmed`. Mail failures are logged, never fatal. Mail is Gmail SMTP in production; locally `MAIL_MAILER=log` until the app password is set.

**Security** — `App\Http\Middleware\SecurityHeaders` (appended globally) sets nosniff, X-Frame-Options DENY, Referrer-Policy, Permissions-Policy, HSTS on https, and a CSP (skipped only in `local` while `public/hot` exists). If you add an external script/style/font/image host, add it to the CSP. When passing flags to `@json` inside `<script>`, always include `JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT` (custom flags replace Blade's safe defaults). Login/register/reset/complaints are throttled 5/min, checkout 10/min, cart 60/min; failed logins are logged.

**Auth extras** — password reset uses Laravel's `Password` broker (`/olvide-mi-contrasena`, `/restablecer-contrasena/{token}`). UI and validation messages are Spanish (`APP_LOCALE=es`, `lang/es` from `laravel-lang/common`). Legal pages: `/terminos-y-condiciones`, `/politica-de-privacidad` (drafts pending legal review). Deployment steps live in `DEPLOY.md`.

**Libro de Reclamaciones** — legally required in Peru. `Complaint` model (`complaints` table) gets a correlative `code` (`LR-YYYY-000001`) after insert and a private `token` for the consumer's constancia URL; the consumer gets an email copy (`App\Mail\ComplaintRegistered`, failures are logged, not fatal). Admin answers at `/admin/reclamaciones`; legal response window is `Complaint::RESPONSE_DAYS` (15 business days). The sheet markup lives in `shop/complaints/partials/sheet.blade.php` (inline styles so it works in email too).

**SEO** — the shop layout emits canonical, Open Graph and `@stack('head')`; private pages set `@section('noindex', '1')`. The product page adds Product/BreadcrumbList JSON-LD. `/sitemap.xml` and `/robots.txt` are dynamic routes (`Shop\SeoController`); there is no static `public/robots.txt`.

**Cart** — session-only (`session('cart')`), read through `App\Support\Cart` (items/count/subtotal and the shipping rule: S/ 12, free from S/ 250). Keyed by `"{product_id}-{size_id}-{color_id}"`. Each entry snapshots name, price, size/color names, image URL, and `max_stock`. Cart add/remove endpoints serve both normal form posts (redirect) and AJAX (JSON with `cart_count` and re-rendered `shop.partials.mini-cart-items` HTML) — keep both paths working when changing them.

**Checkout** — supports guests and logged-in users (`orders.user_id` is nullable; `customer_name`/`customer_email` stored on the order). `CheckoutController@store` runs in a DB transaction, `lockForUpdate()`s each variant, takes the **current** price from the DB (not the session snapshot), decrements stock, and writes `OrderItem`s with that historical price. Shipping isn't stored; `Order::subtotal`/`shipping_cost` accessors derive it from the items. Status/payment values and labels live in `Order::STATUSES` / `PAYMENT_STATUSES` (render with `partials.order-badges`). Cancelling an order in admin restocks its variants; un-cancelling decrements again.

**Admin stock table** — the product form posts stock as a color × size grid `stock[color_id][size_id]` (`admin/products/partials/variants-card` + `matrix-row`); `ProductRequest::prepareForValidation` turns every non-empty cell into a `variants[]` entry (empty cell = combination not sold, `0` = exists but sold out). Posting `variants[]` directly still works.

**Variants with sales** — `order_items.product_variant_id` is a FK, so variants are never bulk-deleted on product update: `ProductController::syncVariants` upserts by (size, color) and zeroes the stock of removed variants that have sales. Orders are identified publicly by `reference_number` (`TZN-YYYYMMDD-XXXXXXXXXXXX`).

**Frontend** — Bootstrap 5 + jQuery + Bootstrap Icons, compiled by Vite from [resources/sass/app.scss](resources/sass/app.scss) and [resources/js/app.js](resources/js/app.js) (which exposes `window.bootstrap` and `window.$`). Layouts: `layouts/app` (shop) and `layouts/admin`; both render the `success`/`error` flash messages, so views must not repeat them. Pagination uses Bootstrap 5 views (`AppServiceProvider`), which also feeds top-level categories to the navbar. Tailwind is installed and `resources/css/app.css` exists but is **not** a Vite input — styling is Bootstrap/SCSS.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.3. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>

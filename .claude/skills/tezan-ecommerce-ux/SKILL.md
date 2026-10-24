---
name: tezan-ecommerce-ux
description: UX and conversion rules for the TEZAN clothing store (Laravel + Blade + Bootstrap). Use this whenever you touch any public shop page or flow — home/catalog, filters and sorting, product page (sizes, colors, stock, gallery), the bag (mini-cart offcanvas or /carrito), checkout, order confirmation, "mis pedidos", login/register — or when the user asks to improve sales, conversion, trust, usability, mobile shopping, or "make it feel more like a real online store", even if they don't say "UX".
---

# TEZAN e-commerce UX

TEZAN sells Peruvian-made basics (polos, conjuntos) to young unisex customers who mostly shop from a phone. The store's job is simple: let someone find their size and color, trust the quality, and pay without friction. Every change should make one of those three things easier — if it doesn't, it probably isn't worth adding.

Read `PRODUCT.md` (product truth) and `DESIGN.md` (visual system, Zara-style e-commerce) before changing a page. The visual system lives in `resources/sass/_tienda.scss` under `body.tz`; reuse its classes (`tz-label`, `tz-card`, `tz-grid`, `tz-choice`, `tz-details`, `tz-display`) instead of inventing new ones.

## Store facts you must keep true

These come from the code and PRODUCT.md. Never contradict them in copy and never invent new ones (no return policy, delivery times, discounts, reviews or "best seller" claims until the owner defines them).

- Currency is soles, written `S/ 35.00` (`number_format($x, 2)`).
- Shipping: S/ 12, free from S/ 250 — always read `App\Support\Cart::SHIPPING_COST` / `FREE_SHIPPING_FROM`, never hardcode.
- Payment: card or Yape (simulated today). Despacho a todo el Perú. Hecho en Perú.
- Vocabulary: it's **"bolsa"**, never "carrito" in UI copy; the add button is **"Añadir a la bolsa"**. Keep one name per action across the whole flow — the button, the toast and the page title must match.
- UI copy is Spanish (Peru), sentence case for text; small uppercase labels (`tz-label`) only for navigation, buttons and section labels.

## How the flows work (don't break them)

- Cart is session-only via `App\Support\Cart`. `cart.add`, `cart.update` and `cart.remove` answer both normal form posts (redirect + flash) and AJAX (JSON with `cart_count` + rendered `shop.partials.mini-cart-items`). Any new cart interaction must keep both paths working — the no-JS path is the fallback.
- Elements showing the bag count use `data-cart-count`; update all of them after AJAX calls.
- Stock lives on variants (size × color). The product page receives `$variantMatrix`; disable combinations with no stock instead of letting the server reject them.
- Checkout re-reads prices and locks stock in `CheckoutController@store`; UI totals are estimates, the server is the truth.
- Flash messages are rendered by the layout — views must not repeat them.

## Page checklists

Use these as the bar when building or reviewing a page. Fix gaps in priority order (top of each list first).

**Catalog / home**
1. Products visible within the first scroll on mobile; categories reachable from the header.
2. Each card: photo (3:4), name, price; second photo on hover. Out-of-stock or no-photo states look intentional ("Foto próximamente").
3. Sorting and category filters keep other query params; empty results explain what happened and offer "Ver toda la colección".
4. Pagination keeps `#catalogo` fragment and the query string.

**Product page**
1. Price, color, size and the add button visible without scrolling on desktop; on mobile right after the gallery.
2. Single available option is auto-selected; sold-out combos are crossed out, not hidden.
3. Show real stock urgency only when true (≤ 3 units), never fake scarcity.
4. Shipping and payment info near the button (details/summary accordions).
5. Related products from the same category.

**Bag & checkout**
1. Free-shipping progress ("Te faltan S/ X para el envío gratis").
2. Editable quantities capped at stock; removing an item never needs a page reload in the offcanvas.
3. Checkout asks only what's needed (name, email, address, city, phone, payment, terms); guests can buy; validation errors appear next to the form, inputs keep `old()` values.
4. The submit button prevents double orders (disabled after click).
5. Confirmation page shows the reference number, items, shipping and total, plus "Ver mis pedidos" for logged-in users.

**Trust**
- Real contact (`config('tienda.email')`), clear shipping and payment, consistent prices everywhere. Dead links (`href="#"`) erode trust — remove them rather than leave placeholders.

## Mobile first

Most buyers are on phones. Check every change at 390px: tap targets ≥ 44px, no horizontal scroll, sticky header doesn't cover content, the bag offcanvas fits (`min(420px, 100vw)`). Note: headless Chrome on this Windows machine won't render narrower than ~484px; use `.agents/skills/design-taste-frontend/scripts/inspect.mjs <url> --widths 1440,390` for real phone-width captures.

## Verify

1. `npm run build`
2. Tests (XAMPP PHP lacks pdo_sqlite in the default config):
   `php -d extension_dir=C:/xampp/php83/ext -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit`
   `tests/Feature/StoreFlowTest.php` covers cart, checkout, shipping rule, stock and admin. Add a test there when you change a flow's behavior.
3. Look at the rendered page at 1440 and 390 before calling it done.

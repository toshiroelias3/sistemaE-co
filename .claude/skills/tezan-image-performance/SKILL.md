---
name: tezan-image-performance
description: Product-image and page-speed optimization for the TEZAN Laravel store — converting uploads to WebP, generating responsive sizes (srcset/sizes), lazy loading, fetchpriority for the hero, explicit dimensions to avoid layout shift, and keeping the product gallery fast on phones. Use this whenever the user mentions slow pages, heavy images, PageSpeed/Lighthouse, Core Web Vitals (LCP, CLS), image upload handling in the admin, thumbnails, WebP, or adds a new place that renders product photos, even if they don't say "performance".
---

# TEZAN image performance

TEZAN is a photo-led store viewed mostly on phones over mobile data. Product photos are the heaviest thing on every page, so they decide whether the store feels fast. Today admins upload originals (JPG/PNG/WebP up to 10 MB) and every grid card loads the full-size file — that's the main thing to fix.

## Current state (verify before relying on it)

- Uploads: `ProductController::storeImages()` stores originals with `$file->store('products', 'public')`; rows in `product_images` (`image_path`, `is_primary`). Views render `asset('storage/'.$path)`.
- Renders: `shop/partials/product-tile.blade.php` (grid card, 3:4, hover cycles all images), `shop/product.blade.php` (gallery), `shop/home.blade.php` (hero is an Unsplash URL; editorial banners), mini cart and cart.
- **PHP GD is not enabled** in `C:\xampp\php83\php.ini` (`php -m` shows no `gd`), and no image package (Intervention, spatie/image) is installed. Server-side resizing/WebP needs the user to enable `extension=gd` in php.ini (and restart Apache) — ask before touching their PHP config, and check `php -r "print_r(gd_info());"` reports WebP support afterwards.

## Approach

1. **Free wins first (no dependencies)** — do these before anything else:
   - `loading="lazy"` + `decoding="async"` on every product image except the first visible one.
   - The LCP image (hero or first gallery photo) gets `fetchpriority="high"` and no lazy loading.
   - Give images intrinsic `width`/`height` (or rely on the existing `aspect-ratio` containers in `_tienda.scss`) so nothing shifts while loading.
   - In grid cards, only the first image should load eagerly-ish; the hover alternates can be lazy — consider loading the 2nd image only on first hover (`data-src` swapped in on `mouseenter`) to avoid downloading every photo of every product.
2. **Responsive derivatives (needs GD or an image package)**:
   - On upload, generate WebP variants at fixed widths (e.g. 400, 800, 1200) next to the original: `products/{name}-400.webp` etc. Keep the original as fallback. Do it inside `storeImages()` so admin behavior stays the same, and delete derivatives in `destroyImage()`/`destroy()` alongside the original.
   - Prefer adding a small helper (e.g. `App\Support\ProductImageUrl` or an accessor on `ProductImage`) that returns `src`, `srcset` and falls back to the original when derivatives don't exist — existing images keep working without a migration.
   - Provide an artisan command to backfill derivatives for existing images so the switch isn't all-or-nothing.
   - `sizes` hints: grid card `(min-width: 992px) 25vw, 50vw`; gallery `(min-width: 992px) 33vw, 100vw`; hero `100vw`.
3. **External hero image**: the home hero uses Unsplash; request an appropriate width via its URL params (`w=`) and add `srcset` with a few widths. When TEZAN provides its own campaign photo, treat it like a product image (derivatives + preload).

## Guardrails

- Never drop the original upload — derivatives are disposable and regenerable.
- Keep `ProductRequest` limits (jpeg/png/jpg/webp, 10 MB) unless the user asks.
- Don't break the hover photo cycling in `product-tile` or the gallery thumbs; they read `.hover-slide-img` and `data-large-src`.
- Measure, don't guess: capture before/after with Lighthouse in Chrome DevTools (or `inspect.mjs`) and report LCP/total image bytes.

## Verify

- `npm run build`, then load `/` and a product page; in DevTools Network filter "Img" and confirm small WebP files load for the grid and lazy images load only when scrolled.
- Run the test suite (see CLAUDE.md for the pdo_sqlite command). If you change uploads, add a test that uploads a fake image (`UploadedFile::fake()->image('p.jpg', 1600, 2000)`) and asserts the derivative paths exist on the fake `public` disk.

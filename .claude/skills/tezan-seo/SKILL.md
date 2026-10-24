---
name: tezan-seo
description: SEO for the TEZAN Laravel clothing store — page titles and meta descriptions, canonical URLs, Open Graph/WhatsApp/Instagram share previews, schema.org Product/Offer/BreadcrumbList JSON-LD, sitemap.xml and robots.txt. Use this whenever the user mentions Google, ranking, search results, SEO, sharing links on WhatsApp/Facebook/Instagram, link previews, rich results, sitemap, robots, indexing, or adds/changes a public page or product URL, even if they don't say "SEO".
---

# TEZAN SEO

Goal: each public page is findable on Google and looks good when shared on WhatsApp or Instagram — the two places TEZAN's audience actually discovers clothes. Product pages matter most: they're what people search for and share.

## Where things live

- Layout: `resources/views/layouts/app.blade.php`. It already yields `title` (rendered as `{title} | TEZAN`) and `meta_description`. Add new head tags there, driven by sections or `@stack('head')`, so every page inherits sane defaults and pages override only what they know.
- Product page: `resources/views/shop/product.blade.php` (data: `$product` with `category`, `images` primary-first, `variants`, plus `$sizes`).
- Routes: `routes/web.php`. Public, indexable: `home` (`/`), `shop.product` (`/producto/{slug}`). Not indexable: cart, checkout, success, login, register, mis-pedidos, admin.
- `public/robots.txt` currently allows everything.

## Rules

**Titles and descriptions**
- Title ≤ 60 chars, specific: product name first (`Polo Oversize | TEZAN`). Home: what it is + where ("Básicos de algodón hechos en Perú").
- Description ≤ 155 chars from real data (`Str::limit($product->description, 155)`), falling back to a factual line. Never invent claims (materials, reviews, discounts) that PRODUCT.md doesn't confirm.

**Canonical**
- Add `<link rel="canonical">` on every indexable page. The catalog's filter/sort/page query strings create duplicates: canonicalize `/?sort=...` to `/`, but keep `?category=ID` canonical to itself (category pages are worth indexing). Paginated pages canonicalize to themselves.

**noindex**
- Add `<meta name="robots" content="noindex">` to cart, checkout, checkout success, login, register, mis-pedidos and search results (`?q=`). These pages are useless in Google and some are personal.

**Open Graph / social**
- `og:type` (`product` on product pages, `website` elsewhere), `og:title`, `og:description`, `og:url`, `og:image` (absolute URL via `asset('storage/'.$path)` — the primary image), `og:locale` `es_PE`, `og:site_name` TEZAN. Add `twitter:card` `summary_large_image`. Product: `product:price:amount` and `product:price:currency` `PEN`.
- WhatsApp needs an absolute, publicly reachable image URL; `APP_URL` must be the real domain in production or previews break.

**JSON-LD (product page)**
- `Product` with `name`, `description`, `image` (all image URLs), `sku` (or product id), `brand` {`@type`: Brand, name TEZAN}, and `offers` {`@type`: Offer, `priceCurrency` PEN, `price` from `base_price`, `availability` `https://schema.org/InStock` if any variant stock > 0 else `OutOfStock`, `url`}.
- `BreadcrumbList`: Inicio → categoría → producto.
- Build it as a PHP array and print with `@json(...)` inside `<script type="application/ld+json">` so escaping is correct. Never add `aggregateRating` or `review` — there are no real reviews.

**Sitemap and robots**
- No sitemap package is installed. Prefer a small route (`/sitemap.xml`) returning XML from a Blade view: home, each active top-level/child category URL, and every active product (`updated_at` as `lastmod`). Exclude inactive/soft-deleted products.
- robots.txt: disallow `/carrito`, `/checkout`, `/mis-pedidos`, `/admin`, `/login`, `/register`, and add `Sitemap: {APP_URL}/sitemap.xml`.

**Semantics that also help SEO**
- One `<h1>` per page (product name on product pages). Descriptive `alt` on the first product image; decorative duplicates get `alt=""`.
- Fast pages rank better — coordinate with the `tezan-image-performance` skill.

## Verify

- `php artisan view:clear`, then fetch a product page and check the head: `curl -s http://127.0.0.1:8000/producto/<slug> | grep -E "og:|canonical|ld\+json"`.
- Validate JSON-LD by pasting the script into https://validator.schema.org (tell the user; you can't post it yourself without asking).
- Add a feature test asserting the product page contains `application/ld+json` and the correct `og:title`, alongside `tests/Feature/StoreFlowTest.php`.

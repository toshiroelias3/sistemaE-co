{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('home') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
@foreach(['pages.faq', 'pages.shipping', 'complaints.create'] as $page)
    <url>
        <loc>{{ route($page) }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.3</priority>
    </url>
@endforeach
@foreach(\App\Models\Product::SECTIONS as $section => $label)
    <url>
        <loc>{{ route('home', ['genero' => $section]) }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
@endforeach
@foreach($categories as $category)
    <url>
        <loc>{{ route('home', ['category' => $category->id]) }}</loc>
        <lastmod>{{ $category->updated_at?->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
@endforeach
@foreach($products as $product)
    <url>
        <loc>{{ route('shop.product', $product->slug) }}</loc>
        <lastmod>{{ $product->updated_at?->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
@endforeach
</urlset>

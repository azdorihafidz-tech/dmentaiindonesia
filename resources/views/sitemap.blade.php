<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ route('home') }}</loc><lastmod>{{ now()->toAtomString() }}</lastmod></url>
    @foreach ($pages as $page)
        <url><loc>{{ route('pages.show', $page->slug) }}</loc>@if ($page->updated_at)<lastmod>{{ $page->updated_at->toAtomString() }}</lastmod>@endif</url>
    @endforeach
    @foreach ($products as $product)
        <url><loc>{{ route('products.show', $product->slug) }}</loc>@if ($product->updated_at)<lastmod>{{ $product->updated_at->toAtomString() }}</lastmod>@endif</url>
    @endforeach
</urlset>

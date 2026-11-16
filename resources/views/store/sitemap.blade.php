{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach (['home' => 'daily', 'shop' => 'daily', 'deals' => 'daily', 'new' => 'daily', 'categories' => 'weekly', 'about' => 'monthly', 'contact' => 'monthly'] as $r => $freq)
        <url><loc>{{ route($r) }}</loc><changefreq>{{ $freq }}</changefreq></url>
    @endforeach
    @foreach ($categories as $c)
        <url><loc>{{ route('category', $c) }}</loc><lastmod>{{ $c->updated_at?->toAtomString() }}</lastmod><changefreq>daily</changefreq></url>
    @endforeach
    @foreach ($products as $p)
        <url><loc>{{ route('product', $p) }}</loc><lastmod>{{ $p->updated_at?->toAtomString() }}</lastmod></url>
    @endforeach
</urlset>

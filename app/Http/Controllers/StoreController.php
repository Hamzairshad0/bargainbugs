<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StoreController extends Controller
{
    private const PER_PAGE = 24;

    public const SORTS = [
        'featured' => 'Featured',
        'new' => 'Newest',
        'low' => 'Price: low to high',
        'high' => 'Price: high to low',
        'save' => 'Biggest saving',
        'pop' => 'Best selling',
    ];

    // ------------------------------------------------------------------ pages

    public function home(Request $request)
    {
        $featured = Product::visible()->with('category')->where('is_featured', true)->latest('listed_at')->take(3)->get();
        $deals = $this->dealsQuery()->with('category')->take(12)->get();

        $hero = $featured->count() >= 2 ? $featured : ($deals->count() >= 2 ? $deals->take(3) : Product::visible()->latest('listed_at')->take(3)->get());

        $products = $this->catalog($request)->paginate(self::PER_PAGE)->withPath(route('shop'));
        if ($request->boolean('partial')) {
            return $this->partial($products);
        }

        $live = $products->total();
        $stats = collect([
            ['value' => (int) Product::sum('quantity_sold'), 'suffix' => '+', 'label' => 'Items sold'],
            ['value' => (float) setting('ebay_positive_percent'), 'suffix' => '%', 'label' => 'Positive feedback', 'dec' => 1],
            ['value' => $live, 'suffix' => '', 'label' => 'Bargains live'],
        ])->filter(fn ($s) => $s['value'] > 0)->values();

        return view('store.home', [
            'hero' => $hero,
            'products' => $products,
            'chips' => $this->chips(null),
            'deals' => $deals,
            'stats' => $stats,
            'storyImages' => Product::visible()->whereNotNull('images')->latest('listed_at')->take(4)->get(),
            'testimonials' => $this->testimonials(),
            'maxDiscount' => (int) $deals->max('discount_percent'),
        ]);
    }

    public function shop(Request $request, ?Category $category = null)
    {
        if ($category && ! $category->is_visible) {
            abort(404);
        }

        $products = $this->catalog($request, $category)->paginate(self::PER_PAGE)->withQueryString();
        if ($request->boolean('partial')) {
            return $this->partial($products);
        }

        $search = trim((string) $request->q);
        [$title, $subtitle] = match (true) {
            (bool) $category => [$category->name, $category->description ?: 'Every '.$category->name.' bargain in stock right now.'],
            $request->routeIs('deals') || $request->boolean('deals') => ['Price <em>drops</em>', 'Our biggest savings — grab them before they go.'],
            $request->routeIs('new') || $request->boolean('new') => ['New <em>in</em>', 'The latest stock to land in the store.'],
            $search !== '' => ['Results for “'.e($search).'”', number_format($products->total()).' bargains match your search.'],
            default => ['All <em>bargains</em>', 'Everything in our store, straight from eBay with buyer protection.'],
        };

        return view('store.shop', [
            'products' => $products,
            'category' => $category,
            'title' => $title,
            'subtitle' => $subtitle,
            'chips' => $this->chips($category),
            'sort' => $this->sortKey($request),
            'conditions' => Product::visible()->whereNotNull('condition')->distinct()->orderBy('condition')->pluck('condition'),
        ]);
    }

    public function product(Product $product)
    {
        abort_if($product->is_hidden, 404);
        $product->loadMissing('category');
        Product::whereKey($product->id)->increment('views');

        $related = Product::visible()->with('category')->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->inRandomOrder()->take(10)->get();
        if ($related->count() < 6) {
            $related = $related->merge(Product::visible()->with('category')->where('id', '!=', $product->id)
                ->whereNotIn('id', $related->pluck('id'))->inRandomOrder()->take(8 - $related->count())->get());
        }

        return view('store.product', compact('product', 'related'));
    }

    public function categories()
    {
        return view('store.categories');
    }

    public function about()
    {
        return view('store.about', ['testimonials' => $this->testimonials()]);
    }

    public function links()
    {
        return view('store.links', ['products' => Product::visible()->orderByDesc('is_featured')->latest('listed_at')->take(6)->get()]);
    }

    public function contact()
    {
        return view('store.contact');
    }

    // ------------------------------------------------------------ actions/api

    public function go(Product $product)
    {
        Product::whereKey($product->id)->increment('clicks');

        return redirect()->away($product->buy_url ?: setting('ebay_store_url'));
    }

    public function suggest(Request $request)
    {
        $search = trim((string) $request->q);
        if (mb_strlen($search) < 2) {
            return response()->json(['items' => [], 'total' => 0, 'url' => route('shop')]);
        }

        $q = Product::visible()->where(fn ($w) => $this->searchWords($w, $search));
        $total = (clone $q)->count();
        $items = $q->orderByDesc('is_featured')->latest('listed_at')->take(6)->get()->map(fn (Product $p) => [
            'title' => $p->title, 'price' => $p->money(), 'image' => Product::sized($p->images[0] ?? null, 300) ?? $p->thumb, 'url' => route('product', $p),
        ]);

        return response()->json(['items' => $items, 'total' => $total, 'url' => route('shop', ['q' => $search])]);
    }

    public function cards(Request $request)
    {
        $ids = collect(explode(',', (string) $request->ids))->map(fn ($i) => (int) $i)->filter()->unique()->take(40)->values();
        $products = Product::with('category')->where('is_hidden', false)->whereIn('id', $ids)->get()->sortBy(fn ($p) => $ids->search($p->id))->values();

        return view('partials.cards', ['products' => $products, 'layout' => $request->get('layout', 'grid')]);
    }

    public function sendContact(Request $request)
    {
        if (filled($request->website)) { // honeypot
            return back()->with('success', 'Thanks! We will get back to you soon.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'subject' => 'nullable|string|max:150',
            'message' => 'required|string|max:5000',
        ]);
        ContactMessage::create($data + ['ip' => $request->ip()]);

        return back()->with('success', 'Thanks! Your message has been sent — we usually reply within 24 hours.');
    }

    public function subscribe(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:150']);
        Subscriber::firstOrCreate(['email' => strtolower($data['email'])]);

        return back()->with('subscribed', 'You’re on the list! Watch your inbox for the best bargains.');
    }

    public function sitemap()
    {
        $products = Product::visible()->select('slug', 'updated_at')->latest('updated_at')->take(5000)->get();
        $categories = Category::where('is_visible', true)->select('slug', 'updated_at')->get();

        // Built as a plain string (not a Blade view) so the XML prolog can't clash with PHP tag parsing.
        $url = fn (string $loc, $lastmod = null, ?string $freq = null) => '<url><loc>'.e($loc).'</loc>'
            .($lastmod ? '<lastmod>'.$lastmod->toAtomString().'</lastmod>' : '')
            .($freq ? '<changefreq>'.$freq.'</changefreq>' : '').'</url>';

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach (['home' => 'daily', 'shop' => 'daily', 'deals' => 'daily', 'new' => 'daily', 'categories' => 'weekly', 'about' => 'monthly', 'contact' => 'monthly'] as $r => $freq) {
            $xml .= $url(route($r), null, $freq);
        }
        foreach ($categories as $c) {
            $xml .= $url(route('category', $c), $c->updated_at, 'daily');
        }
        foreach ($products as $p) {
            $xml .= $url(route('product', $p), $p->updated_at);
        }

        return response($xml.'</urlset>', 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    // --------------------------------------------------------------- helpers

    /** One query for the home "edit", the shop, categories, deals and new-in pages. */
    private function catalog(Request $request, ?Category $category = null): Builder
    {
        $q = Product::visible()->with('category');

        if ($category) {
            $q->where('category_id', $category->id);
        } elseif ($request->filled('category')) {
            $q->whereHas('category', fn ($c) => $c->where('slug', $request->category));
        }
        if ($search = trim((string) $request->q)) {
            $q->where(fn ($w) => $this->searchWords($w, $search));
        }
        if ($conditions = array_filter((array) $request->input('condition', []))) {
            $q->whereIn('condition', $conditions);
        }
        if (is_numeric($request->min)) {
            $q->where('price', '>=', (float) $request->min);
        }
        if (is_numeric($request->max)) {
            $q->where('price', '<=', (float) $request->max);
        }
        if ($request->routeIs('deals') || $request->boolean('deals')) {
            $q->whereNotNull('original_price')->whereColumn('original_price', '>', 'price');
        }
        if ($request->routeIs('new') || $request->boolean('new')) {
            $q->where('listed_at', '>=', now()->subDays(21));
        }

        match ($this->sortKey($request)) {
            'new' => $q->orderByDesc('listed_at')->orderByDesc('id'),
            'low' => $q->orderBy('price'),
            'high' => $q->orderByDesc('price'),
            'save' => $q->orderByRaw('CASE WHEN original_price > price THEN (original_price - price) / original_price ELSE 0 END DESC'),
            'pop' => $q->orderByDesc('quantity_sold')->orderByDesc('watch_count')->orderByDesc('views'),
            default => $q->orderByDesc('is_featured')
                ->orderByRaw('CASE WHEN original_price > price THEN 1 ELSE 0 END DESC')
                ->orderByDesc('listed_at')->orderByDesc('id'),
        };

        return $q;
    }

    private function sortKey(Request $request): string
    {
        $s = (string) $request->get('sort', $request->routeIs('new') ? 'new' : 'featured');

        return array_key_exists($s, self::SORTS) ? $s : 'featured';
    }

    private function searchWords($w, string $search): void
    {
        foreach (array_slice(preg_split('/\s+/', $search), 0, 6) as $word) {
            $w->where('title', 'like', '%'.$word.'%');
        }
    }

    private function dealsQuery(): Builder
    {
        return Product::visible()->whereNotNull('original_price')->whereColumn('original_price', '>', 'price')
            ->orderByRaw('(original_price - price) / original_price DESC');
    }

    /** JSON returned to the "load more" / chip / sort requests. */
    private function partial(LengthAwarePaginator $products)
    {
        $shown = min($products->total(), ($products->currentPage() - 1) * $products->perPage() + $products->count());
        $next = $products->hasMorePages() ? $products->appends(request()->except('partial', 'page'))->nextPageUrl() : null;

        return response()->json([
            'html' => view('partials.cards', ['products' => $products->getCollection(), 'layout' => 'grid'])->render(),
            'shown' => $shown,
            'total' => $products->total(),
            'next' => $next,
            'nextCount' => min($products->perPage(), $products->total() - $shown),
        ]);
    }

    /** Filter chips: quick filters + every category, with counts. */
    private function chips(?Category $active): Collection
    {
        $r = request();
        $base = Product::visible();
        $isPlain = ! $active && ! $r->boolean('deals') && ! $r->boolean('new') && ! $r->filled('max') && ! $r->routeIs('deals', 'new');

        $chips = collect([
            ['All', route('shop'), (clone $base)->count(), $isPlain],
            ['Price drops', route('shop', ['deals' => 1]), (clone $base)->whereNotNull('original_price')->whereColumn('original_price', '>', 'price')->count(), ! $active && ($r->boolean('deals') || $r->routeIs('deals'))],
            ['New in', route('shop', ['new' => 1]), (clone $base)->where('listed_at', '>=', now()->subDays(21))->count(), ! $active && ($r->boolean('new') || $r->routeIs('new'))],
            ['Under £15', route('shop', ['max' => 15]), (clone $base)->where('price', '<=', 15)->count(), ! $active && $r->get('max') == 15],
        ])->filter(fn ($c) => $c[2] > 0 || $c[3]);

        foreach (app('nav.categories') as $c) {
            $chips->push([$c->name, route('category', $c), $c->products_count, $active?->id === $c->id]);
        }

        return $chips->values();
    }

    /** "Name | Review" lines from Admin → Settings. */
    private function testimonials(): Collection
    {
        return collect(preg_split('/\R/', (string) setting('testimonials')))
            ->map(fn ($line) => array_map('trim', explode('|', $line, 2)))
            ->filter(fn ($p) => count($p) === 2 && $p[0] !== '' && $p[1] !== '')
            ->map(fn ($p) => ['name' => $p[0], 'text' => $p[1]])
            ->values();
    }
}

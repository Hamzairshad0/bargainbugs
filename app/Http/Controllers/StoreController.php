<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\Subscriber;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function home()
    {
        $featured = Product::visible()->where('is_featured', true)->latest('listed_at')->take(8)->get();
        $latest = Product::visible()->latest('listed_at')->latest('id')->take(8)->get();
        $deals = Product::visible()->whereNotNull('original_price')->whereColumn('original_price', '>', 'price')
            ->orderByRaw('(original_price - price) / original_price DESC')->take(4)->get();
        $popular = Product::visible()->orderByDesc('watch_count')->orderByDesc('views')->take(4)->get();

        if ($featured->isEmpty()) {
            $featured = $popular->isNotEmpty() ? Product::visible()->orderByDesc('watch_count')->orderByDesc('views')->take(8)->get() : $latest;
        }

        $heroProducts = $featured->take(3);
        $liveCount = Product::visible()->count();

        return view('store.home', compact('featured', 'latest', 'deals', 'popular', 'heroProducts', 'liveCount'));
    }

    public function shop(Request $request, ?Category $category = null)
    {
        $q = Product::visible()->with('category');

        if ($category) {
            $q->where('category_id', $category->id);
        } elseif ($request->filled('category')) {
            $q->whereHas('category', fn ($c) => $c->where('slug', $request->category));
        }

        if ($search = trim((string) $request->q)) {
            $q->where(function ($w) use ($search) {
                foreach (preg_split('/\s+/', $search) as $word) {
                    $w->where('title', 'like', '%'.$word.'%');
                }
            });
        }

        if ($request->filled('condition')) {
            $q->where('condition', $request->condition);
        }
        if (is_numeric($request->min)) {
            $q->where('price', '>=', (float) $request->min);
        }
        if (is_numeric($request->max)) {
            $q->where('price', '<=', (float) $request->max);
        }
        if ($request->boolean('deals') || $request->routeIs('deals')) {
            $q->whereNotNull('original_price')->whereColumn('original_price', '>', 'price');
        }

        $sort = $request->get('sort', $request->routeIs('new') ? 'newest' : 'featured');
        match ($sort) {
            'price_asc' => $q->orderBy('price'),
            'price_desc' => $q->orderByDesc('price'),
            'newest' => $q->orderByDesc('listed_at')->orderByDesc('id'),
            'popular' => $q->orderByDesc('watch_count')->orderByDesc('views'),
            default => $q->orderByDesc('is_featured')->orderByDesc('listed_at')->orderByDesc('id'),
        };

        $products = $q->paginate(24)->withQueryString();
        $conditions = Product::visible()->whereNotNull('condition')->distinct()->orderBy('condition')->pluck('condition');

        $title = match (true) {
            (bool) $category => $category->name,
            $request->routeIs('deals') => 'Hot Deals',
            $request->routeIs('new') => 'New In',
            filled($search) => 'Results for “'.$search.'”',
            default => 'Shop all bargains',
        };

        return view('store.shop', compact('products', 'conditions', 'category', 'title', 'sort'));
    }

    public function product(Product $product)
    {
        abort_if($product->is_hidden, 404);

        Product::whereKey($product->id)->increment('views');

        $related = Product::visible()->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->inRandomOrder()->take(4)->get();

        if ($related->count() < 4) {
            $related = $related->merge(Product::visible()->where('id', '!=', $product->id)
                ->whereNotIn('id', $related->pluck('id'))->inRandomOrder()->take(4 - $related->count())->get());
        }

        return view('store.product', compact('product', 'related'));
    }

    /** Count the click, then send the visitor to the eBay listing. */
    public function go(Product $product)
    {
        Product::whereKey($product->id)->increment('clicks');

        return redirect()->away($product->buy_url ?: setting('ebay_store_url'));
    }

    public function categories()
    {
        return view('store.categories');
    }

    public function about()
    {
        return view('store.about');
    }

    public function links()
    {
        $products = Product::visible()->orderByDesc('is_featured')->latest('listed_at')->take(6)->get();

        return view('store.links', compact('products'));
    }

    public function contact()
    {
        return view('store.contact');
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

        return back()->with('subscribed', 'You’re on the list! 🐞 Watch your inbox for the best bargains.');
    }

    public function sitemap()
    {
        $products = Product::visible()->select('slug', 'updated_at')->latest('updated_at')->take(5000)->get();
        $categories = Category::where('is_visible', true)->select('slug', 'updated_at')->get();

        // Built as a string (not Blade) so the "<?xml ... ?>" prolog can't clash with PHP tag parsing on the server.
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
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}

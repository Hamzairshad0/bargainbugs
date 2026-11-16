<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = Product::with('category');

        if ($s = trim((string) $request->q)) {
            $q->where(fn ($w) => $w->where('title', 'like', "%{$s}%")->orWhere('ebay_item_id', $s));
        }
        if ($request->filled('status')) {
            match ($request->status) {
                'hidden' => $q->where('is_hidden', true),
                'featured' => $q->where('is_featured', true),
                default => $q->where('status', $request->status),
            };
        }
        if ($request->filled('source')) {
            $q->where('source', $request->source);
        }
        if ($request->filled('category')) {
            $q->where('category_id', $request->category);
        }

        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'views' => $q->orderByDesc('views'),
            'clicks' => $q->orderByDesc('clicks'),
            'price' => $q->orderByDesc('price'),
            'title' => $q->orderBy('title'),
            default => $q->latest('id'),
        };

        return view('admin.products.index', [
            'products' => $q->paginate(30)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
            'counts' => [
                'all' => Product::count(),
                'active' => Product::where('status', 'active')->count(),
                'sold' => Product::where('status', 'sold')->count(),
                'ended' => Product::where('status', 'ended')->count(),
                'hidden' => Product::where('is_hidden', true)->count(),
                'featured' => Product::where('is_featured', true)->count(),
            ],
        ]);
    }

    public function create()
    {
        return view('admin.products.form', ['product' => new Product(['status' => 'active', 'quantity' => 1, 'currency' => 'GBP']), 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $product = new Product(['source' => 'manual']);
        $this->save($request, $product);

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', ['product' => $product, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(Request $request, Product $product)
    {
        $this->save($request, $product);

        return back()->with('success', 'Product saved.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        Cache::forget('nav.categories');

        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    public function toggle(Product $product, string $field)
    {
        abort_unless(in_array($field, ['is_featured', 'is_hidden'], true), 404);
        $product->update([$field => ! $product->$field]);
        Cache::forget('nav.categories');

        return back()->with('success', 'Updated.');
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'action' => 'required|in:feature,unfeature,hide,show,delete,category',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $q = Product::whereIn('id', $data['ids']);
        $n = count($data['ids']);

        match ($data['action']) {
            'feature' => $q->update(['is_featured' => true]),
            'unfeature' => $q->update(['is_featured' => false]),
            'hide' => $q->update(['is_hidden' => true]),
            'show' => $q->update(['is_hidden' => false]),
            'category' => $q->update(['category_id' => $data['category_id'], 'lock_content' => true]),
            'delete' => $q->delete(),
        };
        Cache::forget('nav.categories');

        return back()->with('success', "{$n} product(s) updated.");
    }

    private function save(Request $request, Product $product): void
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'condition' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'ebay_url' => 'nullable|url|max:1024',
            'status' => 'required|in:active,sold,ended',
            'image_urls' => 'nullable|string',
            'uploads' => 'nullable|array|max:12',
            'uploads.*' => 'image|max:5120',
            'slug' => 'nullable|alpha_dash|max:255',
        ]);

        $images = collect(preg_split('/\R+/', (string) ($data['image_urls'] ?? '')))
            ->map(fn ($u) => trim($u))->filter(fn ($u) => filter_var($u, FILTER_VALIDATE_URL))->values()->all();

        foreach ($request->file('uploads', []) as $file) {
            try {
                $disk = config('filesystems.upload_disk', 'public');
                $path = $file->store('products', $disk);
                $images[] = Storage::disk($disk)->url($path);
            } catch (Throwable $e) {
                session()->flash('error', 'Image upload failed — on Vercel set UPLOAD_DISK to an S3/R2 disk, or paste image URLs instead. ('.$e->getMessage().')');
            }
        }

        $product->fill([
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'description' => HtmlSanitizer::clean($data['description'] ?? ''),
            'price' => $data['price'],
            'original_price' => $data['original_price'] ?? null,
            'quantity' => $data['quantity'],
            'condition' => $data['condition'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'ebay_url' => $data['ebay_url'] ?? null,
            'status' => $data['status'],
            'images' => $images,
            'is_featured' => $request->boolean('is_featured'),
            'is_hidden' => $request->boolean('is_hidden'),
            'lock_content' => $request->boolean('lock_content'),
        ]);

        if (! empty($data['slug']) && $data['slug'] !== $product->slug) {
            $product->slug = Product::uniqueSlug($data['slug'], $product->id);
        }

        if (! $product->exists) {
            $product->listed_at = now();
        }

        $product->save();
        Cache::forget('nav.categories');
    }
}

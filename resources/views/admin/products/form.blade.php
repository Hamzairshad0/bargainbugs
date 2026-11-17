@extends('layouts.admin')

@section('title', $product->exists ? 'Edit product' : 'Add product')
@section('actions')
    @if ($product->exists)
        <a href="{{ route('product', $product) }}" target="_blank" class="btn-ghost btn-sm">View on site ↗</a>
    @endif
@endsection

@section('content')
    @php $isEbay = $product->source === 'ebay'; @endphp

    @if ($isEbay)
        <div class="mb-6 rounded-2xl border border-ink/15 bg-white p-4 text-sm">
            🔄 <b>This product is synced from eBay</b> (item #{{ $product->ebay_item_id }}, last synced {{ $product->ebay_synced_at?->diffForHumans() ?? 'never' }}).
            Price, stock and status always follow eBay. Tick <b>“Lock my edits”</b> below if you want to keep your own title, description, photos and category.
        </div>
    @endif

    <form action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" method="post" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-3">
        @csrf
        @if ($product->exists) @method('PUT') @endif

        <div class="space-y-6 xl:col-span-2">
            <div class="card-pop grid gap-5 p-6">
                <label class="grid gap-2 text-sm font-bold">Title
                    <input name="title" value="{{ old('title', $product->title) }}" required class="input">
                </label>
                <label class="grid gap-2 text-sm font-bold">Subtitle <span class="font-normal text-ink/50">(optional)</span>
                    <input name="subtitle" value="{{ old('subtitle', $product->subtitle) }}" class="input">
                </label>
                <label class="grid gap-2 text-sm font-bold">Description <span class="font-normal text-ink/50">(basic HTML allowed: &lt;p&gt; &lt;b&gt; &lt;ul&gt; &lt;li&gt;…)</span>
                    <textarea name="description" rows="12" class="input font-mono !text-xs">{{ old('description', $product->description) }}</textarea>
                </label>
            </div>

            <div class="card-pop grid gap-4 p-6">
                <h2 class="font-display text-lg font-semibold">Photos</h2>
                <label class="grid gap-2 text-sm font-bold">Image URLs <span class="font-normal text-ink/50">(one per line — first is the main photo)</span>
                    <textarea name="image_urls" rows="5" data-images-input="#img-preview" class="input font-mono !text-xs">{{ old('image_urls', implode("\n", $product->images ?? [])) }}</textarea>
                </label>
                <div id="img-preview" class="flex flex-wrap gap-2"></div>
                <label class="grid gap-2 text-sm font-bold">…or upload photos
                    <input type="file" name="uploads[]" multiple accept="image/*" class="input">
                    <span class="text-xs font-normal text-ink/50">Uploads need a storage disk (works locally; on Vercel set UPLOAD_DISK=s3 with Cloudflare R2/S3 — see README).</span>
                </label>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card-pop grid gap-4 p-6">
                <h2 class="font-display text-lg font-semibold">Pricing & stock</h2>
                <div class="grid grid-cols-2 gap-4">
                    <label class="grid gap-2 text-sm font-bold">Price (£)
                        <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" required class="input">
                    </label>
                    <label class="grid gap-2 text-sm font-bold">Was (£)
                        <input type="number" step="0.01" min="0" name="original_price" value="{{ old('original_price', $product->original_price) }}" class="input" placeholder="optional">
                    </label>
                </div>
                <label class="grid gap-2 text-sm font-bold">Quantity available
                    <input type="number" min="0" name="quantity" value="{{ old('quantity', $product->quantity) }}" required class="input">
                </label>
                <label class="grid gap-2 text-sm font-bold">Status
                    <select name="status" class="input">
                        @foreach (['active' => 'Active (for sale)', 'sold' => 'Sold out', 'ended' => 'Ended'] as $k => $l)
                            <option value="{{ $k }}" @selected(old('status', $product->status) === $k)>{{ $l }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="grid gap-2 text-sm font-bold">Condition
                    <input name="condition" value="{{ old('condition', $product->condition) }}" list="conditions" class="input" placeholder="e.g. New">
                    <datalist id="conditions"><option>New</option><option>New with tags</option><option>New other (see details)</option><option>Opened – never used</option><option>Used</option><option>Refurbished</option></datalist>
                </label>
            </div>

            <div class="card-pop grid gap-4 p-6">
                <h2 class="font-display text-lg font-semibold">Organise</h2>
                <label class="grid gap-2 text-sm font-bold">Category
                    <select name="category_id" class="input">
                        <option value="">— Uncategorised —</option>
                        @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->icon }} {{ $c->name }}</option>@endforeach
                    </select>
                </label>
                <label class="grid gap-2 text-sm font-bold">eBay listing URL <span class="font-normal text-ink/50">(“Buy” button goes here)</span>
                    <input type="url" name="ebay_url" value="{{ old('ebay_url', $product->ebay_url) }}" class="input" placeholder="https://www.ebay.co.uk/itm/...">
                </label>
                @if ($product->exists)
                    <label class="grid gap-2 text-sm font-bold">URL slug
                        <input name="slug" value="{{ old('slug', $product->slug) }}" class="input font-mono !text-xs">
                    </label>
                @endif
                <label class="flex items-center gap-3 text-sm font-bold"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured)) class="h-5 w-5 accent-bug-500"> ⭐ Feature on home page</label>
                <label class="flex items-center gap-3 text-sm font-bold"><input type="checkbox" name="is_hidden" value="1" @checked(old('is_hidden', $product->is_hidden)) class="h-5 w-5 accent-bug-500"> 🙈 Hide from website</label>
                @if ($isEbay)
                    <label class="flex items-center gap-3 text-sm font-bold"><input type="checkbox" name="lock_content" value="1" @checked(old('lock_content', $product->lock_content)) class="h-5 w-5 accent-bug-500"> 🔒 Lock my edits (sync won’t overwrite title, text, photos, category)</label>
                @endif
            </div>

            <div class="flex gap-3">
                <button class="btn-primary flex-1">{{ $product->exists ? 'Save changes' : 'Create product' }}</button>
                <a href="{{ route('admin.products.index') }}" class="btn-ghost">Cancel</a>
            </div>

            @if ($product->exists)
                <div class="rounded-2xl border border-ink/10 bg-white p-4 text-xs text-ink/60">
                    {{ number_format($product->views) }} views · {{ number_format($product->clicks) }} eBay clicks · {{ $product->watch_count }} watchers on eBay<br>
                    Added {{ $product->created_at->format('d M Y H:i') }}
                </div>
            @endif
        </div>
    </form>

    @if ($product->exists)
        <form action="{{ route('admin.products.destroy', $product) }}" method="post" class="mt-6" data-confirm="Delete this product from the website? (It stays on eBay. If it’s still live on eBay it will come back on the next sync — use Hide instead.)">
            @csrf @method('DELETE')
            <button class="text-sm font-bold text-bug-600 hover:underline">Delete product</button>
        </form>
    @endif
@endsection

@extends('layouts.store')

@section('title', $product->title)
@section('description', $product->money().' — '.($product->excerpt ?: $product->title))
@section('og_image', $product->image)
@section('og_type', 'product')

@push('meta')
    <meta property="product:price:amount" content="{{ $product->price }}">
    <meta property="product:price:currency" content="{{ $product->currency }}">
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->title,
        'image' => $product->gallery,
        'description' => $product->excerpt,
        'sku' => $product->ebay_item_id ?: (string) $product->id,
        'itemCondition' => str_contains(strtolower((string) $product->condition), 'new') ? 'https://schema.org/NewCondition' : 'https://schema.org/UsedCondition',
        'offers' => [
            '@type' => 'Offer',
            'price' => (string) $product->price,
            'priceCurrency' => $product->currency,
            'availability' => $product->status === 'active' ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut',
            'url' => route('product', $product),
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
    @php
        $shareUrl = route('product', $product);
        $shareText = $product->title.' — only '.$product->money().' at '.setting('site_name');
        $available = $product->status === 'active' && $product->quantity > 0;
    @endphp

    <section class="container-x py-8 lg:py-12">
        <nav class="mb-6 truncate text-sm font-semibold text-ink/50" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-ink">Home</a> <span class="mx-1.5">/</span>
            @if ($product->category)
                <a href="{{ route('category', $product->category) }}" class="hover:text-ink">{{ $product->category->name }}</a> <span class="mx-1.5">/</span>
            @else
                <a href="{{ route('shop') }}" class="hover:text-ink">Shop</a> <span class="mx-1.5">/</span>
            @endif
            <span class="text-ink">{{ Str::limit($product->title, 60) }}</span>
        </nav>

        <div class="grid gap-8 lg:grid-cols-2 lg:gap-14">
            {{-- Gallery --}}
            <div data-gallery class="lg:sticky lg:top-28 lg:self-start">
                <div class="relative overflow-hidden rounded-[2rem] border-2 border-ink bg-white shadow-[6px_6px_0_0_var(--color-ink)]">
                    <img data-gallery-main src="{{ $product->gallery[0] }}" alt="{{ $product->title }}" class="aspect-square w-full object-contain" onerror="this.src='{{ asset('images/placeholder.svg') }}'">
                    <div class="absolute left-4 top-4 flex flex-col items-start gap-2">
                        @if ($product->on_sale)<span class="badge border-2 border-ink bg-bug-500 !text-sm text-white">Save {{ $product->discount_percent }}%</span>@endif
                        @if ($product->is_new)<span class="badge border-2 border-ink bg-sun !text-sm">Just in</span>@endif
                    </div>
                    @if (count($product->gallery) > 1)
                        <button data-gallery-prev type="button" aria-label="Previous image" class="absolute left-3 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full border-2 border-ink bg-white/90 hover:bg-sun">@include('partials.icon', ['name' => 'left', 'class' => 'h-5 w-5'])</button>
                        <button data-gallery-next type="button" aria-label="Next image" class="absolute right-3 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full border-2 border-ink bg-white/90 hover:bg-sun">@include('partials.icon', ['name' => 'right', 'class' => 'h-5 w-5'])</button>
                        <span data-gallery-count class="absolute bottom-4 right-4 rounded-full bg-ink/80 px-3 py-1 text-xs font-bold text-white">1 / {{ count($product->gallery) }}</span>
                    @endif
                </div>
                @if (count($product->gallery) > 1)
                    <div class="mt-4 flex gap-3 overflow-x-auto pb-2 scrollbar-none">
                        @foreach ($product->gallery as $i => $img)
                            <button type="button" data-gallery-thumb data-src="{{ $img }}" class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl border-2 bg-white transition {{ $i === 0 ? '!border-bug-500' : 'border-ink/15 hover:border-ink' }}" aria-label="Show image {{ $i + 1 }}">
                                <img src="{{ $img }}" alt="" loading="lazy" class="h-full w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Details --}}
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    @if ($product->condition)<span class="chip">{{ $product->condition }}</span>@endif
                    @if ($product->category)<a href="{{ route('category', $product->category) }}" class="chip hover:bg-sun">{{ $product->category->icon }} {{ $product->category->name }}</a>@endif
                    @if ($product->watch_count > 2)<span class="chip !border-bug-500 !text-bug-600">👀 {{ $product->watch_count }} watching</span>@endif
                </div>

                <h1 class="mt-4 font-display text-3xl font-extrabold leading-tight sm:text-4xl">{{ $product->title }}</h1>
                @if ($product->subtitle)<p class="mt-2 text-lg text-ink/60">{{ $product->subtitle }}</p>@endif

                <div class="mt-6 flex flex-wrap items-end gap-3">
                    <span class="font-display text-5xl font-extrabold">{{ $product->money() }}</span>
                    @if ($product->on_sale)
                        <span class="pb-1.5 text-xl font-semibold text-ink/40 line-through">{{ $product->money($product->original_price) }}</span>
                        <span class="mb-2 badge bg-sun border-2 border-ink">You save {{ $product->money($product->original_price - $product->price) }}</span>
                    @endif
                </div>

                <p class="mt-3 text-sm font-bold {{ $available ? 'text-leaf' : 'text-bug-600' }}">
                    @if ($available)
                        ● In stock{{ $product->quantity <= 5 ? ' — only '.$product->quantity.' left!' : '' }}
                    @elseif ($product->status === 'sold')
                        ● Sold out
                    @else
                        ● No longer available
                    @endif
                </p>

                <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                    @if ($available && $product->buy_url)
                        <a href="{{ route('go', $product) }}" target="_blank" rel="noopener nofollow" class="btn-primary flex-1 !py-4 !text-base">
                            @include('partials.icon', ['name' => 'bag', 'class' => 'h-5 w-5']) Buy now on eBay
                        </a>
                    @else
                        <a href="{{ route('shop') }}" class="btn-primary flex-1 !py-4 !text-base">See similar bargains</a>
                    @endif
                    <button type="button" data-share data-title="{{ $shareText }}" data-url="{{ $shareUrl }}" class="btn-ghost !py-4">
                        @include('partials.icon', ['name' => 'share', 'class' => 'h-5 w-5']) <span data-label>Share</span>
                    </button>
                </div>

                {{-- Social share --}}
                <div class="mt-5 flex flex-wrap items-center gap-2">
                    <span class="mr-1 text-sm font-bold text-ink/50">Share with a friend:</span>
                    <a href="https://wa.me/?text={{ urlencode($shareText.' '.$shareUrl) }}" target="_blank" rel="noopener" class="grid h-10 w-10 place-items-center rounded-full border-2 border-ink bg-[#25D366] text-white transition hover:-translate-y-0.5" aria-label="Share on WhatsApp">@include('partials.icon', ['name' => 'whatsapp', 'class' => 'h-5 w-5'])</a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="grid h-10 w-10 place-items-center rounded-full border-2 border-ink bg-[#1877F2] text-white transition hover:-translate-y-0.5" aria-label="Share on Facebook">@include('partials.icon', ['name' => 'facebook', 'class' => 'h-5 w-5'])</a>
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode($shareText) }}&url={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="grid h-10 w-10 place-items-center rounded-full border-2 border-ink bg-ink text-white transition hover:-translate-y-0.5" aria-label="Share on X">@include('partials.icon', ['name' => 'x', 'class' => 'h-4 w-4'])</a>
                    <a href="https://pinterest.com/pin/create/button/?url={{ urlencode($shareUrl) }}&media={{ urlencode($product->image) }}&description={{ urlencode($shareText) }}" target="_blank" rel="noopener" class="grid h-10 w-10 place-items-center rounded-full border-2 border-ink bg-[#E60023] text-sm font-black text-white transition hover:-translate-y-0.5" aria-label="Share on Pinterest">P</a>
                    <button type="button" data-copy="{{ $shareUrl }}" class="grid h-10 w-10 place-items-center rounded-full border-2 border-ink bg-white transition hover:-translate-y-0.5" aria-label="Copy link">@include('partials.icon', ['name' => 'link', 'class' => 'h-4 w-4'])</button>
                </div>

                {{-- Trust --}}
                <ul class="mt-8 grid gap-3 rounded-3xl border-2 border-ink bg-white p-5 sm:grid-cols-2">
                    <li class="flex items-start gap-3 text-sm"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-cream">@include('partials.icon', ['name' => 'shield', 'class' => 'h-5 w-5 text-leaf'])</span><span><b>eBay Money Back Guarantee</b><br><span class="text-ink/60">Get the item you ordered or your money back.</span></span></li>
                    <li class="flex items-start gap-3 text-sm"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-cream">@include('partials.icon', ['name' => 'lock', 'class' => 'h-5 w-5 text-leaf'])</span><span><b>Secure checkout</b><br><span class="text-ink/60">PayPal, cards, Apple Pay & Google Pay.</span></span></li>
                    <li class="flex items-start gap-3 text-sm"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-cream">@include('partials.icon', ['name' => 'truck', 'class' => 'h-5 w-5 text-leaf'])</span><span><b>Fast UK dispatch</b><br><span class="text-ink/60">Postage options shown on eBay.</span></span></li>
                    <li class="flex items-start gap-3 text-sm"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-cream">@include('partials.icon', ['name' => 'mail', 'class' => 'h-5 w-5 text-leaf'])</span><span><b>Questions?</b><br><a href="{{ route('contact') }}" class="text-ink/60 underline hover:text-ink">Message our team</a></span></li>
                </ul>

                {{-- Description --}}
                @if ($product->description)
                    <div class="mt-8">
                        <h2 class="font-display text-2xl font-extrabold">About this item</h2>
                        <div class="prose-bb mt-4 rounded-3xl bg-white p-6">{!! $product->description !!}</div>
                    </div>
                @endif

                @if ($product->ebay_item_id)
                    <p class="mt-4 text-xs text-ink/40">eBay item number: {{ $product->ebay_item_id }}</p>
                @endif
            </div>
        </div>
    </section>

    {{-- Mobile sticky buy bar --}}
    @if ($available && $product->buy_url)
        @push('scripts')<div class="h-20 lg:hidden" aria-hidden="true"></div>@endpush
        <div class="fixed inset-x-0 bottom-0 z-40 border-t-2 border-ink bg-white p-3 lg:hidden">
            <div class="flex items-center gap-3">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs font-semibold text-ink/60">{{ $product->title }}</p>
                    <p class="font-display text-xl font-extrabold">{{ $product->money() }}</p>
                </div>
                <a href="{{ route('go', $product) }}" target="_blank" rel="noopener nofollow" class="btn-primary">Buy on eBay</a>
            </div>
        </div>
    @endif

    @if ($related->isNotEmpty())
        <section class="border-t-2 border-ink bg-white py-14">
            <div class="container-x">
                <h2 class="font-display text-3xl font-extrabold">You might also like</h2>
                <div class="mt-8 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                    @foreach ($related as $r)
                        <x-product-card :product="$r" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection

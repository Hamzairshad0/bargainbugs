@extends('layouts.store')

@php
    $p = $product;
    $available = $p->status === 'active' && $p->quantity > 0;
    $gallery = $p->gallery;
    $shareUrl = route('product', $p);
    $shareText = $p->title.' — only '.$p->money().' at '.setting('site_name');
@endphp

@section('title', $p->title)
@section('description', $p->money().' — '.($p->excerpt ?: $p->title))
@section('og_image', \App\Models\Product::sized($gallery[0], 960))
@section('og_type', 'product')
@section('body_class', $available && $p->buy_url ? 'has-sticky-buy' : '')

@push('preload')<link rel="preload" as="image" href="{{ \App\Models\Product::sized($gallery[0], 960) }}" fetchpriority="high">@endpush
@push('meta')
    <meta property="product:price:amount" content="{{ $p->price }}">
    <meta property="product:price:currency" content="{{ $p->currency }}">
    <script type="application/ld+json">{!! json_encode([
        '@'.'context' => 'https://schema.org', '@type' => 'Product', 'name' => $p->title, 'image' => $gallery,
        'description' => $p->excerpt, 'sku' => $p->ebay_item_id ?: (string) $p->id,
        'offers' => ['@type' => 'Offer', 'price' => (string) $p->price, 'priceCurrency' => $p->currency,
            'availability' => $available ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut', 'url' => $shareUrl],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
    <div data-recent-id="{{ $p->id }}" hidden></div>
    <section class="page-head" style="padding-bottom:28px">
        <div class="wrap">
            <nav class="crumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span>
                @if ($p->category)<a href="{{ route('category', $p->category) }}">{{ $p->category->name }}</a>@else<a href="{{ route('shop') }}">Shop</a>@endif
                <span>/</span><span>{{ Str::limit($p->title, 40) }}</span></nav>
        </div>
    </section>

    <section class="wrap pgrid">
        {{-- Gallery --}}
        <div class="gallery">
            <div class="swiper gmain" data-gallery-main style="position:relative">
                <div class="swiper-wrapper">
                    @foreach ($gallery as $i => $img)
                        <div class="swiper-slide"><img src="{{ \App\Models\Product::sized($img, 960) }}" alt="{{ $p->title }}{{ $i ? ' — photo '.($i + 1) : '' }}" width="800" height="800" @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif onerror="this.onerror=null;this.src='{{ asset('images/placeholder.svg') }}'"></div>
                    @endforeach
                </div>
                @if (count($gallery) > 1)
                    <button class="round gnav prev" data-gallery-prev aria-label="Previous photo">←</button>
                    <button class="round gnav next" data-gallery-next aria-label="Next photo">→</button>
                    <span class="gcount" data-gallery-count>1 / {{ count($gallery) }}</span>
                @endif
                @if ($p->on_sale)<span class="badge" style="top:16px;left:16px;z-index:3">-{{ $p->discount_percent }}%</span>@endif
            </div>
            @if (count($gallery) > 1)
                <div class="swiper gthumbs" data-gallery-thumbs>
                    <div class="swiper-wrapper">
                        @foreach ($gallery as $img)
                            <div class="swiper-slide"><img src="{{ \App\Models\Product::sized($img, 300) }}" alt="" loading="lazy" width="84" height="84"></div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Info --}}
        <div class="pinfo">
            <p class="c">{{ $p->category?->name ?? 'Bargain' }}@if ($p->condition) · {{ $p->condition }}@endif</p>
            <h1>{{ $p->title }}</h1>
            @if ($p->subtitle)<p style="margin-top:10px;color:var(--color-muted)">{{ $p->subtitle }}</p>@endif

            <div class="price-row">
                <b>{{ $p->money() }}</b>
                @if ($p->on_sale)<s>{{ $p->money($p->original_price) }}</s><span class="save">You save {{ $p->money($p->original_price - $p->price) }}</span>@endif
            </div>
            <p class="stock {{ $available ? 'in' : 'out' }}">
                @if ($available) ● In stock{{ $p->quantity <= 5 ? ' — only '.$p->quantity.' left' : '' }} @elseif ($p->status === 'sold') ● Sold out @else ● No longer available @endif
            </p>

            <div class="buy-row">
                @if ($available && $p->buy_url)
                    <a class="btn btn-dark" href="{{ route('go', $p) }}" target="_blank" rel="noopener nofollow">Buy now on eBay <span class="arr">→</span></a>
                @else
                    <a class="btn btn-dark" href="{{ route('shop') }}">See similar bargains</a>
                @endif
                <button type="button" class="round" style="width:56px;height:56px" :class="$store.wish.has({{ $p->id }}) && 'on'" @click="$store.wish.toggle({{ $p->id }})" aria-label="Save to wishlist">
                    <svg class="icon" viewBox="0 0 24 24" :style="$store.wish.has({{ $p->id }}) && 'fill:var(--color-tang);color:var(--color-tang)'"><path d="M12 20s-7-4.4-9-9a5 5 0 0 1 9-3 5 5 0 0 1 9 3c-2 4.6-9 9-9 9z"/></svg>
                </button>
            </div>

            <div class="assure">
                <div>@include('partials.icon', ['name' => 'shield', 'class' => 'icon']) <span><b>Money Back Guarantee</b>Get the item you ordered or your money back.</span></div>
                <div>@include('partials.icon', ['name' => 'truck', 'class' => 'icon']) <span><b>Fast UK dispatch</b>Postage &amp; delivery dates shown on eBay.</span></div>
            </div>

            <div class="share">
                <span>Share:</span>
                <a class="round" style="width:40px;height:40px" href="https://wa.me/?text={{ urlencode($shareText.' '.$shareUrl) }}" target="_blank" rel="noopener" aria-label="Share on WhatsApp">@include('partials.icon', ['name' => 'whatsapp', 'class' => 'icon icon-sm'])</a>
                <a class="round" style="width:40px;height:40px" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" aria-label="Share on Facebook">@include('partials.icon', ['name' => 'facebook', 'class' => 'icon icon-sm'])</a>
                <a class="round" style="width:40px;height:40px" href="https://twitter.com/intent/tweet?text={{ urlencode($shareText) }}&url={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" aria-label="Share on X">@include('partials.icon', ['name' => 'x', 'class' => 'icon icon-sm'])</a>
                <button type="button" class="round" style="width:40px;height:40px" data-share data-title="{{ $shareText }}" data-url="{{ $shareUrl }}" aria-label="Copy or share link">@include('partials.icon', ['name' => 'link', 'class' => 'icon icon-sm'])</button>
            </div>

            <div class="acc">
                @if ($p->description)
                    <details open><summary>About this item</summary><div class="body prose-bb">{!! $p->description !!}</div></details>
                @endif
                <details><summary>Details</summary><div class="body"><ul class="facts">
                    <li><span>Condition</span><span>{{ $p->condition ?? '—' }}</span></li>
                    <li><span>Category</span><span>{{ $p->category?->name ?? '—' }}</span></li>
                    @if ($p->quantity_sold > 0)<li><span>Sold on eBay</span><span>{{ number_format($p->quantity_sold) }}</span></li>@endif
                    @if ($p->ebay_item_id)<li><span>eBay item number</span><span>{{ $p->ebay_item_id }}</span></li>@endif
                </ul></div></details>
                <details><summary>Delivery &amp; returns</summary><div class="body prose-bb"><p>Your order is placed on eBay, so postage options, delivery dates and the returns policy are shown on the listing before you pay. Every purchase is covered by the eBay Money Back Guarantee.</p></div></details>
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="pad" style="padding-top:20px" data-carousel-wrap>
            <div class="wrap">
                <div class="sh"><div><small>You might also like</small><h2 data-split>More <em data-keep>bargains</em></h2></div>
                    <div class="arrows"><button class="round" data-prev aria-label="Previous">←</button><button class="round" data-next aria-label="Next">→</button></div></div>
                <div class="swiper" data-carousel style="overflow:visible">
                    <div class="swiper-wrapper">
                        @foreach ($related as $r)
                            <div class="swiper-slide" style="width:min(290px,70vw)"><x-product-card :product="$r" /></div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($available && $p->buy_url)
        <div class="sticky-buy">
            <div style="flex:1;min-width:0"><p style="font-size:12px;color:var(--color-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $p->title }}</p><b style="font-size:20px">{{ $p->money() }}</b></div>
            <a class="btn btn-dark btn-sm" href="{{ route('go', $p) }}" target="_blank" rel="noopener nofollow">Buy on eBay</a>
        </div>
    @endif
@endsection

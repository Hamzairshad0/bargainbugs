@extends('layouts.store')

@php $first = $hero->first(); @endphp
@if ($first)
    @push('preload')<link rel="preload" as="image" href="{{ \App\Models\Product::sized($first->images[0] ?? null, 960) }}" fetchpriority="high">@endpush
@endif

@section('content')
    {{-- ============================================================ HERO --}}
    <section class="hero">
        <div class="hero-word" data-speed="-0.4" aria-hidden="true">Bargain</div>
        <div class="wrap hero-grid">
            <div>
                <p class="kicker" data-reveal>UK online e-store — secure checkout via eBay</p>
                <h1 data-split data-now>Big bargains, <em data-keep>beautifully</em> small prices.</h1>
                <p class="lede" data-reveal>{{ setting('hero_subtitle') }}</p>
                <div class="cta" data-reveal>
                    <a class="btn btn-dark" href="#edit" data-magnetic>{{ setting('hero_cta') }} <span class="arr">→</span></a>
                    <a class="ulink" href="{{ setting('ebay_store_url') }}" target="_blank" rel="noopener">Visit our eBay store</a>
                </div>
                @if ($stats->isNotEmpty())
                    <div class="meta" data-reveal>
                        @foreach ($stats as $s)
                            <div><b><span data-count="{{ $s['value'] }}" data-dec="{{ $s['dec'] ?? 0 }}">{{ number_format($s['value'], $s['dec'] ?? 0) }}</span>{{ $s['suffix'] }}</b><span>{{ $s['label'] }}</span></div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="stage">
                <div class="ring r2"></div><div class="ring"></div><div class="disc"></div>
                @if ($hero->isNotEmpty())
                    <div class="swiper hero-swiper" data-hero-swiper>
                        <div class="swiper-wrapper">
                            @foreach ($hero as $i => $p)
                                <div class="swiper-slide" data-cat="{{ $p->category?->name ?? 'Bargain' }}" data-price="{{ $p->money() }}" data-was="{{ $p->on_sale ? $p->money($p->original_price) : '' }}" data-off="{{ $p->on_sale ? '-'.$p->discount_percent.'%' : '' }}">
                                    <a href="{{ route('product', $p) }}" style="display:contents" aria-label="{{ $p->title }}">
                                        <img class="pimg" src="{{ \App\Models\Product::sized($p->images[0] ?? null, 960) ?? $p->thumb }}" alt="{{ $p->title }}" width="600" height="600" @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="tag t1"><span data-tag-cat>{{ $first->category?->name }}</span><b data-tag-price>{{ $first->money() }}</b><s data-tag-was>{{ $first->on_sale ? $first->money($first->original_price) : '' }}</s> <span class="off" data-tag-off @if (! $first->on_sale) hidden @endif>{{ $first->on_sale ? '-'.$first->discount_percent.'%' : '' }}</span></div>
                    <div class="tag t2">🔒 Buyer protection <span style="color:var(--color-muted)">on every order</span></div>
                    @if ($hero->count() > 1)
                        <div class="hnav">
                            <button class="round" data-hero-prev aria-label="Previous">←</button>
                            <span data-hero-count>01</span><span class="bar"><i data-hero-bar></i></span><span>{{ str_pad($hero->count(), 2, '0', STR_PAD_LEFT) }}</span>
                            <button class="round" data-hero-next aria-label="Next">→</button>
                        </div>
                    @endif
                @else
                    <div class="hero-brand"><img src="{{ asset('images/logo.png') }}" alt="{{ setting('site_name') }}"></div>
                    <div class="tag t2">✨ New stock <span style="color:var(--color-muted)">every week</span></div>
                @endif
            </div>
        </div>
    </section>

    <div class="strip" aria-hidden="true"><div class="marquee">
        @for ($i = 0; $i < 2; $i++)
            <span>Secure checkout</span><span>Fast UK dispatch</span><span>New stock every week</span><span>Money back guarantee</span><span>Hand-picked bargains</span>
        @endfor
    </div></div>

    {{-- ====================================================== CATEGORIES --}}
    @if ($navCategories->isNotEmpty())
        <section class="pad" data-carousel-wrap>
            <div class="wrap">
                <div class="sh">
                    <div><small>01 — Categories</small><h2 data-split>Find your next <em data-keep>favourite</em> thing</h2></div>
                    <div class="arrows"><button class="round" data-prev aria-label="Previous">←</button><button class="round" data-next aria-label="Next">→</button></div>
                </div>
                <div class="swiper cat-swiper" data-carousel>
                    <div class="swiper-wrapper">
                        @foreach ($navCategories as $c)
                            <div class="swiper-slide" data-reveal>
                                <a class="cat" href="{{ route('category', $c) }}">
                                    <div class="im">
                                        @if ($c->cover)<img src="{{ $c->cover }}" alt="" loading="lazy" width="300" height="400">@else<span class="emoji">{{ $c->icon ?: '🛍️' }}</span>@endif
                                        <span class="lbl">{{ $c->name }}<span class="cnt">{{ $c->products_count }} {{ Str::plural('item', $c->products_count) }}</span></span>
                                        <span class="go">→</span>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ======================================================== THE EDIT --}}
    <section class="pad" id="edit" @if ($navCategories->isNotEmpty()) style="padding-top:0" @endif>
        <div class="wrap" data-catalog data-base="{{ route('shop') }}">
            <div class="sh">
                <div><small>02 — The edit</small><h2 data-split>This week’s <em data-keep>best</em> finds</h2></div>
                <p class="sub" data-reveal><b>{{ number_format($products->total()) }}</b> bargains in stock — updated from our eBay store every day.</p>
            </div>
            @include('partials.catalog', ['products' => $products, 'chips' => $chips, 'sort' => 'featured'])
        </div>
    </section>

    {{-- ========================================================== STORY --}}
    <section class="story">
        <div class="wrap">
            <div class="sticky">
                <h2 data-split>Bought on eBay. <em data-keep>Protected</em> by eBay.</h2>
                <p class="l" data-reveal>Everything is listed on our eBay store, so every purchase comes with secure payments, tracked delivery and the Money Back Guarantee.</p>
                <ul class="feat">
                    <li data-reveal><i>i.</i><div><b>Pay your way</b><span>PayPal, cards, Apple Pay &amp; Google Pay.</span></div></li>
                    <li data-reveal><i>ii.</i><div><b>Fast UK dispatch</b><span>Packed with care, tracked to your door.</span></div></li>
                    <li data-reveal><i>iii.</i><div><b>Money back guarantee</b><span>Item as described — or your money back.</span></div></li>
                </ul>
                <a class="btn btn-tang" href="{{ route('about') }}#faq" style="margin-top:34px">How buying works <span class="arr">→</span></a>
            </div>
            <div class="cols">
                @foreach ([[0, 1, '-0.2'], [2, 3, '0.2']] as [$a, $b, $speed])
                    <div class="col" data-speed="{{ $speed }}">
                        @foreach ([$a, $b] as $idx)
                            <div class="ph">
                                @if ($img = $storyImages->get($idx))
                                    <img src="{{ \App\Models\Product::sized($img->images[0] ?? null, 600) }}" alt="" loading="lazy" width="400" height="530">
                                @else
                                    <img src="{{ asset('images/logo.png') }}" alt="" loading="lazy" style="object-fit:contain;padding:22%">
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ========================================================== DEALS --}}
    @if ($deals->count() >= 2)
        <section class="pad" data-carousel-wrap>
            <div class="wrap">
                <div class="sh">
                    <div><small>03 — Price drops</small><h2 data-split>Up to <em data-keep>{{ $maxDiscount }}% off</em>, while stock lasts</h2></div>
                    <div class="arrows"><button class="round" data-prev aria-label="Previous">←</button><button class="round" data-next aria-label="Next">→</button></div>
                </div>
                <div class="swiper deal-swiper" data-carousel>
                    <div class="swiper-wrapper">
                        @foreach ($deals as $p)
                            <div class="swiper-slide">
                                <a class="deal" href="{{ route('product', $p) }}">
                                    <div class="im"><img src="{{ \App\Models\Product::sized($p->images[0] ?? null, 300) ?? $p->thumb }}" alt="" loading="lazy" width="150" height="150"></div>
                                    <div><div class="pc">-{{ $p->discount_percent }}%</div><h3>{{ $p->title }}</h3><p class="pr"><b>{{ $p->money() }}</b><s>{{ $p->money($p->original_price) }}</s></p></div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ======================================================== REVIEWS --}}
    @if ($testimonials->isNotEmpty())
        <section class="pad reviews" style="padding-top:20px">
            <div class="wrap">
                <div class="stars">★★★★★</div>
                <div class="swiper rev-swiper" data-reviews>
                    <div class="swiper-wrapper">
                        @foreach ($testimonials as $t)
                            <div class="swiper-slide"><blockquote>“{{ $t['text'] }}”</blockquote><p class="who"><b>{{ $t['name'] }}</b> · eBay buyer</p></div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </section>
    @endif
@endsection

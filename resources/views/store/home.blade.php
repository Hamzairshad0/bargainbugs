@extends('layouts.store')

@section('content')
    {{-- ============================== HERO ============================== --}}
    <section class="relative overflow-hidden border-b-2 border-ink">
        <div class="dots pointer-events-none absolute inset-0 opacity-[0.06]"></div>
        <div class="pointer-events-none absolute -left-24 top-24 h-72 w-72 rounded-full bg-sun/40 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-24 bottom-0 h-80 w-80 rounded-full bg-bug-300/40 blur-3xl"></div>

        <div class="container-x relative grid items-center gap-12 py-14 lg:grid-cols-[1.1fr_1fr] lg:py-24">
            <div>
                <span class="chip bg-white">
                    <span class="relative flex h-2 w-2"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-leaf opacity-75"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-leaf"></span></span>
                    {{ number_format($liveCount) }} bargains live right now
                </span>

                @php
                    $heroTitle = setting('hero_title');
                    $words = explode(' ', $heroTitle);
                    $last = array_pop($words);
                @endphp
                <h1 class="mt-6 font-display text-[44px] font-extrabold leading-[0.98] sm:text-6xl lg:text-7xl">
                    {{ implode(' ', $words) }} <span class="highlight">{{ $last }}</span>
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-relaxed text-ink/70">{{ setting('hero_subtitle') }}</p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('shop') }}" class="btn-primary !px-8 !py-4 !text-base">{{ setting('hero_cta') }} @include('partials.icon', ['name' => 'arrow', 'class' => 'h-5 w-5'])</a>
                    <a href="{{ route('deals') }}" class="btn-ghost !px-8 !py-4 !text-base">See hot deals 🔥</a>
                </div>

                <div class="mt-10 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm font-semibold text-ink/70">
                    <span class="inline-flex items-center gap-2">@include('partials.icon', ['name' => 'shield', 'class' => 'h-5 w-5 text-leaf']) Buyer protection</span>
                    <span class="inline-flex items-center gap-2">@include('partials.icon', ['name' => 'truck', 'class' => 'h-5 w-5 text-leaf']) Fast UK dispatch</span>
                    <span class="inline-flex items-center gap-2">@include('partials.icon', ['name' => 'lock', 'class' => 'h-5 w-5 text-leaf']) Secure checkout</span>
                </div>
            </div>

            {{-- Floating product collage --}}
            <div class="relative mx-auto h-[420px] w-full max-w-[520px] sm:h-[500px]">
                <div class="absolute inset-6 rounded-[3rem] border-2 border-ink bg-bug-500 shadow-[10px_10px_0_0_var(--color-ink)]">
                    <div class="dots-light absolute inset-0 rounded-[3rem]"></div>
                    <img src="{{ asset('images/logo-mark.svg') }}" alt="" class="absolute -bottom-6 -left-6 h-24 w-24 animate-wiggle drop-shadow-lg">
                </div>
                @php
                    $pos = [
                        ['left-0 top-4 w-[56%]', '-6deg', '0s'],
                        ['right-0 top-20 w-[50%]', '5deg', '1.2s'],
                        ['left-[18%] bottom-0 w-[48%]', '-2deg', '2.4s'],
                    ];
                @endphp
                @forelse ($heroProducts as $i => $p)
                    <a href="{{ route('product', $p) }}" class="absolute {{ $pos[$i][0] }} animate-float block rounded-3xl border-2 border-ink bg-white p-2 shadow-[6px_6px_0_0_var(--color-ink)] transition hover:z-20 hover:scale-[1.03]"
                       style="--r: {{ $pos[$i][1] }}; animation-delay: {{ $pos[$i][2] }}">
                        <img src="{{ $p->image }}" alt="{{ $p->title }}" class="aspect-square w-full rounded-2xl object-cover" onerror="this.src='{{ asset('images/placeholder.svg') }}'">
                        <div class="flex items-center justify-between gap-2 px-1.5 pb-1 pt-2">
                            <span class="truncate text-xs font-semibold">{{ $p->title }}</span>
                            <span class="shrink-0 rounded-full bg-sun px-2.5 py-0.5 font-display text-sm font-extrabold">{{ $p->money() }}</span>
                        </div>
                    </a>
                @empty
                    <div class="absolute inset-0 grid place-items-center">
                        <div class="rounded-3xl border-2 border-ink bg-white p-8 text-center shadow-[6px_6px_0_0_var(--color-ink)]">
                            <p class="font-display text-2xl font-extrabold">New stock landing soon 🐞</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ============================ TRUST STRIP ============================ --}}
    <section class="border-b-2 border-ink bg-white">
        <div class="container-x grid grid-cols-2 divide-ink/10 lg:grid-cols-4 lg:divide-x">
            @foreach ([['shield', 'trust_1'], ['truck', 'trust_2'], ['heart', 'trust_3'], ['spark', 'trust_4']] as [$icon, $key])
                <div class="flex items-center gap-3 px-2 py-5 lg:justify-center">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full border-2 border-ink bg-cream">@include('partials.icon', ['name' => $icon, 'class' => 'h-5 w-5'])</span>
                    <span class="text-sm font-bold leading-tight">{{ setting($key) }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ============================ CATEGORIES ============================ --}}
    @if ($navCategories->isNotEmpty())
        <section class="container-x py-16 lg:py-20">
            <div class="reveal flex items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-extrabold uppercase tracking-widest text-bug-500">Browse</p>
                    <h2 class="mt-1 font-display text-3xl font-extrabold sm:text-4xl">Shop by category</h2>
                </div>
                <a href="{{ route('categories') }}" class="hidden font-bold underline decoration-sun decoration-4 underline-offset-4 hover:text-bug-600 sm:inline">All categories →</a>
            </div>
            <div class="mt-8 flex gap-4 overflow-x-auto pb-4 scrollbar-none sm:grid sm:grid-cols-3 sm:overflow-visible lg:grid-cols-6">
                @php $tileColors = ['bg-sun', 'bg-bug-100', 'bg-white', 'bg-[#d9f2e8]', 'bg-[#e3e8ff]', 'bg-cream-dark']; @endphp
                @foreach ($navCategories->take(6) as $i => $cat)
                    <a href="{{ route('category', $cat) }}" class="reveal group flex w-40 shrink-0 flex-col items-center gap-3 rounded-3xl border-2 border-ink {{ $tileColors[$i % 6] }} p-5 text-center shadow-[4px_4px_0_0_var(--color-ink)] transition hover:-translate-y-1 hover:shadow-[6px_7px_0_0_var(--color-ink)] sm:w-auto">
                        @if ($cat->image_url)
                            <img src="{{ $cat->image_url }}" alt="" class="h-16 w-16 rounded-2xl object-cover">
                        @else
                            <span class="text-5xl transition-transform group-hover:scale-110 group-hover:rotate-6">{{ $cat->icon ?: '🛍️' }}</span>
                        @endif
                        <span class="text-sm font-extrabold leading-tight">{{ $cat->name }}</span>
                        <span class="text-xs font-semibold text-ink/50">{{ $cat->products_count }} items</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============================= FEATURED ============================= --}}
    @if ($featured->isNotEmpty())
        <section class="border-y-2 border-ink bg-white py-16 lg:py-20">
            <div class="container-x">
                <div class="reveal flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-sm font-extrabold uppercase tracking-widest text-bug-500">Hand-picked</p>
                        <h2 class="mt-1 font-display text-3xl font-extrabold sm:text-4xl">Featured bargains</h2>
                    </div>
                    <a href="{{ route('shop') }}" class="btn-ghost btn-sm">View all @include('partials.icon', ['name' => 'arrow', 'class' => 'h-4 w-4'])</a>
                </div>
                <div class="mt-8 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                    @foreach ($featured as $product)
                        <div class="reveal"><x-product-card :product="$product" /></div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============================ DEALS BANNER ============================ --}}
    @if ($deals->isNotEmpty())
        <section class="container-x py-16 lg:py-20">
            <div class="relative overflow-hidden rounded-[2rem] border-2 border-ink bg-ink p-6 text-white shadow-[8px_8px_0_0_var(--color-bug-500)] sm:p-10">
                <div class="dots-light pointer-events-none absolute inset-0"></div>
                <div class="relative grid gap-8 lg:grid-cols-[1fr_2fr] lg:items-center">
                    <div class="reveal">
                        <span class="badge bg-bug-500 text-white">Limited time</span>
                        <h2 class="mt-4 font-display text-4xl font-extrabold leading-tight sm:text-5xl">Hot deals <span class="text-sun">this week</span> 🔥</h2>
                        <p class="mt-4 text-white/70">Our biggest price drops, all in one place. When they’re gone, they’re gone.</p>
                        <a href="{{ route('deals') }}" class="btn-sun mt-6">Grab a deal @include('partials.icon', ['name' => 'arrow', 'class' => 'h-4 w-4'])</a>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-ink lg:grid-cols-4">
                        @foreach ($deals as $product)
                            <div class="reveal"><x-product-card :product="$product" compact /></div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ============================ NEW ARRIVALS ============================ --}}
    @if ($latest->isNotEmpty())
        <section class="container-x pb-16 lg:pb-20 {{ $deals->isEmpty() ? 'pt-16 lg:pt-20' : '' }}">
            <div class="reveal flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-extrabold uppercase tracking-widest text-bug-500">Fresh in</p>
                    <h2 class="mt-1 font-display text-3xl font-extrabold sm:text-4xl">Just landed</h2>
                </div>
                <a href="{{ route('new') }}" class="btn-ghost btn-sm">All new items @include('partials.icon', ['name' => 'arrow', 'class' => 'h-4 w-4'])</a>
            </div>
            <div class="mt-8 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                @foreach ($latest as $product)
                    <div class="reveal"><x-product-card :product="$product" /></div>
                @endforeach
            </div>
        </section>
    @else
        <section class="container-x py-24 text-center">
            <img src="{{ asset('images/logo-mark.svg') }}" alt="" class="mx-auto h-24 w-24 animate-wiggle">
            <h2 class="mt-6 font-display text-3xl font-extrabold">Our bugs are busy stocking the shelves</h2>
            <p class="mx-auto mt-3 max-w-md text-ink/60">New bargains are on their way. In the meantime, check out our eBay store.</p>
            <a href="{{ setting('ebay_store_url') }}" class="btn-primary mt-6" target="_blank" rel="noopener">Visit our eBay store</a>
        </section>
    @endif

    {{-- ============================ HOW IT WORKS ============================ --}}
    <section class="border-y-2 border-ink bg-cream-dark py-16 lg:py-20">
        <div class="container-x">
            <div class="reveal mx-auto max-w-2xl text-center">
                <p class="text-sm font-extrabold uppercase tracking-widest text-bug-500">Easy peasy</p>
                <h2 class="mt-1 font-display text-3xl font-extrabold sm:text-4xl">How buying works</h2>
                <p class="mt-3 text-ink/60">Browse here, then check out on eBay. You get eBay’s protection on every order.</p>
            </div>
            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ([
                    ['1', 'Find your bargain', 'Browse hundreds of deals here — filter by category, price and condition.', 'bg-sun'],
                    ['2', 'Buy securely on eBay', 'Tap “Buy on eBay” and check out with PayPal, card, Apple Pay or Google Pay.', 'bg-bug-500 text-white'],
                    ['3', 'Fast delivery', 'We pack and post quickly from the UK. Track your parcel right from eBay.', 'bg-leaf text-white'],
                ] as [$n, $t, $d, $c])
                    <div class="reveal card-pop p-7">
                        <span class="grid h-12 w-12 place-items-center rounded-2xl border-2 border-ink font-display text-xl font-extrabold {{ $c }}">{{ $n }}</span>
                        <h3 class="mt-5 text-xl font-extrabold">{{ $t }}</h3>
                        <p class="mt-2 text-sm leading-6 text-ink/65">{{ $d }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================ SOCIAL CTA ============================ --}}
    <section class="container-x pt-16 lg:pt-20">
        <div class="reveal grid items-center gap-8 rounded-[2rem] border-2 border-ink bg-white p-8 shadow-[7px_7px_0_0_var(--color-ink)] sm:p-12 lg:grid-cols-2">
            <div>
                <h2 class="font-display text-3xl font-extrabold sm:text-4xl">Follow the bugs 🐞</h2>
                <p class="mt-3 max-w-md text-ink/65">We drop new deals, unboxings and giveaways on our socials first. Follow along and never miss a bargain.</p>
            </div>
            <div class="flex flex-wrap gap-3 lg:justify-end">
                @forelse (socials() as $key => [$label, $url])
                    <a href="{{ $url }}" target="_blank" rel="noopener" class="btn-ghost">@include('partials.icon', ['name' => $key, 'class' => 'h-5 w-5']) {{ $label }}</a>
                @empty
                    <a href="{{ setting('ebay_store_url') }}" target="_blank" rel="noopener" class="btn-ghost">@include('partials.icon', ['name' => 'bag', 'class' => 'h-5 w-5']) Follow us on eBay</a>
                @endforelse
            </div>
        </div>
    </section>
@endsection

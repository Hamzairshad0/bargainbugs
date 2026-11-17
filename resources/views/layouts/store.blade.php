<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#fbfaf7">
    @php
        $pageTitle = trim($__env->yieldContent('title'));
        $fullTitle = $pageTitle ? $pageTitle.' | '.setting('site_name') : setting('site_name').' — '.setting('tagline');
        $metaDesc = trim($__env->yieldContent('description')) ?: setting('meta_description');
        $ogImage = trim($__env->yieldContent('og_image')) ?: asset('images/og-default.png');
    @endphp
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $metaDesc }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ setting('site_name') }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="{{ $pageTitle ?: setting('site_name').' — '.setting('tagline') }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:locale" content="en_GB">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ $ogImage }}">
    @php preg_match('/content=["\']([^"\']+)/', (string) setting('google_site_verification'), $gsv); $gsvCode = $gsv[1] ?? trim((string) setting('google_site_verification')); @endphp
    @if ($gsvCode !== '')<meta name="google-site-verification" content="{{ $gsvCode }}">@endif
    @stack('meta')

    <script>if (!matchMedia('(prefers-reduced-motion: reduce)').matches) { document.documentElement.classList.add('motion'); setTimeout(function () { document.documentElement.classList.remove('motion'); }, 3000); }</script>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="preconnect" href="https://i.ebayimg.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.bunny.net/css?family=inter-tight:400,500,600,700|instrument-serif:400,400i&display=swap">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=inter-tight:400,500,600,700|instrument-serif:400,400i&display=swap">
    @stack('preload')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="@yield('body_class')" x-data>
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[200] btn btn-dark">Skip to content</a>
    <div class="progress" data-progress></div>

    @include('partials.header')

    <main id="main">@yield('content')</main>

    @include('partials.footer')

    {{-- ===== Overlays ===== --}}
    <div class="scrim" x-show="$store.ui.panel" x-cloak x-transition.opacity.duration.300ms @click="$store.ui.close()"></div>

    {{-- Search --}}
    <div class="search-panel" x-show="$store.ui.panel === 'search'" x-cloak x-data="liveSearch"
         x-transition:enter="transition duration-500 ease-[cubic-bezier(.16,1,.3,1)]" x-transition:enter-start="-translate-y-full" x-transition:leave="transition duration-300" x-transition:leave-end="-translate-y-full"
         x-effect="if ($store.ui.panel === 'search') $nextTick(() => $refs.q.focus())">
        <form class="wrap" action="{{ route('shop') }}" method="get" @submit="submit($event)">
            <div class="search-row">
                @include('partials.icon', ['name' => 'search', 'class' => 'icon'])
                <input x-ref="q" type="search" name="q" x-model="q" @input="input()" @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)" placeholder="Search kettles, earbuds, toys…" autocomplete="off" aria-label="Search products">
                <button type="button" class="round" @click="$store.ui.close()" aria-label="Close search">@include('partials.icon', ['name' => 'close', 'class' => 'icon icon-sm'])</button>
            </div>
            <div class="suggest" x-show="items.length">
                <template x-for="(it, i) in items" :key="it.url">
                    <a :href="it.url" :class="active === i && 'on'"><img :src="it.image" alt=""><div><p x-text="it.title"></p><b x-text="it.price"></b></div></a>
                </template>
            </div>
            <p x-show="q.trim().length > 1 && !loading && !items.length" style="padding-bottom:26px;color:var(--color-muted)">No bargains match “<span x-text="q"></span>” yet.</p>
            <p x-show="total > items.length" style="padding-bottom:26px"><a class="ulink" :href="url">See all <span x-text="total"></span> results →</a></p>
        </form>
    </div>

    {{-- Mobile menu --}}
    <aside class="drawer left" x-show="$store.ui.panel === 'menu'" x-cloak data-lenis-prevent
           x-transition:enter="transition duration-500 ease-[cubic-bezier(.16,1,.3,1)]" x-transition:enter-start="-translate-x-full" x-transition:leave="transition duration-300" x-transition:leave-end="-translate-x-full" aria-label="Menu">
        <div class="drawer-head">
            <a class="logo" href="{{ route('home') }}"><img src="{{ asset('images/logo.png') }}" alt="" width="44" height="44" style="width:44px;height:44px"><span>Bargain <i>Bugs</i></span></a>
            <button class="ibtn" @click="$store.ui.close()" aria-label="Close menu">@include('partials.icon', ['name' => 'close', 'class' => 'icon'])</button>
        </div>
        <div class="drawer-body">
            <nav class="mnav">
                @foreach ([['home', 'Home'], ['shop', 'Shop all'], ['deals', 'Price drops'], ['new', 'New in'], ['about', 'About'], ['contact', 'Contact']] as [$r, $l])
                    <a href="{{ route($r) }}">{{ $l }} <span style="color:var(--color-muted)">→</span></a>
                @endforeach
                @if ($navCategories->isNotEmpty())
                    <small>Categories</small>
                    @foreach ($navCategories as $c)
                        <a class="mcat" href="{{ route('category', $c) }}" style="border:0;font-size:16px;padding:9px 0">
                            @if ($c->cover)<img src="{{ $c->cover }}" alt="" loading="lazy">@else<span style="font-size:26px">{{ $c->icon }}</span>@endif
                            {{ $c->name }} <span>{{ $c->products_count }}</span>
                        </a>
                    @endforeach
                @endif
            </nav>
            <a href="{{ setting('ebay_store_url') }}" target="_blank" rel="noopener" class="btn btn-dark" style="width:100%;margin-top:28px">Visit our eBay store</a>
            @if (socials())
                <div class="socials" style="justify-content:center">
                    @foreach (socials() as $key => [$label, $url])
                        <a class="round" href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $label }}">@include('partials.icon', ['name' => $key, 'class' => 'icon icon-sm'])</a>
                    @endforeach
                </div>
            @endif
        </div>
    </aside>

    {{-- Wishlist --}}
    <aside class="drawer right" x-show="$store.ui.panel === 'wish'" x-cloak data-lenis-prevent x-data="wishDrawer" x-effect="if ($store.ui.panel === 'wish') load()"
           x-transition:enter="transition duration-500 ease-[cubic-bezier(.16,1,.3,1)]" x-transition:enter-start="translate-x-full" x-transition:leave="transition duration-300" x-transition:leave-end="translate-x-full" aria-label="Wishlist">
        <div class="drawer-head">
            <div><b style="font-size:20px;letter-spacing:-.02em">Your wishlist</b><p style="font-size:13px;color:var(--color-muted)"><span x-text="$store.wish.ids.length"></span> saved on this device</p></div>
            <button class="ibtn" @click="$store.ui.close()" aria-label="Close wishlist">@include('partials.icon', ['name' => 'close', 'class' => 'icon'])</button>
        </div>
        <div class="drawer-body">
            <p x-show="loading" style="padding:40px 0;text-align:center;color:var(--color-muted)">Loading…</p>
            <div x-show="!loading" x-html="html"></div>
            <div x-show="!loading && !$store.wish.ids.length" style="padding:50px 0;text-align:center">
                <p class="serif" style="font-size:34px">Nothing saved yet</p>
                <p style="margin-top:8px;color:var(--color-muted)">Tap the heart on any product to keep it here.</p>
                <a href="{{ route('shop') }}" class="btn btn-dark btn-sm" style="margin-top:22px">Start browsing</a>
            </div>
        </div>
    </aside>

    <div class="toasts" aria-live="polite">
        <template x-for="t in $store.toast.items" :key="t.id">
            <div class="toast" x-transition><i x-text="t.icon"></i><span x-text="t.text"></span></div>
        </template>
    </div>

    @stack('scripts')
</body>
</html>

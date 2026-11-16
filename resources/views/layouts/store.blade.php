<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ef3340">
    @php
        $pageTitle = trim($__env->yieldContent('title'));
        $fullTitle = $pageTitle ? $pageTitle.' | '.setting('site_name') : setting('site_name').' — '.setting('tagline');
        $metaDesc = trim($__env->yieldContent('description')) ?: setting('meta_description');
        $ogImage = trim($__env->yieldContent('og_image')) ?: asset('images/og-default.png');
    @endphp
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $metaDesc }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph / social cards: makes links look great when shared on Instagram, Facebook, WhatsApp, X --}}
    <meta property="og:site_name" content="{{ setting('site_name') }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="{{ $pageTitle ?: setting('site_name').' — '.setting('tagline') }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:locale" content="en_GB">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle ?: setting('site_name') }}">
    <meta name="twitter:description" content="{{ $metaDesc }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    @stack('meta')

    <script>document.documentElement.classList.add('js')</script>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=bricolage-grotesque:600,700,800|inter:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] btn-sun">Skip to content</a>

    {{-- Announcement marquee --}}
    @if (setting('announcement'))
        <div class="relative overflow-hidden border-b-2 border-ink bg-bug-500 py-2 text-[13px] font-bold text-white">
            <div class="flex w-max animate-marquee gap-12 whitespace-nowrap">
                @for ($i = 0; $i < 8; $i++)
                    <span>{{ setting('announcement') }}</span>
                @endfor
            </div>
        </div>
    @endif

    {{-- Header --}}
    <header data-header class="sticky top-0 z-50 border-b-2 border-ink bg-cream/95 backdrop-blur transition-shadow">
        <div class="container-x flex h-[72px] items-center justify-between gap-4">
            @include('partials.logo')

            <nav class="hidden items-center gap-1 lg:flex" aria-label="Main">
                @php
                    $nav = [['shop', 'Shop all'], ['deals', 'Hot deals 🔥'], ['new', 'New in'], ['categories', 'Categories'], ['about', 'About'], ['contact', 'Contact']];
                @endphp
                @foreach ($nav as [$r, $label])
                    <a href="{{ route($r) }}" class="rounded-full px-4 py-2 text-sm font-semibold transition hover:bg-ink/5 {{ request()->routeIs($r) ? 'bg-ink text-white hover:bg-ink' : '' }}">{{ $label }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2">
                <button type="button" data-toggle="#search-panel" aria-label="Search" aria-expanded="false" class="grid h-11 w-11 place-items-center rounded-full border-2 border-ink bg-white transition hover:bg-sun">
                    @include('partials.icon', ['name' => 'search'])
                </button>
                <a href="{{ setting('ebay_store_url') }}" target="_blank" rel="noopener" class="btn-primary btn-sm hidden sm:inline-flex">
                    @include('partials.icon', ['name' => 'bag', 'class' => 'h-4 w-4']) eBay Store
                </a>
                <button type="button" data-toggle="#mobile-menu" aria-label="Menu" aria-expanded="false" class="grid h-11 w-11 place-items-center rounded-full border-2 border-ink bg-sun lg:hidden">
                    @include('partials.icon', ['name' => 'menu'])
                </button>
            </div>
        </div>

        {{-- Search drop-down --}}
        <div id="search-panel" class="hidden border-t-2 border-ink bg-white">
            <form action="{{ route('shop') }}" method="get" class="container-x flex items-center gap-3 py-4">
                @include('partials.icon', ['name' => 'search', 'class' => 'h-5 w-5 shrink-0 text-ink/40'])
                <input data-autofocus type="search" name="q" value="{{ request('q') }}" placeholder="Search bargains… e.g. lego, kettle, phone case" class="w-full bg-transparent text-lg font-medium outline-none placeholder:text-ink/35">
                <button class="btn-dark btn-sm">Search</button>
            </form>
        </div>

        {{-- Mobile menu --}}
        <div id="mobile-menu" class="hidden border-t-2 border-ink bg-cream lg:hidden">
            <nav class="container-x grid gap-1 py-4" aria-label="Mobile">
                @foreach ($nav as [$r, $label])
                    <a href="{{ route($r) }}" class="rounded-2xl px-4 py-3 text-base font-bold hover:bg-white {{ request()->routeIs($r) ? 'bg-white' : '' }}">{{ $label }}</a>
                @endforeach
                <a href="{{ setting('ebay_store_url') }}" target="_blank" rel="noopener" class="btn-primary mt-2">Visit our eBay store</a>
                @if (socials())
                    <div class="mt-3 flex justify-center gap-3">
                        @foreach (socials() as $key => [$label, $url])
                            <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $label }}" class="grid h-11 w-11 place-items-center rounded-full border-2 border-ink bg-white">@include('partials.icon', ['name' => $key])</a>
                        @endforeach
                    </div>
                @endif
            </nav>
        </div>
    </header>

    <main id="main">
        @yield('content')
    </main>

    {{-- Newsletter --}}
    @unless (View::hasSection('hide_newsletter'))
        <section class="container-x py-16">
            <div class="relative overflow-hidden rounded-[2rem] border-2 border-ink bg-sun p-8 shadow-[7px_7px_0_0_var(--color-ink)] sm:p-12">
                <div class="dots pointer-events-none absolute -right-10 -top-10 h-64 w-64 rounded-full opacity-10"></div>
                <div class="relative grid items-center gap-8 lg:grid-cols-2">
                    <div>
                        <h2 class="font-display text-3xl font-extrabold sm:text-4xl">Don’t let the best bargains bug off. 🐞</h2>
                        <p class="mt-3 max-w-md font-medium text-ink/75">Join the Bug Club for first dibs on new stock, flash deals and subscriber-only drops. No spam, ever.</p>
                    </div>
                    <form action="{{ route('subscribe') }}" method="post" class="flex flex-col gap-3 sm:flex-row">
                        @csrf
                        <label for="nl-email" class="sr-only">Email address</label>
                        <input id="nl-email" type="email" name="email" required placeholder="your@email.com" class="input !border-ink !py-4 !text-base">
                        <button class="btn-dark !py-4">Join the club</button>
                    </form>
                </div>
                @if (session('subscribed'))
                    <p class="relative mt-4 font-bold">{{ session('subscribed') }}</p>
                @endif
                @error('email')<p class="relative mt-4 font-bold text-bug-700">{{ $message }}</p>@enderror
            </div>
        </section>
    @endunless

    {{-- Footer --}}
    <footer class="relative overflow-hidden border-t-2 border-ink bg-ink text-white">
        <div class="dots-light pointer-events-none absolute inset-0 opacity-50"></div>
        <div class="container-x relative grid gap-10 py-14 md:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-1">
                @include('partials.logo', ['text' => 'text-white'])
                <p class="mt-4 max-w-xs text-sm leading-6 text-white/70">{{ setting('tagline') }} Every order is processed securely through eBay with full buyer protection.</p>
                @if (socials())
                    <div class="mt-5 flex gap-2">
                        @foreach (socials() as $key => [$label, $url])
                            <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $label }}" class="grid h-10 w-10 place-items-center rounded-full bg-white/10 transition hover:bg-bug-500">@include('partials.icon', ['name' => $key, 'class' => 'h-4 w-4'])</a>
                        @endforeach
                    </div>
                @endif
            </div>
            <div>
                <h3 class="font-display text-lg font-bold text-sun">Shop</h3>
                <ul class="mt-4 space-y-2.5 text-sm text-white/75">
                    <li><a class="hover:text-white" href="{{ route('shop') }}">All bargains</a></li>
                    <li><a class="hover:text-white" href="{{ route('deals') }}">Hot deals</a></li>
                    <li><a class="hover:text-white" href="{{ route('new') }}">New in</a></li>
                    @foreach ($navCategories->take(4) as $cat)
                        <li><a class="hover:text-white" href="{{ route('category', $cat) }}">{{ $cat->name }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h3 class="font-display text-lg font-bold text-sun">Help</h3>
                <ul class="mt-4 space-y-2.5 text-sm text-white/75">
                    <li><a class="hover:text-white" href="{{ route('about') }}">About us</a></li>
                    <li><a class="hover:text-white" href="{{ route('contact') }}">Contact</a></li>
                    <li><a class="hover:text-white" href="{{ route('about') }}#faq">Delivery & returns</a></li>
                    <li><a class="hover:text-white" href="{{ route('privacy') }}">Privacy policy</a></li>
                    <li><a class="hover:text-white" href="{{ setting('ebay_store_url') }}" target="_blank" rel="noopener">Our eBay store ↗</a></li>
                </ul>
            </div>
            <div>
                <h3 class="font-display text-lg font-bold text-sun">Get in touch</h3>
                <ul class="mt-4 space-y-2.5 text-sm text-white/75">
                    @if (setting('contact_email'))<li><a class="hover:text-white" href="mailto:{{ setting('contact_email') }}">{{ setting('contact_email') }}</a></li>@endif
                    @if (setting('contact_phone'))<li>{{ setting('contact_phone') }}</li>@endif
                    @if (setting('whatsapp'))<li><a class="hover:text-white" href="https://wa.me/{{ preg_replace('/\D/', '', setting('whatsapp')) }}" target="_blank" rel="noopener">WhatsApp us</a></li>@endif
                    <li>{{ setting('address') }}</li>
                </ul>
            </div>
        </div>
        <div class="relative border-t border-white/10">
            <div class="container-x flex flex-col items-center justify-between gap-2 py-5 text-xs text-white/50 sm:flex-row">
                <p>© {{ date('Y') }} {{ setting('site_name') }}. All rights reserved.</p>
                <p>Purchases are completed securely on eBay. eBay is a trademark of eBay Inc.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>

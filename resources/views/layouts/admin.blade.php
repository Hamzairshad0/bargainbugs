<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Dashboard') · {{ setting('site_name') }} Admin</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=bricolage-grotesque:600,700,800|inter:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen !bg-[#f7f3ee]">
    @php
        $links = [
            ['admin.dashboard', 'Dashboard', 'spark', 'admin.dashboard'],
            ['admin.products.index', 'Products', 'tag', 'admin.products.*'],
            ['admin.categories.index', 'Categories', 'bag', 'admin.categories.*'],
            ['admin.ebay', 'eBay Sync', 'link', 'admin.ebay*'],
            ['admin.messages.index', 'Messages', 'mail', 'admin.messages.*'],
            ['admin.subscribers', 'Subscribers', 'heart', 'admin.subscribers*'],
            ['admin.settings', 'Settings', 'shield', 'admin.settings'],
        ];
    @endphp

    <div class="lg:flex">
        {{-- Sidebar --}}
        <aside id="admin-nav" class="fixed inset-y-0 left-0 z-50 hidden w-72 flex-col border-r-2 border-ink bg-ink text-white lg:sticky lg:top-0 lg:flex lg:h-screen">
            <div class="flex items-center justify-between px-6 py-6">
                @include('partials.logo', ['text' => 'text-white'])
                <button data-toggle="#admin-nav" class="lg:hidden" aria-label="Close menu">@include('partials.icon', ['name' => 'close'])</button>
            </div>
            <nav class="flex-1 space-y-1 px-3">
                @foreach ($links as [$route, $label, $icon, $active])
                    <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs($active) ? 'bg-sun text-ink' : 'text-white/75 hover:bg-white/10 hover:text-white' }}">
                        @include('partials.icon', ['name' => $icon, 'class' => 'h-5 w-5'])
                        <span class="flex-1">{{ $label }}</span>
                        @if ($label === 'Messages' && $unreadMessages)
                            <span class="rounded-full bg-bug-500 px-2 py-0.5 text-xs font-bold text-white">{{ $unreadMessages }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>
            <div class="border-t border-white/10 p-4">
                <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-white/75 hover:bg-white/10 hover:text-white">@include('partials.icon', ['name' => 'eye', 'class' => 'h-5 w-5']) View website ↗</a>
                <form action="{{ route('admin.logout') }}" method="post">
                    @csrf
                    <button class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-white/75 hover:bg-white/10 hover:text-white">@include('partials.icon', ['name' => 'lock', 'class' => 'h-5 w-5']) Log out</button>
                </form>
                <p class="px-4 pt-2 text-xs text-white/40">{{ auth()->user()->email }}</p>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-40 flex items-center justify-between gap-4 border-b-2 border-ink bg-white px-4 py-4 sm:px-8">
                <div class="flex items-center gap-3">
                    <button data-toggle="#admin-nav" class="grid h-10 w-10 place-items-center rounded-full border-2 border-ink bg-sun lg:hidden" aria-label="Menu">@include('partials.icon', ['name' => 'menu'])</button>
                    <h1 class="font-display text-2xl font-extrabold">@yield('title', 'Dashboard')</h1>
                </div>
                <div class="flex items-center gap-2">@yield('actions')</div>
            </header>

            <main class="p-4 sm:p-8">
                @if (session('success'))
                    <div class="mb-6 flex items-start gap-3 rounded-2xl border-2 border-leaf bg-[#e6f7f0] p-4 text-sm font-semibold text-[#136b4f]">✅ {{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-6 flex items-start gap-3 rounded-2xl border-2 border-bug-500 bg-bug-50 p-4 text-sm font-semibold text-bug-700">⚠️ {{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border-2 border-bug-500 bg-bug-50 p-4 text-sm font-semibold text-bug-700">
                        <ul class="list-inside list-disc">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>

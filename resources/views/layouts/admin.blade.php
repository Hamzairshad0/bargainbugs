<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Dashboard') · {{ setting('site_name') }} Admin</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=inter-tight:400,500,600,700|instrument-serif:400,400i&display=swap" rel="stylesheet">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="min-h-screen">
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
        <aside id="admin-nav" class="fixed inset-y-0 left-0 z-50 hidden w-72 flex-col border-r border-line bg-white lg:sticky lg:top-0 lg:flex lg:h-screen">
            <div class="flex items-center justify-between px-6 pb-6 pt-7">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="" width="42" height="42" class="h-[42px] w-[42px]">
                    <span class="leading-none">
                        <span class="block text-[19px] font-semibold tracking-tight">Bargain <em class="text-plum">Bugs</em></span>
                        <span class="eyebrow mt-1 block !text-[10px]">Store admin</span>
                    </span>
                </a>
                <button data-toggle="#admin-nav" class="grid h-10 w-10 place-items-center rounded-full border border-line lg:hidden" aria-label="Close menu">@include('partials.icon', ['name' => 'close', 'class' => 'h-4 w-4'])</button>
            </div>
            <p class="eyebrow px-7 pb-2">Manage</p>
            <nav class="flex-1 space-y-1 overflow-y-auto px-4">
                @foreach ($links as [$route, $label, $icon, $active])
                    @php $on = request()->routeIs($active); @endphp
                    <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-full px-4 py-2.5 text-[15px] font-medium transition {{ $on ? 'bg-ink text-white' : 'text-ink/70 hover:bg-stone hover:text-ink' }}">
                        @include('partials.icon', ['name' => $icon, 'class' => 'h-[18px] w-[18px]'])
                        <span class="flex-1">{{ $label }}</span>
                        @if ($label === 'Messages' && $unreadMessages)
                            <span class="rounded-full bg-bug-500 px-2 py-0.5 text-xs font-semibold text-white">{{ $unreadMessages }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>
            <div class="m-4 rounded-3xl bg-stone p-4">
                <a href="{{ route('home') }}" target="_blank" class="flex items-center justify-between rounded-full bg-white px-4 py-2.5 text-sm font-medium hover:bg-ink hover:text-white transition">View website <span>↗</span></a>
                <form action="{{ route('admin.logout') }}" method="post" class="mt-2">
                    @csrf
                    <button class="flex w-full items-center gap-2 rounded-full px-4 py-2 text-sm font-medium text-ink/60 hover:text-ink">@include('partials.icon', ['name' => 'lock', 'class' => 'h-4 w-4']) Log out</button>
                </form>
                <p class="truncate px-4 text-xs text-muted">{{ auth()->user()->email }}</p>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-40 flex items-center justify-between gap-4 border-b border-line bg-bg/85 px-4 py-4 backdrop-blur-md sm:px-10 sm:py-5">
                <div class="flex items-center gap-3">
                    <button data-toggle="#admin-nav" class="grid h-10 w-10 place-items-center rounded-full border border-line bg-white lg:hidden" aria-label="Menu">@include('partials.icon', ['name' => 'menu', 'class' => 'h-5 w-5'])</button>
                    <div>
                        <p class="eyebrow hidden sm:block">Bargain Bugs · Admin</p>
                        <h1 class="text-2xl font-semibold tracking-tight sm:text-[30px]">@yield('title', 'Dashboard')</h1>
                    </div>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-2">@yield('actions')</div>
            </header>

            <main class="p-4 sm:p-10">
                @if (session('success'))
                    <div class="mb-6 flex items-center gap-3 rounded-2xl border border-[#cfe8d6] bg-[#eef8f1] px-5 py-4 text-sm font-medium text-[#1f6b3a]"><span class="grid h-6 w-6 place-items-center rounded-full bg-leaf text-xs text-white">✓</span> {{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-6 flex items-center gap-3 rounded-2xl border border-bug-100 bg-bug-50 px-5 py-4 text-sm font-medium text-bug-700"><span class="grid h-6 w-6 place-items-center rounded-full bg-bug-500 text-xs text-white">!</span> {{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-bug-100 bg-bug-50 px-5 py-4 text-sm font-medium text-bug-700">
                        <ul class="list-inside list-disc">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>

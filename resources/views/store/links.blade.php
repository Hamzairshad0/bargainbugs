<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ef3340">
    <title>{{ setting('site_name') }} — Links</title>
    <meta name="description" content="{{ setting('tagline') }} Shop our latest deals, follow us and find our eBay store.">
    <meta property="og:title" content="{{ setting('site_name') }} 🐞">
    <meta property="og:description" content="{{ setting('tagline') }}">
    <meta property="og:image" content="{{ asset('images/og-default.png') }}">
    <meta property="og:url" content="{{ route('links') }}">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=bricolage-grotesque:600,700,800|inter:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{-- "Link in bio" page — put https://bargainbugs.co.uk/links in your Instagram / TikTok bio --}}
<body class="min-h-screen bg-bug-500">
    <div class="dots-light pointer-events-none fixed inset-0"></div>
    <main class="relative mx-auto flex min-h-screen max-w-md flex-col items-center px-5 py-10">
        <img src="{{ asset('images/logo-mark.svg') }}" alt="" class="h-24 w-24 rounded-full border-4 border-ink bg-white p-2 shadow-[5px_5px_0_0_var(--color-ink)]">
        <h1 class="mt-4 font-display text-3xl font-extrabold text-white">{{ setting('site_name') }}</h1>
        <p class="mt-1 text-center font-semibold text-white/85">{{ setting('tagline') }}</p>

        @if (socials())
            <div class="mt-4 flex gap-2">
                @foreach (socials() as $key => [$label, $url])
                    <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $label }}" class="grid h-10 w-10 place-items-center rounded-full border-2 border-ink bg-white transition hover:-translate-y-0.5">@include('partials.icon', ['name' => $key, 'class' => 'h-5 w-5'])</a>
                @endforeach
            </div>
        @endif

        <div class="mt-7 grid w-full gap-3">
            <a href="{{ route('home') }}" class="btn-sun w-full !py-4 !text-base">🛍️ Shop our website</a>
            <a href="{{ route('deals') }}" class="btn-ghost w-full !py-4 !text-base">🔥 Today’s hot deals</a>
            <a href="{{ route('new') }}" class="btn-ghost w-full !py-4 !text-base">✨ New arrivals</a>
            <a href="{{ setting('ebay_store_url') }}" target="_blank" rel="noopener" class="btn-ghost w-full !py-4 !text-base">🏪 Our eBay store</a>
            @if (setting('whatsapp'))
                <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('whatsapp')) }}" target="_blank" rel="noopener" class="btn-ghost w-full !py-4 !text-base">💬 WhatsApp us</a>
            @endif
            <a href="{{ route('contact') }}" class="btn-ghost w-full !py-4 !text-base">✉️ Contact us</a>
        </div>

        @if ($products->isNotEmpty())
            <h2 class="mt-10 font-display text-xl font-extrabold text-white">Latest bargains</h2>
            <div class="mt-4 grid w-full grid-cols-2 gap-3">
                @foreach ($products as $p)
                    <a href="{{ route('product', $p) }}" class="overflow-hidden rounded-2xl border-2 border-ink bg-white shadow-[4px_4px_0_0_var(--color-ink)] transition hover:-translate-y-0.5">
                        <img src="{{ $p->image }}" alt="{{ $p->title }}" class="aspect-square w-full object-cover" loading="lazy">
                        <div class="p-2.5">
                            <p class="truncate text-xs font-semibold">{{ $p->title }}</p>
                            <p class="font-display text-lg font-extrabold">{{ $p->money() }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        <p class="mt-10 text-xs font-semibold text-white/70">© {{ date('Y') }} {{ setting('site_name') }}</p>
    </main>
</body>
</html>

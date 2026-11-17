<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#fbfaf7">
    <title>{{ setting('site_name') }} — Links</title>
    <meta name="description" content="{{ setting('tagline') }} Shop our latest deals, follow us and find our eBay store.">
    <meta property="og:title" content="{{ setting('site_name') }}">
    <meta property="og:description" content="{{ setting('tagline') }}">
    <meta property="og:image" content="{{ asset('images/og-default.png') }}">
    <meta property="og:url" content="{{ route('links') }}">
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=inter-tight:400,500,600,700|instrument-serif:400,400i&display=swap">
    @vite(['resources/css/app.css'])
    <style>
        .lk { display: flex; align-items: center; justify-content: space-between; width: 100%; height: 62px; padding: 0 22px; border-radius: 18px; background: #fff; border: 1px solid var(--color-line); font-weight: 600; transition: transform .3s var(--ease), border-color .3s; }
        .lk:hover { transform: translateY(-2px); border-color: var(--color-ink); }
        .lk.main { background: var(--color-ink); color: #fff; border-color: var(--color-ink); }
    </style>
</head>
{{-- "Link in bio" page — put https://bargainbugs.co.uk/links in your Instagram / TikTok bio --}}
<body>
    <main style="max-width:460px;margin:0 auto;padding:44px 18px 60px;text-align:center">
        <img src="{{ asset('images/logo.png') }}" alt="{{ setting('site_name') }}" width="110" height="110" style="margin:0 auto">
        <h1 style="margin-top:14px;font-size:34px;font-weight:600;letter-spacing:-.04em">Bargain <em style="color:var(--color-plum)">Bugs</em></h1>
        <p style="margin-top:4px;color:var(--color-muted)">{{ setting('tagline') }}</p>
        @if (socials())
            <div class="socials" style="justify-content:center">
                @foreach (socials() as $key => [$label, $url])
                    <a class="round" href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $label }}">@include('partials.icon', ['name' => $key, 'class' => 'icon icon-sm'])</a>
                @endforeach
            </div>
        @endif
        <div style="display:grid;gap:10px;margin-top:28px">
            <a class="lk main" href="{{ route('home') }}">Shop our website <span>→</span></a>
            <a class="lk" href="{{ route('deals') }}">Today’s price drops <span>→</span></a>
            <a class="lk" href="{{ route('new') }}">New arrivals <span>→</span></a>
            <a class="lk" href="{{ setting('ebay_store_url') }}" target="_blank" rel="noopener">Our eBay store <span>↗</span></a>
            @if (setting('whatsapp'))<a class="lk" href="https://wa.me/{{ preg_replace('/\D/', '', setting('whatsapp')) }}" target="_blank" rel="noopener">WhatsApp us <span>↗</span></a>@endif
            <a class="lk" href="{{ route('contact') }}">Contact us <span>→</span></a>
        </div>
        @if ($products->isNotEmpty())
            <p class="serif" style="margin-top:44px;font-size:28px">Latest bargains</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:16px;text-align:left">
                @foreach ($products as $p)
                    <a href="{{ route('product', $p) }}" style="display:block">
                        <img src="{{ $p->thumb }}" alt="{{ $p->title }}" loading="lazy" width="250" height="250" style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:16px;background:var(--color-stone)">
                        <p style="margin-top:8px;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $p->title }}</p>
                        <b>{{ $p->money() }}</b>
                    </a>
                @endforeach
            </div>
        @endif
        <p style="margin-top:44px;font-size:12px;color:var(--color-muted)">© {{ date('Y') }} {{ setting('site_name') }}</p>
    </main>
</body>
</html>

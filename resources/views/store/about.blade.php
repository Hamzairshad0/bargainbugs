@extends('layouts.store')

@section('title', 'About us')

@section('content')
    <section class="page-head">
        <div class="wrap two" style="align-items:end">
            <div>
                <nav class="crumbs"><a href="{{ route('home') }}">Home</a><span>/</span><span>About</span></nav>
                <h1 data-split data-now>{{ setting('about_title') }}</h1>
            </div>
            <div class="prose-bb" style="font-size:17px" data-reveal>
                @foreach (preg_split("/\R{2,}/", setting('about_text')) as $para)<p>{{ $para }}</p>@endforeach
                <div class="cta" style="margin-top:20px"><a class="btn btn-dark" href="{{ route('shop') }}">Start shopping <span class="arr">→</span></a><a class="ulink" href="{{ setting('ebay_store_url') }}" target="_blank" rel="noopener">Our eBay feedback</a></div>
            </div>
        </div>
    </section>

    <section class="story" style="margin-top:40px">
        <div class="wrap" style="grid-template-columns:1fr">
            <div>
                <h2 data-split>Every order is <em data-keep>protected</em>.</h2>
                <ul class="feat" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:0 40px">
                    <li data-reveal><i>i.</i><div><b>Hand-picked bargains</b><span>We hunt down great value so you don’t have to.</span></div></li>
                    <li data-reveal><i>ii.</i><div><b>Secure eBay checkout</b><span>PayPal, cards, Apple Pay &amp; Google Pay.</span></div></li>
                    <li data-reveal><i>iii.</i><div><b>Packed with care</b><span>Fast UK dispatch, tracked to your door.</span></div></li>
                </ul>
            </div>
        </div>
    </section>

    <section class="pad" id="faq">
        <div class="wrap two">
            <div><small style="font-size:13px;color:var(--color-muted)">Questions</small><h2 class="h2" style="margin-top:14px" data-split>Good to <em data-keep>know</em></h2></div>
            <div class="faq">
                @foreach ([
                    ['How do I buy?', 'Click “Buy now on eBay” on any product. You’ll be taken to the listing on our eBay store where you can check out securely with PayPal, debit/credit card, Apple Pay or Google Pay.'],
                    ['Is it safe?', 'Yes. Every order goes through eBay, so you are covered by the eBay Money Back Guarantee — you get the item you ordered, or your money back.'],
                    ['How much is delivery and how long does it take?', 'Postage costs and estimated delivery dates are shown on each eBay listing before you pay. We dispatch quickly from the UK.'],
                    ['Can I return an item?', 'Returns are handled through eBay. Open your order in eBay and choose “Return this item” — the return policy for each item is shown on the listing.'],
                    ['Do prices here match eBay?', 'Yes. The website syncs with our eBay store automatically, so prices and stock stay up to date.'],
                    ['I have a question about an item', 'Send us a message through the contact page or on eBay — we usually reply within 24 hours.'],
                ] as [$q, $a])
                    <details data-reveal><summary>{{ $q }}</summary><p>{{ $a }}</p></details>
                @endforeach
            </div>
        </div>
    </section>
@endsection

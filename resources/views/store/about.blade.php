@extends('layouts.store')

@section('title', 'About us')

@section('content')
    <section class="relative overflow-hidden border-b-2 border-ink bg-bug-500 text-white">
        <div class="dots-light pointer-events-none absolute inset-0"></div>
        <div class="container-x relative grid items-center gap-10 py-16 lg:grid-cols-[1.4fr_1fr] lg:py-24">
            <div>
                <span class="chip !border-white !bg-transparent text-white">About Bargain Bugs</span>
                <h1 class="mt-5 font-display text-5xl font-extrabold leading-[1.02] sm:text-6xl">{{ setting('about_title') }}</h1>
            </div>
            <img src="{{ asset('images/logo-mark.svg') }}" alt="" class="mx-auto h-48 w-48 animate-float drop-shadow-[8px_8px_0_rgba(22,18,15,1)] sm:h-64 sm:w-64" style="--r:-8deg">
        </div>
    </section>

    <section class="container-x grid gap-10 py-16 lg:grid-cols-[1.4fr_1fr]">
        <div class="prose-bb !text-lg !leading-8">
            @foreach (preg_split("/\R{2,}/", setting('about_text')) as $para)
                <p>{{ $para }}</p>
            @endforeach
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('shop') }}" class="btn-primary">Start shopping</a>
                <a href="{{ setting('ebay_store_url') }}" target="_blank" rel="noopener" class="btn-ghost">Our eBay feedback ↗</a>
            </div>
        </div>
        <div class="grid gap-4 self-start">
            @foreach ([['🐞', 'Bargains first', 'We hunt down great value so you don’t have to.'], ['🔒', 'Buy with confidence', 'Every checkout runs through eBay with full buyer protection.'], ['📦', 'Packed with care', 'Fast dispatch from the UK, carefully packaged.']] as [$e, $t, $d])
                <div class="card-pop flex gap-4 p-5">
                    <span class="text-3xl">{{ $e }}</span>
                    <div><h3 class="font-extrabold">{{ $t }}</h3><p class="mt-1 text-sm text-ink/60">{{ $d }}</p></div>
                </div>
            @endforeach
        </div>
    </section>

    <section id="faq" class="border-t-2 border-ink bg-white py-16">
        <div class="container-x max-w-3xl">
            <h2 class="text-center font-display text-3xl font-extrabold sm:text-4xl">Frequently asked questions</h2>
            <div class="mt-10 space-y-3">
                @foreach ([
                    ['How do I buy?', 'Click “Buy now on eBay” on any product. You’ll be taken to the listing on our eBay store where you can check out securely with PayPal, debit/credit card, Apple Pay or Google Pay.'],
                    ['Is it safe?', 'Yes. Because every order goes through eBay, you are covered by the eBay Money Back Guarantee — you get the item you ordered, or your money back.'],
                    ['How much is delivery and how long does it take?', 'Postage costs and estimated delivery dates are shown on each eBay listing before you pay. We dispatch quickly from the UK.'],
                    ['Can I return an item?', 'Returns are handled through eBay. Open your order in eBay and choose “Return this item” — the return policy for each item is shown on the listing.'],
                    ['Do prices on this website match eBay?', 'Yes. Our website syncs with our eBay store automatically, so prices and stock stay up to date.'],
                    ['I have a question about an item', 'Send us a message through the contact page, or message us on eBay — we usually reply within 24 hours.'],
                ] as [$q, $a])
                    <details class="group rounded-3xl border-2 border-ink bg-cream p-5 open:bg-white open:shadow-[4px_4px_0_0_var(--color-ink)]">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-bold">
                            {{ $q }}
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full border-2 border-ink bg-sun text-lg leading-none transition group-open:rotate-45">+</span>
                        </summary>
                        <p class="mt-3 text-ink/70">{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
@endsection

@extends('layouts.admin')

@section('title', 'Dashboard')
@section('actions')
    <a href="{{ route('admin.products.create') }}" class="btn-primary btn-sm">+ Add product</a>
@endsection

@section('content')
    @if (! $ebayConnected)
        <div class="mb-6 flex flex-col gap-4 rounded-[24px] border border-line bg-sun p-6 shadow-[0_12px_32px_-18px_rgb(21_18_26/.12)] sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-display text-xl font-semibold">Connect your eBay store 🔌</h2>
                <p class="mt-1 text-sm font-medium text-ink/75">{{ $ebayConfigured ? 'Your API keys are set — click connect and log in with the bargainbugs eBay account.' : 'Add your eBay API keys to the environment first, then connect. Full guide in EBAY_SETUP.md.' }}</p>
            </div>
            <a href="{{ route('admin.ebay') }}" class="btn-dark shrink-0">Set up eBay sync →</a>
        </div>
    @endif

    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        @foreach ([
            ['Live products', number_format($stats['live']), 'of '.number_format($stats['total']).' total', 'bg-white'],
            ['Page views', number_format($stats['views']), 'all-time product views', 'bg-white'],
            ['eBay clicks', number_format($stats['clicks']), '“Buy on eBay” taps', 'bg-sun'],
            ['Stock value', '£'.number_format($stats['value'], 0), 'live price × quantity', 'bg-white'],
            ['Featured', number_format($stats['featured']), 'shown on home page', 'bg-white'],
            ['Sold out', number_format($stats['sold']), 'synced from eBay', 'bg-white'],
            ['Unread messages', number_format($stats['messages']), 'from contact form', $stats['messages'] ? 'bg-bug-100' : 'bg-white'],
            ['Subscribers', number_format($stats['subscribers']), 'Bug Club emails', 'bg-white'],
        ] as [$label, $value, $sub, $bg])
            <div class="rounded-[24px] border border-line {{ $bg }} p-5 shadow-[0_12px_32px_-18px_rgb(21_18_26/.12)]">
                <p class="text-xs font-bold uppercase tracking-wider text-ink/50">{{ $label }}</p>
                <p class="mt-2 font-display text-3xl font-semibold">{{ $value }}</p>
                <p class="mt-1 text-xs text-ink/50">{{ $sub }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <div class="card-pop p-6 xl:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-xl font-semibold">Recently added</h2>
                <a href="{{ route('admin.products.index') }}" class="text-sm font-bold underline">All products</a>
            </div>
            <div class="mt-4 divide-y divide-ink/10">
                @forelse ($recent as $p)
                    <a href="{{ route('admin.products.edit', $p) }}" class="flex items-center gap-4 py-3 hover:bg-cream/60">
                        <img src="{{ $p->image }}" alt="" class="h-14 w-14 rounded-xl border border-ink/10 object-cover">
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold">{{ $p->title }}</p>
                            <p class="text-xs text-ink/50">{{ ucfirst($p->source) }} · {{ $p->category?->name ?? 'Uncategorised' }} · {{ $p->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="font-display font-semibold">{{ $p->money() }}</span>
                    </a>
                @empty
                    <p class="py-8 text-center text-sm text-ink/50">No products yet. Connect eBay or add one manually.</p>
                @endforelse
            </div>
        </div>

        <div class="space-y-6">
            <div class="card-pop p-6">
                <h2 class="font-display text-xl font-semibold">eBay sync</h2>
                <p class="mt-2 text-sm">
                    @if ($ebayConnected)
                        <span class="font-bold text-leaf">● Connected</span>{{ $ebayUser ? ' as '.$ebayUser : '' }}
                    @else
                        <span class="font-bold text-bug-600">● Not connected</span>
                    @endif
                </p>
                @if ($lastSync)
                    <p class="mt-2 text-sm text-ink/60">Last run: {{ ucfirst($lastSync->type) }} — <b class="{{ $lastSync->status === 'failed' ? 'text-bug-600' : '' }}">{{ $lastSync->status }}</b>, {{ $lastSync->created_at->diffForHumans() }}</p>
                @endif
                @if ($ebayConnected)
                    <form action="{{ route('admin.ebay.sync') }}" method="post" class="mt-4">@csrf<button class="btn-sun btn-sm w-full">Sync now</button></form>
                @endif
            </div>

            <div class="card-pop p-6">
                <h2 class="font-display text-xl font-semibold">Most viewed</h2>
                <ol class="mt-3 space-y-2 text-sm">
                    @forelse ($topViewed as $p)
                        <li class="flex justify-between gap-3"><a class="truncate hover:underline" href="{{ route('admin.products.edit', $p) }}">{{ $p->title }}</a><b>{{ number_format($p->views) }}</b></li>
                    @empty
                        <li class="text-ink/50">No views yet.</li>
                    @endforelse
                </ol>
            </div>

            <div class="card-pop p-6">
                <div class="flex items-center justify-between">
                    <h2 class="font-display text-xl font-semibold">Messages</h2>
                    <a href="{{ route('admin.messages.index') }}" class="text-sm font-bold underline">All</a>
                </div>
                <div class="mt-3 space-y-2">
                    @forelse ($messages as $m)
                        <a href="{{ route('admin.messages.show', $m) }}" class="block rounded-2xl p-3 text-sm hover:bg-cream {{ $m->read_at ? '' : 'bg-bug-50' }}">
                            <p class="font-bold">{{ $m->name }} <span class="font-normal text-ink/50">· {{ $m->created_at->diffForHumans() }}</span></p>
                            <p class="truncate text-ink/60">{{ $m->subject ?: $m->message }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-ink/50">No messages yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

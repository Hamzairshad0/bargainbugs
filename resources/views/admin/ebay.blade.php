@extends('layouts.admin')

@section('title', 'eBay Sync')

@section('content')
    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            {{-- Connection --}}
            <div class="card-pop p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="font-display text-xl font-semibold">1. Connection</h2>
                        <p class="mt-2 text-sm">
                            @if ($connected)
                                <span class="font-bold text-leaf">● Connected</span>{{ $ebayUser ? ' as '.$ebayUser : '' }}
                                @if ($tokenExpires)<span class="text-ink/50"> · authorisation valid until {{ \Carbon\Carbon::parse($tokenExpires)->format('d M Y') }}</span>@endif
                            @elseif ($configured)
                                <span class="font-bold text-sun-dark">● API keys found — not connected yet</span>
                            @else
                                <span class="font-bold text-bug-600">● API keys missing</span>
                            @endif
                            <span class="ml-2 badge bg-ink/10">{{ $env }}</span>
                        </p>
                    </div>
                    <div class="flex gap-2">
                        @if ($configured)
                            <a href="{{ route('admin.ebay.connect') }}" class="btn-primary btn-sm">{{ $connected ? 'Reconnect' : 'Connect eBay account' }}</a>
                        @endif
                        @if ($connected)
                            <form action="{{ route('admin.ebay.disconnect') }}" method="post" data-confirm="Disconnect eBay? Products already on the site are kept.">@csrf<button class="btn-ghost btn-sm">Disconnect</button></form>
                        @endif
                    </div>
                </div>

                @unless ($configured)
                    <div class="mt-5 rounded-2xl bg-cream p-4 text-sm leading-6">
                        Add these to your <code class="font-bold">.env</code> (or Vercel → Settings → Environment Variables), then reload:
                        <pre class="mt-2 overflow-x-auto rounded-xl bg-ink p-3 text-xs text-white">EBAY_CLIENT_ID=your-App-ID
EBAY_CLIENT_SECRET=your-Cert-ID
EBAY_DEV_ID=your-Dev-ID
EBAY_RUNAME=your-RuName</pre>
                        Step-by-step guide: <b>EBAY_SETUP.md</b> in the project.
                    </div>
                @endunless

                <div class="mt-5 rounded-2xl border border-dashed border-ink/15 p-4 text-sm">
                    <p class="font-bold">Your RuName “Auth accepted URL” must be:</p>
                    <div class="mt-2 flex items-center gap-2">
                        <code class="flex-1 overflow-x-auto rounded-xl bg-cream px-3 py-2 text-xs">{{ $callbackUrl }}</code>
                        <button type="button" data-copy="{{ $callbackUrl }}" class="btn-ghost btn-sm !py-1.5"><span data-label>Copy</span></button>
                    </div>
                </div>
            </div>

            {{-- Sync --}}
            <div class="card-pop p-6">
                <h2 class="font-display text-xl font-semibold">2. Sync products</h2>
                <p class="mt-2 text-sm text-ink/60">
                    {{ number_format($ebayCount) }} products from eBay ·
                    Last full sync: <b>{{ $lastFull ? \Carbon\Carbon::parse($lastFull)->diffForHumans() : 'never' }}</b> ·
                    Last change check: <b>{{ $lastIncremental ? \Carbon\Carbon::parse($lastIncremental)->diffForHumans() : 'never' }}</b>
                </p>
                @if ($inProgress)
                    <p class="mt-3 rounded-2xl bg-sun/40 p-3 text-sm font-semibold">⏳ Full sync in progress — next page {{ $inProgress['page'] }} of {{ $inProgress['total_pages'] ?? '?' }}. Click “Sync now” to continue.</p>
                @endif
                <div class="mt-5 flex flex-wrap gap-3">
                    <form action="{{ route('admin.ebay.sync') }}" method="post">@csrf<button class="btn-sun" @disabled(! $connected)>🔄 Sync now (full)</button></form>
                    <form action="{{ route('admin.ebay.sync') }}" method="post">@csrf<input type="hidden" name="type" value="incremental"><button class="btn-ghost" @disabled(! $connected)>⚡ Quick sync (changes only)</button></form>
                    @if ($inProgress)
                        <form action="{{ route('admin.ebay.sync') }}" method="post">@csrf<input type="hidden" name="restart" value="1"><button class="btn-ghost">Restart from page 1</button></form>
                    @endif
                </div>

                <form action="{{ route('admin.ebay.import') }}" method="post" class="mt-6 flex flex-wrap gap-3 border-t border-ink/10 pt-5">
                    @csrf
                    <input name="item" placeholder="Import one item: paste eBay item number or URL" class="input min-w-[260px] flex-1">
                    <button class="btn-dark" @disabled(! $connected)>Import item</button>
                </form>
            </div>

            {{-- Automatic --}}
            <div class="card-pop p-6">
                <h2 class="font-display text-xl font-semibold">3. Automatic sync</h2>
                <p class="mt-2 text-sm leading-6 text-ink/70">
                    Hit this URL every 10–15 minutes and the site stays in step with eBay. Each call adds new listings, updates price and stock, and hides anything that sold or ended. It runs a full re-check once a day.
                </p>
                <div class="mt-3 flex items-center gap-2">
                    <code class="flex-1 overflow-x-auto rounded-xl bg-cream px-3 py-2 text-xs">{{ $cronUrl }}</code>
                    <button type="button" data-copy="{{ $cronUrl }}" class="btn-ghost btn-sm !py-1.5"><span data-label>Copy</span></button>
                </div>
                <ul class="mt-4 list-inside list-disc space-y-1 text-sm text-ink/70">
                    <li><b>Vercel:</b> the built-in cron in <code>vercel.json</code> runs it daily (Hobby plan limit). For every 15 minutes, add the URL above at <b>cron-job.org</b> (free).</li>
                    <li><b>Normal hosting / VPS:</b> add the cron line <code>* * * * * php artisan schedule:run</code>.</li>
                </ul>
            </div>
        </div>

        {{-- Logs --}}
        <div class="card-pop self-start p-6">
            <h2 class="font-display text-xl font-semibold">Sync history</h2>
            <div class="mt-4 space-y-3">
                @forelse ($logs as $log)
                    <div class="rounded-2xl border p-3 text-xs {{ $log->status === 'failed' ? 'border-bug-300 bg-bug-50' : ($log->status === 'running' ? 'border-sun bg-sun/10' : 'border-ink/10') }}">
                        <div class="flex justify-between font-bold">
                            <span>{{ ucfirst($log->type) }} · {{ $log->status }}</span>
                            <span class="text-ink/50">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="mt-1 text-ink/70">+{{ $log->created_count }} new · {{ $log->updated_count }} updated · {{ $log->ended_count }} ended</p>
                        @if ($log->message)<p class="mt-1 break-words text-ink/60">{{ $log->message }}</p>@endif
                    </div>
                @empty
                    <p class="text-sm text-ink/50">No syncs yet.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection

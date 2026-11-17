{{-- Product grid with filter chips, sort and "load more" (used on home + shop). Wrapper must have [data-catalog]. --}}
<div class="toolbar" data-reveal>
    <div class="chips">
        @foreach ($chips as [$label, $url, $count, $on])
            <a class="chip {{ $on ? 'on' : '' }}" href="{{ $url }}" data-chip>{{ $label }}<span>{{ $count }}</span></a>
        @endforeach
    </div>
    <div class="tools">
        {{ $toolsBefore ?? '' }}
        <label class="sel">Sort
            <select data-sort aria-label="Sort products">
                @foreach (\App\Http\Controllers\StoreController::SORTS as $k => $l)
                    <option value="{{ $k }}" @selected($sort === $k)>{{ $l }}</option>
                @endforeach
            </select>
        </label>
    </div>
</div>

<div class="grid-p" data-grid>
    @forelse ($products as $i => $p)
        <x-product-card :product="$p" :eager="$i < 4 && ($eagerFirst ?? false)" />
    @empty
        <div class="empty">
            <img src="{{ asset('images/logo.png') }}" alt="">
            <p class="serif" style="font-size:32px;color:var(--color-ink)">Fresh bargains landing soon</p>
            <p style="margin-top:6px">Our bugs are busy stocking the shelves. Check back shortly — or browse our eBay store.</p>
            <a class="btn btn-dark btn-sm" style="margin-top:20px" href="{{ setting('ebay_store_url') }}" target="_blank" rel="noopener">Visit our eBay store</a>
        </div>
    @endforelse
</div>

@php $shown = min($products->total(), ($products->currentPage() - 1) * $products->perPage() + $products->count()); @endphp
@if ($products->total() > 0)
    <div class="more">
        <p class="count">Showing <b data-shown>{{ $shown }}</b> of <b data-total>{{ $products->total() }}</b> bargains</p>
        <div class="pbar"><i data-bar style="width: {{ $products->total() ? $shown / $products->total() * 100 : 0 }}%"></i></div>
        <a class="btn btn-line" data-more href="{{ $products->nextPageUrl() ?? '#' }}" @if (! $products->hasMorePages()) hidden @endif>
            <span data-more-label>Load {{ min($products->perPage(), $products->total() - $shown) }} more</span> <span class="arr">↓</span>
        </a>
        <noscript>{{ $products->links() }}</noscript>
    </div>
@endif

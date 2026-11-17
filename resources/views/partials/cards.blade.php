{{-- /api/cards — wishlist drawer rows (list) or product cards (grid) --}}
@if ($layout === 'list')
    @forelse ($products as $p)
        <div class="wish-row">
            <a href="{{ route('product', $p) }}"><img src="{{ $p->thumb }}" alt="" loading="lazy"></a>
            <div style="flex:1;min-width:0">
                <a href="{{ route('product', $p) }}" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;font-weight:500;line-height:1.35">{{ $p->title }}</a>
                <p style="margin-top:4px"><b>{{ $p->money() }}</b> @if ($p->on_sale)<s style="color:var(--color-muted);font-size:13px">{{ $p->money($p->original_price) }}</s>@endif</p>
                @if ($p->status === 'active' && $p->buy_url)
                    <a href="{{ route('go', $p) }}" target="_blank" rel="noopener nofollow" class="ulink" style="font-size:13px;color:var(--color-tang)">Buy on eBay</a>
                @else
                    <span style="font-size:13px;color:var(--color-muted)">Sold out</span>
                @endif
            </div>
            <button type="button" class="ibtn" @click="$store.wish.toggle({{ $p->id }}); $el.closest('.wish-row').remove()" aria-label="Remove">
                <svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>
    @empty
        <p style="padding:40px 0;text-align:center;color:var(--color-muted)">Nothing here yet.</p>
    @endforelse
@else
    @foreach ($products as $p)
        <x-product-card :product="$p" />
    @endforeach
@endif

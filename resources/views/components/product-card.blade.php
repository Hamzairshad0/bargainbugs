@props(['product', 'eager' => false])
@php
    $p = $product;
    $soldOut = $p->status !== 'active' || $p->quantity < 1;
    $alt = $p->thumb2;
@endphp
<article class="card">
  <div class="media">
    <a href="{{ route('product', $p) }}" class="im" aria-label="{{ $p->title }}">
        <img src="{{ $p->thumb }}" alt="{{ $p->title }}" width="500" height="625" @if ($eager) fetchpriority="high" @else loading="lazy" @endif decoding="async"
             onerror="this.onerror=null;this.src='{{ asset('images/placeholder.svg') }}'">
        @if ($alt)<img class="alt" src="{{ $alt }}" alt="" loading="lazy" decoding="async" aria-hidden="true">@endif
        @if ($soldOut)
            <span class="badge sold">Sold out</span>
        @elseif ($p->on_sale)
            <span class="badge">-{{ $p->discount_percent }}%</span>
        @elseif ($p->is_new)
            <span class="badge new">New</span>
        @endif
        @if (! $soldOut && $p->quantity > 0 && $p->quantity <= 3)
            <span class="low">Only {{ $p->quantity }} left</span>
        @endif
    </a>
    <button type="button" class="heart" x-data :class="$store.wish.has({{ $p->id }}) && 'on'" @click="$store.wish.toggle({{ $p->id }})" aria-label="Save {{ $p->title }}">
        <svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M12 20s-7-4.4-9-9a5 5 0 0 1 9-3 5 5 0 0 1 9 3c-2 4.6-9 9-9 9z"/></svg>
    </button>
    @if (! $soldOut && $p->buy_url)
        <a class="buy" href="{{ route('go', $p) }}" target="_blank" rel="noopener nofollow">Buy on eBay →</a>
    @endif
  </div>
    <div class="info">
        <p class="c">{{ $p->category?->name ?? $p->condition ?? 'Bargain' }}</p>
        <h3><a href="{{ route('product', $p) }}">{{ $p->title }}</a></h3>
        <p class="pr"><b>{{ $p->money() }}</b>@if ($p->on_sale)<s>{{ $p->money($p->original_price) }}</s>@endif</p>
        @if ($p->quantity_sold > 0 || $p->watch_count > 2)
            <p class="rt">@if ($p->quantity_sold > 0){{ number_format($p->quantity_sold) }} sold @endif @if ($p->watch_count > 2)· {{ $p->watch_count }} watching @endif</p>
        @endif
    </div>
</article>

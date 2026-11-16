@props(['product', 'compact' => false])

<article class="group relative flex flex-col overflow-hidden rounded-3xl border-2 border-ink bg-white transition-all duration-200 hover:-translate-x-1 hover:-translate-y-1 hover:shadow-[7px_7px_0_0_var(--color-ink)] shadow-[4px_4px_0_0_var(--color-ink)]">
    <a href="{{ route('product', $product) }}" class="relative block aspect-square overflow-hidden bg-cream-dark">
        <img src="{{ $product->image }}" alt="{{ $product->title }}" loading="lazy"
             class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
             onerror="this.onerror=null;this.src='{{ asset('images/placeholder.svg') }}'">

        <div class="absolute left-3 top-3 flex flex-col items-start gap-1.5">
            @if ($product->on_sale)
                <span class="badge bg-bug-500 text-white border-2 border-ink">-{{ $product->discount_percent }}%</span>
            @endif
            @if ($product->is_new)
                <span class="badge bg-sun text-ink border-2 border-ink">New</span>
            @endif
        </div>

        @if ($product->status === 'active' && $product->quantity > 0 && $product->quantity <= 3)
            <span class="absolute bottom-3 left-3 badge bg-ink text-white">🔥 Only {{ $product->quantity }} left</span>
        @elseif ($product->status === 'sold')
            <span class="absolute inset-0 grid place-items-center bg-ink/50"><span class="badge bg-white text-ink text-sm">Sold out</span></span>
        @endif
    </a>

    <div class="flex flex-1 flex-col gap-2 p-4">
        @if (! $compact && $product->condition)
            <span class="text-[11px] font-bold uppercase tracking-wider text-ink/50">{{ $product->condition }}</span>
        @endif
        <h3 class="font-sans text-[15px] font-semibold leading-snug text-ink line-clamp-2">
            <a href="{{ route('product', $product) }}" class="after:absolute after:inset-0 hover:text-bug-600">{{ $product->title }}</a>
        </h3>
        <div class="mt-auto flex items-end justify-between gap-2 pt-1">
            <div class="flex items-baseline gap-2">
                <span class="font-display text-xl font-extrabold text-ink">{{ $product->money() }}</span>
                @if ($product->on_sale)
                    <span class="text-sm font-medium text-ink/40 line-through">{{ $product->money($product->original_price) }}</span>
                @endif
            </div>
            <span class="relative z-10 grid h-9 w-9 shrink-0 place-items-center rounded-full border-2 border-ink bg-sun transition-transform group-hover:rotate-[-45deg]" aria-hidden="true">
                @include('partials.icon', ['name' => 'arrow', 'class' => 'h-4 w-4'])
            </span>
        </div>
    </div>
</article>

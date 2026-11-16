@if ($paginator->hasPages())
    <nav class="flex flex-wrap items-center justify-center gap-2" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="grid h-11 w-11 place-items-center rounded-full border-2 border-ink/15 text-ink/30">@include('partials.icon', ['name' => 'left', 'class' => 'h-4 w-4'])</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="grid h-11 w-11 place-items-center rounded-full border-2 border-ink bg-white hover:bg-sun" aria-label="Previous">@include('partials.icon', ['name' => 'left', 'class' => 'h-4 w-4'])</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 font-bold text-ink/40">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="grid h-11 min-w-11 place-items-center rounded-full border-2 border-ink bg-ink px-3 text-sm font-bold text-white">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="grid h-11 min-w-11 place-items-center rounded-full border-2 border-ink/15 bg-white px-3 text-sm font-bold hover:border-ink">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="grid h-11 w-11 place-items-center rounded-full border-2 border-ink bg-white hover:bg-sun" aria-label="Next">@include('partials.icon', ['name' => 'right', 'class' => 'h-4 w-4'])</a>
        @else
            <span class="grid h-11 w-11 place-items-center rounded-full border-2 border-ink/15 text-ink/30">@include('partials.icon', ['name' => 'right', 'class' => 'h-4 w-4'])</span>
        @endif
    </nav>
@endif

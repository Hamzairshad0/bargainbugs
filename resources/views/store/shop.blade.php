@extends('layouts.store')

@section('title', strip_tags($title))
@section('description', $subtitle)

@section('content')
    <section class="page-head">
        <div class="wrap">
            <nav class="crumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span>@if ($category)<a href="{{ route('shop') }}">Shop</a><span>/</span><span>{{ $category->name }}</span>@else<span>Shop</span>@endif</nav>
            <h1 data-split data-now>{!! $title !!}</h1>
            <p class="sub" data-reveal>{{ $subtitle }}</p>
        </div>
    </section>

    <section style="padding-bottom:110px">
        <div class="wrap" data-catalog="push">
            @include('partials.catalog', [
                'products' => $products, 'chips' => $chips, 'sort' => $sort, 'eagerFirst' => true,
                'toolsBefore' => new \Illuminate\Support\HtmlString(
                    '<button type="button" class="chip" @click="$store.ui.open(\'filters\')">'.
                    '<svg class="icon icon-sm" viewBox="0 0 24 24" style="margin-right:6px"><path d="M4 6h16M7 12h10M10 18h4"/></svg>Filters'.
                    (request()->hasAny(['q', 'min', 'max', 'condition']) ? '<span>●</span>' : '').'</button>'
                ),
            ])
        </div>
    </section>

    {{-- Filters drawer --}}
    <aside class="drawer right" x-show="$store.ui.panel === 'filters'" x-cloak data-lenis-prevent
           x-transition:enter="transition duration-500 ease-[cubic-bezier(.16,1,.3,1)]" x-transition:enter-start="translate-x-full" x-transition:leave="transition duration-300" x-transition:leave-end="translate-x-full" aria-label="Filters">
        <div class="drawer-head"><b style="font-size:20px">Filters</b><button class="ibtn" @click="$store.ui.close()" aria-label="Close filters">@include('partials.icon', ['name' => 'close', 'class' => 'icon'])</button></div>
        <form class="drawer-body filter-box" method="get" action="{{ url()->current() }}" style="padding-top:22px">
            @foreach (['deals', 'new', 'sort'] as $keep)
                @if (request()->filled($keep))<input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}">@endif
            @endforeach
            <div><h4>Search</h4><input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Keyword…"></div>
            <div><h4>Price (£)</h4>
                <div style="display:flex;gap:10px;align-items:center">
                    <input class="input" type="number" name="min" min="0" step="1" value="{{ request('min') }}" placeholder="Min" inputmode="numeric">
                    <span style="color:var(--color-muted)">–</span>
                    <input class="input" type="number" name="max" min="0" step="1" value="{{ request('max') }}" placeholder="Max" inputmode="numeric">
                </div>
            </div>
            @if ($conditions->isNotEmpty())
                <div><h4>Condition</h4>
                    @foreach ($conditions as $c)
                        <label class="check"><input type="checkbox" name="condition[]" value="{{ $c }}" @checked(in_array($c, (array) request('condition', [])))> {{ $c }}</label>
                    @endforeach
                </div>
            @endif
            <div style="display:flex;gap:10px;margin-top:6px">
                <button class="btn btn-dark" style="flex:1">Show results</button>
                <a class="btn btn-line" href="{{ url()->current() }}">Reset</a>
            </div>
        </form>
    </aside>
@endsection

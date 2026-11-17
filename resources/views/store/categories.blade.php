@extends('layouts.store')

@section('title', 'Categories')

@section('content')
    <section class="page-head">
        <div class="wrap">
            <nav class="crumbs"><a href="{{ route('home') }}">Home</a><span>/</span><span>Categories</span></nav>
            <h1 data-split data-now>Shop by <em data-keep>category</em></h1>
            <p class="sub" data-reveal>Pick a department and dig in — new bargains land every week.</p>
        </div>
    </section>
    <section style="padding-bottom:110px">
        <div class="wrap">
            @if ($navCategories->isEmpty())
                <div class="empty"><img src="{{ asset('images/logo.png') }}" alt=""><p>Categories will appear here once products are added.</p></div>
            @else
                <div class="grid-p" style="row-gap:24px">
                    @foreach ($navCategories as $c)
                        <a class="cat" href="{{ route('category', $c) }}" data-reveal>
                            <div class="im">
                                @if ($c->cover)<img src="{{ $c->cover }}" alt="" loading="lazy" width="300" height="400">@else<span class="emoji">{{ $c->icon ?: '🛍️' }}</span>@endif
                                <span class="lbl">{{ $c->name }}<span class="cnt">{{ $c->products_count }} {{ Str::plural('item', $c->products_count) }}</span></span>
                                <span class="go">→</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection

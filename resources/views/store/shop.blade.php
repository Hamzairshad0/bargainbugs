@extends('layouts.store')

@section('title', $title)
@if ($category && $category->description)
    @section('description', $category->description)
@endif

@section('content')
    <section class="border-b-2 border-ink bg-white">
        <div class="container-x py-10 lg:py-14">
            <nav class="text-sm font-semibold text-ink/50" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-ink">Home</a> <span class="mx-1.5">/</span>
                @if ($category)
                    <a href="{{ route('shop') }}" class="hover:text-ink">Shop</a> <span class="mx-1.5">/</span> <span class="text-ink">{{ $category->name }}</span>
                @else
                    <span class="text-ink">{{ $title }}</span>
                @endif
            </nav>
            <div class="mt-3 flex flex-wrap items-end justify-between gap-4">
                <h1 class="font-display text-4xl font-extrabold sm:text-5xl">
                    @if ($category && $category->icon)<span class="mr-1">{{ $category->icon }}</span>@endif{{ $title }}
                </h1>
                <p class="font-semibold text-ink/55">{{ number_format($products->total()) }} {{ Str::plural('item', $products->total()) }}</p>
            </div>

            {{-- Category pills --}}
            @if ($navCategories->isNotEmpty())
                <div class="mt-6 flex gap-2 overflow-x-auto pb-1 scrollbar-none">
                    <a href="{{ route('shop') }}" class="chip shrink-0 !px-4 !py-2 {{ ! $category && request()->routeIs('shop') ? '!bg-ink text-white' : 'hover:bg-sun' }}">All</a>
                    <a href="{{ route('deals') }}" class="chip shrink-0 !px-4 !py-2 {{ request()->routeIs('deals') ? '!bg-ink text-white' : 'hover:bg-sun' }}">🔥 Deals</a>
                    @foreach ($navCategories as $cat)
                        <a href="{{ route('category', $cat) }}" class="chip shrink-0 !px-4 !py-2 {{ $category?->id === $cat->id ? '!bg-ink text-white' : 'hover:bg-sun' }}">{{ $cat->icon }} {{ $cat->name }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="container-x py-8 lg:py-10">
        {{-- Filters --}}
        <form method="get" class="mb-8 flex flex-wrap items-center gap-3 rounded-3xl border-2 border-ink bg-white p-3 shadow-[4px_4px_0_0_var(--color-ink)]">
            <div class="flex min-w-[220px] flex-1 items-center gap-2 rounded-2xl bg-cream px-4">
                @include('partials.icon', ['name' => 'search', 'class' => 'h-4 w-4 text-ink/40'])
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search in {{ $category?->name ?? 'all bargains' }}…" class="w-full bg-transparent py-3 text-sm font-medium outline-none">
            </div>
            @if ($conditions->count() > 1)
                <select name="condition" data-autosubmit class="rounded-2xl bg-cream px-4 py-3 text-sm font-semibold outline-none" aria-label="Condition">
                    <option value="">Any condition</option>
                    @foreach ($conditions as $c)
                        <option value="{{ $c }}" @selected(request('condition') === $c)>{{ $c }}</option>
                    @endforeach
                </select>
            @endif
            <div class="flex items-center gap-1 rounded-2xl bg-cream px-3 text-sm font-semibold">
                <span class="text-ink/50">£</span>
                <input type="number" name="min" value="{{ request('min') }}" placeholder="Min" min="0" step="1" class="w-16 bg-transparent py-3 outline-none" aria-label="Minimum price">
                <span class="text-ink/30">–</span>
                <input type="number" name="max" value="{{ request('max') }}" placeholder="Max" min="0" step="1" class="w-16 bg-transparent py-3 outline-none" aria-label="Maximum price">
            </div>
            <select name="sort" data-autosubmit class="rounded-2xl bg-cream px-4 py-3 text-sm font-semibold outline-none" aria-label="Sort">
                @foreach (['featured' => 'Featured', 'newest' => 'Newest', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low', 'popular' => 'Most popular'] as $k => $l)
                    <option value="{{ $k }}" @selected($sort === $k)>{{ $l }}</option>
                @endforeach
            </select>
            <button class="btn-dark btn-sm !py-3">Apply</button>
            @if (request()->hasAny(['q', 'condition', 'min', 'max']))
                <a href="{{ url()->current() }}" class="px-2 text-sm font-bold text-bug-600 underline">Clear</a>
            @endif
        </form>

        @if ($products->isEmpty())
            <div class="rounded-[2rem] border-2 border-dashed border-ink/25 bg-white py-20 text-center">
                <img src="{{ asset('images/logo-mark.svg') }}" alt="" class="mx-auto h-20 w-20 animate-wiggle">
                <h2 class="mt-5 font-display text-2xl font-extrabold">No bargains found</h2>
                <p class="mt-2 text-ink/60">Try a different search or browse everything.</p>
                <a href="{{ route('shop') }}" class="btn-primary mt-6">Browse all bargains</a>
            </div>
        @else
            <div class="grid grid-cols-2 gap-4 sm:gap-6 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
            <div class="mt-12">{{ $products->links() }}</div>
        @endif
    </section>
@endsection

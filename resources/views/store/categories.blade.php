@extends('layouts.store')

@section('title', 'Categories')

@section('content')
    <section class="border-b-2 border-ink bg-white">
        <div class="container-x py-12 text-center lg:py-16">
            <p class="text-sm font-extrabold uppercase tracking-widest text-bug-500">Browse</p>
            <h1 class="mt-2 font-display text-4xl font-extrabold sm:text-5xl">All categories</h1>
            <p class="mx-auto mt-3 max-w-lg text-ink/60">Pick a category and dig in — new bargains land every week.</p>
        </div>
    </section>

    <section class="container-x py-12">
        @if ($navCategories->isEmpty())
            <p class="py-16 text-center text-ink/60">Categories will appear here once products are added.</p>
        @else
            @php $tileColors = ['bg-sun', 'bg-bug-100', 'bg-white', 'bg-[#d9f2e8]', 'bg-[#e3e8ff]', 'bg-cream-dark']; @endphp
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-6 lg:grid-cols-4">
                @foreach ($navCategories as $i => $cat)
                    <a href="{{ route('category', $cat) }}" class="reveal group flex flex-col items-center gap-3 rounded-[2rem] border-2 border-ink {{ $tileColors[$i % 6] }} p-8 text-center shadow-[5px_5px_0_0_var(--color-ink)] transition hover:-translate-y-1 hover:shadow-[7px_8px_0_0_var(--color-ink)]">
                        @if ($cat->image_url)
                            <img src="{{ $cat->image_url }}" alt="" class="h-20 w-20 rounded-2xl object-cover">
                        @else
                            <span class="text-6xl transition-transform group-hover:scale-110 group-hover:-rotate-6">{{ $cat->icon ?: '🛍️' }}</span>
                        @endif
                        <span class="font-display text-lg font-extrabold leading-tight">{{ $cat->name }}</span>
                        <span class="chip">{{ $cat->products_count }} {{ Str::plural('item', $cat->products_count) }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
@endsection

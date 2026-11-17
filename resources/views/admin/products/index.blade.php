@extends('layouts.admin')

@section('title', 'Products')
@section('actions')
    <a href="{{ route('admin.products.create') }}" class="btn-primary btn-sm">+ Add product</a>
@endsection

@section('content')
    {{-- Status tabs --}}
    <div class="mb-4 flex gap-2 overflow-x-auto pb-1 scrollbar-none">
        @foreach (['' => 'All', 'active' => 'Active', 'sold' => 'Sold', 'ended' => 'Ended', 'featured' => 'Featured', 'hidden' => 'Hidden'] as $k => $l)
            <a href="{{ route('admin.products.index', array_filter(['status' => $k] + request()->except('status', 'page'))) }}"
               class="chip shrink-0 !px-4 !py-2 {{ (string) request('status') === (string) $k ? '!bg-ink text-white' : 'hover:bg-sun' }}">
                {{ $l }} <span class="opacity-60">{{ $counts[$k ?: 'all'] }}</span>
            </a>
        @endforeach
    </div>

    <form method="get" class="mb-4 flex flex-wrap gap-3">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search title or eBay item number…" class="input !w-auto min-w-[260px] flex-1">
        <select name="category" data-autosubmit class="input !w-auto">
            <option value="">All categories</option>
            @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
        <select name="source" data-autosubmit class="input !w-auto">
            <option value="">eBay + manual</option>
            <option value="ebay" @selected(request('source') === 'ebay')>From eBay</option>
            <option value="manual" @selected(request('source') === 'manual')>Manual</option>
        </select>
        <select name="sort" data-autosubmit class="input !w-auto">
            @foreach (['newest' => 'Newest', 'views' => 'Most viewed', 'clicks' => 'Most clicked', 'price' => 'Highest price', 'title' => 'A–Z'] as $k => $l)
                <option value="{{ $k }}" @selected(request('sort', 'newest') === $k)>{{ $l }}</option>
            @endforeach
        </select>
        <button class="btn-dark btn-sm">Filter</button>
    </form>

    <form action="{{ route('admin.products.bulk') }}" method="post">
        @csrf
        <div class="mb-3 flex flex-wrap items-center gap-2 rounded-2xl border border-ink/10 bg-white p-3 text-sm">
            <span class="font-bold">With selected:</span>
            <select name="action" class="rounded-xl border border-ink/15 px-3 py-2 font-semibold">
                <option value="feature">⭐ Feature on home page</option>
                <option value="unfeature">Remove from featured</option>
                <option value="hide">🙈 Hide from website</option>
                <option value="show">👁️ Show on website</option>
                <option value="category">📁 Move to category…</option>
                <option value="delete">🗑️ Delete</option>
            </select>
            <select name="category_id" class="rounded-xl border border-ink/15 px-3 py-2 font-semibold">
                <option value="">(category for “Move to”)</option>
                @foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
            </select>
            <button class="btn-dark btn-sm" data-confirm="Apply this action to the selected products?">Apply</button>
        </div>

        <div class="overflow-x-auto rounded-[24px] border border-line bg-white shadow-[0_12px_32px_-18px_rgb(21_18_26/.12)]">
            <table class="w-full min-w-[900px] text-sm">
                <thead class="border-b border-line bg-bg text-left text-[11px] font-semibold uppercase tracking-wider text-ink/60">
                    <tr>
                        <th class="w-10 p-4"><input type="checkbox" data-check-all=".row-check" class="h-4 w-4 accent-bug-500" aria-label="Select all"></th>
                        <th class="p-4">Product</th>
                        <th class="p-4">Price</th>
                        <th class="p-4">Stock</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Views / clicks</th>
                        <th class="p-4 text-center">Featured</th>
                        <th class="p-4 text-center">Visible</th>
                        <th class="p-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink/10">
                    @forelse ($products as $p)
                        <tr class="hover:bg-cream/50 {{ $p->is_hidden ? 'opacity-60' : '' }}">
                            <td class="p-4"><input type="checkbox" name="ids[]" value="{{ $p->id }}" class="row-check h-4 w-4 accent-bug-500" aria-label="Select {{ $p->title }}"></td>
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $p->image }}" alt="" class="h-12 w-12 shrink-0 rounded-xl border border-ink/10 object-cover" loading="lazy">
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.products.edit', $p) }}" class="line-clamp-1 max-w-md font-semibold hover:text-bug-600">{{ $p->title }}</a>
                                        <p class="text-xs text-ink/50">
                                            {{ $p->source === 'ebay' ? 'eBay #'.$p->ebay_item_id : 'Manual' }} · {{ $p->category?->name ?? 'Uncategorised' }}
                                            @if ($p->lock_content) · 🔒 locked @endif
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 font-bold">{{ $p->money() }}@if ($p->on_sale)<br><span class="text-xs font-normal text-ink/40 line-through">{{ $p->money($p->original_price) }}</span>@endif</td>
                            <td class="p-4">{{ $p->quantity }}</td>
                            <td class="p-4">
                                <span class="badge {{ ['active' => 'bg-[#d9f2e8] text-[#136b4f]', 'sold' => 'bg-sun text-ink', 'ended' => 'bg-ink/10 text-ink/60'][$p->status] ?? 'bg-ink/10' }}">{{ $p->status }}</span>
                            </td>
                            <td class="p-4 text-ink/70">{{ number_format($p->views) }} / {{ number_format($p->clicks) }}</td>
                            <td class="p-4 text-center">
                                <button form="toggle-feat-{{ $p->id }}" class="text-xl transition hover:scale-125" title="Toggle featured">{{ $p->is_featured ? '⭐' : '☆' }}</button>
                            </td>
                            <td class="p-4 text-center">
                                <button form="toggle-hide-{{ $p->id }}" class="text-xl transition hover:scale-125" title="Toggle visibility">{{ $p->is_hidden ? '🙈' : '👁️' }}</button>
                            </td>
                            <td class="p-4 text-right whitespace-nowrap">
                                <a href="{{ route('product', $p) }}" target="_blank" class="mr-2 text-xs font-bold text-ink/50 hover:text-ink">View</a>
                                <a href="{{ route('admin.products.edit', $p) }}" class="btn-ghost btn-sm !py-1.5">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="p-12 text-center text-ink/50">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>

    {{-- toggle forms live outside the bulk form (forms can't be nested) --}}
    @foreach ($products as $p)
        <form id="toggle-feat-{{ $p->id }}" action="{{ route('admin.products.toggle', [$p, 'is_featured']) }}" method="post" class="hidden">@csrf</form>
        <form id="toggle-hide-{{ $p->id }}" action="{{ route('admin.products.toggle', [$p, 'is_hidden']) }}" method="post" class="hidden">@csrf</form>
    @endforeach

    <div class="mt-8">{{ $products->links() }}</div>
@endsection

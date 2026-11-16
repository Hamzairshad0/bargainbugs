@extends('layouts.admin')

@section('title', 'Categories')
@section('actions')
    <a href="{{ route('admin.categories.create') }}" class="btn-primary btn-sm">+ Add category</a>
@endsection

@section('content')
    <p class="mb-4 text-sm text-ink/60">Categories are created automatically from your eBay listings (top-level eBay category). Rename them, add an emoji, reorder or hide them here — your changes are kept on future syncs.</p>

    <div class="overflow-x-auto rounded-3xl border-2 border-ink bg-white shadow-[4px_4px_0_0_var(--color-ink)]">
        <table class="w-full min-w-[700px] text-sm">
            <thead class="border-b-2 border-ink bg-cream text-left text-xs font-bold uppercase tracking-wider text-ink/60">
                <tr><th class="p-4">Category</th><th class="p-4">Maps from eBay</th><th class="p-4">Products</th><th class="p-4">Order</th><th class="p-4">Visible</th><th class="p-4"></th></tr>
            </thead>
            <tbody class="divide-y divide-ink/10">
                @forelse ($categories as $c)
                    <tr class="hover:bg-cream/50">
                        <td class="p-4"><span class="mr-2 text-2xl">{{ $c->icon }}</span><b>{{ $c->name }}</b> <span class="text-xs text-ink/40">/{{ $c->slug }}</span></td>
                        <td class="p-4 text-ink/60">{{ $c->ebay_key ?: '—' }}</td>
                        <td class="p-4">{{ $c->products_count }}</td>
                        <td class="p-4">{{ $c->sort_order }}</td>
                        <td class="p-4">{{ $c->is_visible ? '👁️' : '🙈' }}</td>
                        <td class="p-4 text-right whitespace-nowrap">
                            <a href="{{ route('admin.categories.edit', $c) }}" class="btn-ghost btn-sm !py-1.5">Edit</a>
                            <form action="{{ route('admin.categories.destroy', $c) }}" method="post" class="inline" data-confirm="Delete “{{ $c->name }}”? Products in it become uncategorised.">
                                @csrf @method('DELETE')
                                <button class="ml-2 text-xs font-bold text-bug-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-12 text-center text-ink/50">No categories yet — they appear after your first eBay sync.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

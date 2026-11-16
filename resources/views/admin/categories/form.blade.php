@extends('layouts.admin')

@section('title', $category->exists ? 'Edit category' : 'Add category')

@section('content')
    <form action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" method="post" class="card-pop grid max-w-2xl gap-5 p-6">
        @csrf
        @if ($category->exists) @method('PUT') @endif

        <div class="grid grid-cols-[100px_1fr] gap-4">
            <label class="grid gap-2 text-sm font-bold">Emoji
                <input name="icon" value="{{ old('icon', $category->icon) }}" class="input text-center !text-2xl" maxlength="16">
            </label>
            <label class="grid gap-2 text-sm font-bold">Name
                <input name="name" value="{{ old('name', $category->name) }}" required class="input">
            </label>
        </div>
        <label class="grid gap-2 text-sm font-bold">URL slug <span class="font-normal text-ink/50">(leave empty to auto-generate)</span>
            <input name="slug" value="{{ old('slug', $category->slug) }}" class="input font-mono !text-xs">
        </label>
        <label class="grid gap-2 text-sm font-bold">Image URL <span class="font-normal text-ink/50">(optional — replaces the emoji on tiles)</span>
            <input type="url" name="image_url" value="{{ old('image_url', $category->image_url) }}" class="input">
        </label>
        <label class="grid gap-2 text-sm font-bold">Description <span class="font-normal text-ink/50">(used for SEO)</span>
            <textarea name="description" rows="3" class="input">{{ old('description', $category->description) }}</textarea>
        </label>
        <label class="grid gap-2 text-sm font-bold">eBay category it maps from
            <input name="ebay_key" value="{{ old('ebay_key', $category->ebay_key) }}" class="input" placeholder="e.g. Home, Furniture & DIY">
            <span class="text-xs font-normal text-ink/50">New eBay items in this top-level eBay category are put here automatically.</span>
        </label>
        <label class="grid gap-2 text-sm font-bold">Sort order <span class="font-normal text-ink/50">(lower = first)</span>
            <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="input">
        </label>
        <label class="flex items-center gap-3 text-sm font-bold"><input type="checkbox" name="is_visible" value="1" @checked(old('is_visible', $category->is_visible ?? true)) class="h-5 w-5 accent-bug-500"> Show in menus and on the home page</label>
        <div class="flex gap-3">
            <button class="btn-primary">Save category</button>
            <a href="{{ route('admin.categories.index') }}" class="btn-ghost">Cancel</a>
        </div>
    </form>
@endsection

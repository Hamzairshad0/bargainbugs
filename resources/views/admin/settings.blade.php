@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
    <div class="grid gap-6 xl:grid-cols-3">
        <form action="{{ route('admin.settings.update') }}" method="post" class="space-y-6 xl:col-span-2">
            @csrf
            @foreach ($groups as $group => $fields)
                <div class="card-pop grid gap-4 p-6">
                    <h2 class="font-display text-lg font-semibold">{{ $group }}</h2>
                    @foreach ($fields as $key => [$label, $type])
                        <label class="grid gap-2 text-sm font-bold">{{ $label }}
                            @if ($type === 'textarea')
                                <textarea name="{{ $key }}" rows="{{ $key === 'about_text' ? 7 : 3 }}" class="input" placeholder="{{ \App\Models\Setting::DEFAULTS[$key] ?? '' }}">{{ old($key, \App\Models\Setting::allCached()[$key] ?? '') }}</textarea>
                            @else
                                <input type="{{ $type }}" name="{{ $key }}" value="{{ old($key, \App\Models\Setting::allCached()[$key] ?? '') }}" class="input" placeholder="{{ \App\Models\Setting::DEFAULTS[$key] ?? '' }}">
                            @endif
                        </label>
                    @endforeach
                </div>
            @endforeach
            <p class="text-xs text-ink/50">Empty fields use the default text (shown greyed out).</p>
            <button class="btn-primary">Save settings</button>
        </form>

        <div class="space-y-6">
            <form action="{{ route('admin.account') }}" method="post" class="card-pop grid gap-4 p-6">
                @csrf
                <h2 class="font-display text-lg font-semibold">Your admin login</h2>
                <label class="grid gap-2 text-sm font-bold">Email
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required class="input">
                </label>
                <label class="grid gap-2 text-sm font-bold">New password <span class="font-normal text-ink/50">(leave empty to keep)</span>
                    <input type="password" name="password" class="input" autocomplete="new-password">
                </label>
                <label class="grid gap-2 text-sm font-bold">Confirm new password
                    <input type="password" name="password_confirmation" class="input" autocomplete="new-password">
                </label>
                <label class="grid gap-2 text-sm font-bold">Current password
                    <input type="password" name="current_password" required class="input" autocomplete="current-password">
                </label>
                <button class="btn-dark">Update login</button>
            </form>

            <div class="card-pop p-6 text-sm leading-6">
                <h2 class="font-display text-lg font-semibold">Social media tips 📱</h2>
                <ul class="mt-3 list-inside list-disc space-y-2 text-ink/70">
                    <li>Put <b>{{ route('links') }}</b> in your Instagram & TikTok bio — it’s a ready-made link page.</li>
                    <li>Every product link has a preview card (photo, title, price) when shared on WhatsApp, Facebook, X & Instagram DMs.</li>
                    <li>Use ⭐ Featured in Products to choose what shows on the home page.</li>
                </ul>
            </div>
        </div>
    </div>
@endsection

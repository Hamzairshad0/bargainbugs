@extends('layouts.store')

@section('title', 'Contact us')

@section('content')
    <section class="container-x py-14 lg:py-20">
        <div class="grid gap-10 lg:grid-cols-[1fr_1.3fr]">
            <div>
                <p class="text-sm font-extrabold uppercase tracking-widest text-bug-500">Say hello</p>
                <h1 class="mt-2 font-display text-5xl font-extrabold leading-tight">We’d love to hear from you</h1>
                <p class="mt-4 max-w-md text-lg text-ink/65">Questions about an item, bulk orders or collaborations? Drop us a message and our team will get back to you.</p>

                <div class="mt-8 space-y-3">
                    @if (setting('contact_email'))
                        <a href="mailto:{{ setting('contact_email') }}" class="card-pop flex items-center gap-4 p-4 transition hover:-translate-y-0.5">
                            <span class="grid h-11 w-11 place-items-center rounded-2xl bg-sun">@include('partials.icon', ['name' => 'mail'])</span>
                            <span><span class="block text-xs font-bold uppercase text-ink/50">Email</span><span class="font-bold">{{ setting('contact_email') }}</span></span>
                        </a>
                    @endif
                    @if (setting('whatsapp'))
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('whatsapp')) }}" target="_blank" rel="noopener" class="card-pop flex items-center gap-4 p-4 transition hover:-translate-y-0.5">
                            <span class="grid h-11 w-11 place-items-center rounded-2xl bg-[#25D366] text-white">@include('partials.icon', ['name' => 'whatsapp'])</span>
                            <span><span class="block text-xs font-bold uppercase text-ink/50">WhatsApp</span><span class="font-bold">{{ setting('whatsapp') }}</span></span>
                        </a>
                    @endif
                    <a href="{{ setting('ebay_store_url') }}" target="_blank" rel="noopener" class="card-pop flex items-center gap-4 p-4 transition hover:-translate-y-0.5">
                        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-bug-500 text-white">@include('partials.icon', ['name' => 'bag'])</span>
                        <span><span class="block text-xs font-bold uppercase text-ink/50">eBay</span><span class="font-bold">Message us on eBay</span></span>
                    </a>
                </div>
            </div>

            <div class="card-pop p-6 sm:p-10">
                @if (session('success'))
                    <div class="mb-6 rounded-2xl border-2 border-leaf bg-[#e6f7f0] p-4 font-semibold text-leaf">{{ session('success') }}</div>
                @endif
                <form action="{{ route('contact.send') }}" method="post" class="grid gap-5">
                    @csrf
                    <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <label class="grid gap-2 text-sm font-bold">Your name
                            <input name="name" value="{{ old('name') }}" required class="input" autocomplete="name">
                            @error('name')<span class="text-bug-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="grid gap-2 text-sm font-bold">Email
                            <input type="email" name="email" value="{{ old('email') }}" required class="input" autocomplete="email">
                            @error('email')<span class="text-bug-600">{{ $message }}</span>@enderror
                        </label>
                    </div>
                    <label class="grid gap-2 text-sm font-bold">Subject
                        <input name="subject" value="{{ old('subject') }}" class="input" placeholder="e.g. Question about an item">
                    </label>
                    <label class="grid gap-2 text-sm font-bold">Message
                        <textarea name="message" rows="6" required class="input">{{ old('message') }}</textarea>
                        @error('message')<span class="text-bug-600">{{ $message }}</span>@enderror
                    </label>
                    <button class="btn-primary justify-self-start !px-8 !py-4">Send message @include('partials.icon', ['name' => 'arrow', 'class' => 'h-4 w-4'])</button>
                </form>
            </div>
        </div>
    </section>
@endsection

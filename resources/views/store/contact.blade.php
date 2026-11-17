@extends('layouts.store')

@section('title', 'Contact us')

@section('content')
    <section class="page-head" style="padding-bottom:110px">
        <div class="wrap two">
            <div>
                <nav class="crumbs"><a href="{{ route('home') }}">Home</a><span>/</span><span>Contact</span></nav>
                <h1 data-split data-now>Say <em data-keep>hello.</em></h1>
                <p class="sub" data-reveal>Questions about an item, bulk orders or collaborations? Drop us a message and we’ll get back to you, usually within 24 hours.</p>
                <div style="display:grid;gap:12px;margin-top:36px" data-reveal>
                    @if (setting('contact_email'))
                        <a class="contact-card" href="mailto:{{ setting('contact_email') }}">@include('partials.icon', ['name' => 'mail', 'class' => 'icon'])<span><small style="display:block;color:var(--color-muted);font-size:12px">Email</small><b>{{ setting('contact_email') }}</b></span></a>
                    @endif
                    @if (setting('whatsapp'))
                        <a class="contact-card" href="https://wa.me/{{ preg_replace('/\D/', '', setting('whatsapp')) }}" target="_blank" rel="noopener">@include('partials.icon', ['name' => 'whatsapp', 'class' => 'icon'])<span><small style="display:block;color:var(--color-muted);font-size:12px">WhatsApp</small><b>{{ setting('whatsapp') }}</b></span></a>
                    @endif
                    <a class="contact-card" href="{{ setting('ebay_store_url') }}" target="_blank" rel="noopener">@include('partials.icon', ['name' => 'bag', 'class' => 'icon'])<span><small style="display:block;color:var(--color-muted);font-size:12px">eBay</small><b>Message us on eBay</b></span></a>
                </div>
            </div>
            <form action="{{ route('contact.send') }}" method="post" style="display:grid;gap:16px;background:#fff;border:1px solid var(--color-line);border-radius:28px;padding:clamp(22px,4vw,40px)" data-reveal>
                @csrf
                @if (session('success'))<div class="alert ok">{{ session('success') }}</div>@endif
                <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
                <label style="display:grid;gap:8px;font-size:14px;font-weight:500">Your name<input class="input" name="name" value="{{ old('name') }}" required autocomplete="name">@error('name')<span style="color:var(--color-tang)">{{ $message }}</span>@enderror</label>
                <label style="display:grid;gap:8px;font-size:14px;font-weight:500">Email<input class="input" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">@error('email')<span style="color:var(--color-tang)">{{ $message }}</span>@enderror</label>
                <label style="display:grid;gap:8px;font-size:14px;font-weight:500">Subject<input class="input" name="subject" value="{{ old('subject') }}" placeholder="e.g. Question about an item"></label>
                <label style="display:grid;gap:8px;font-size:14px;font-weight:500">Message<textarea class="input" name="message" rows="6" required>{{ old('message') }}</textarea>@error('message')<span style="color:var(--color-tang)">{{ $message }}</span>@enderror</label>
                <button class="btn btn-dark" style="justify-self:start">Send message <span class="arr">→</span></button>
            </form>
        </div>
    </section>
@endsection

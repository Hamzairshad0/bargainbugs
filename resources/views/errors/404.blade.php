@extends('layouts.store')

@section('title', 'Page not found')
@section('hide_newsletter', '1')

@section('content')
    <section class="container-x py-24 text-center">
        <img src="{{ asset('images/logo-mark.svg') }}" alt="" class="mx-auto h-28 w-28 animate-wiggle">
        <p class="mt-6 font-display text-8xl font-extrabold text-bug-500">404</p>
        <h1 class="mt-2 font-display text-3xl font-extrabold">This bug crawled off somewhere…</h1>
        <p class="mx-auto mt-3 max-w-md text-ink/60">The page or item you’re looking for isn’t here any more — it may have sold out. Plenty more bargains are waiting though!</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('shop') }}" class="btn-primary">Browse bargains</a>
            <a href="{{ route('home') }}" class="btn-ghost">Back home</a>
        </div>
    </section>
@endsection

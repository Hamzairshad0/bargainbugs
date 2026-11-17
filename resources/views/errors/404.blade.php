@extends('layouts.store')

@section('title', 'Page not found')
@section('hide_newsletter', '1')

@section('content')
    <section class="page-head" style="padding:80px 0 120px;text-align:center">
        <div class="wrap">
            <img src="{{ asset('images/logo.png') }}" alt="" width="120" height="120" style="margin:0 auto">
            <p class="serif" style="margin-top:20px;font-size:clamp(90px,16vw,180px);line-height:.8;color:var(--color-tang)">404</p>
            <h1 style="font-size:clamp(32px,4vw,52px)">This bug crawled <em>off</em> somewhere…</h1>
            <p class="sub" style="margin-inline:auto">The page or item you’re after isn’t here any more — it may have sold out. Plenty more bargains are waiting though.</p>
            <div class="cta" style="justify-content:center"><a class="btn btn-dark" href="{{ route('shop') }}">Browse bargains <span class="arr">→</span></a><a class="ulink" href="{{ route('home') }}">Back home</a></div>
        </div>
    </section>
@endsection

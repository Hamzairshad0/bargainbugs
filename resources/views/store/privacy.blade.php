@extends('layouts.store')

@section('title', 'Privacy policy')
@section('hide_newsletter', '1')

@section('content')
    <section class="page-head" style="padding-bottom:110px">
      <div class="wrap" style="max-width:860px">
        <nav class="crumbs"><a href="{{ route('home') }}">Home</a><span>/</span><span>Privacy</span></nav>
        <h1>Privacy <em>policy</em></h1>
        <p class="sub">Last updated {{ now()->format('F Y') }}</p>
        <div class="prose-bb" style="margin-top:36px;font-size:16.5px">
            <p>{{ setting('site_name') }} (“we”, “us”) runs this website to showcase products sold through our eBay store. This policy explains what information we collect and how we use it.</p>
            <h2>What we collect</h2>
            <ul>
                <li><b>Contact form:</b> your name, email address and message, so we can reply to you.</li>
                <li><b>Newsletter:</b> your email address, if you choose to join our mailing list. You can ask us to remove it at any time.</li>
                <li><b>Basic usage data:</b> anonymous page-view and click counts to understand which products are popular. We don’t use advertising trackers.</li>
            </ul>
            <h2>Purchases</h2>
            <p>All purchases are completed on eBay. We never see or store your payment details. eBay’s own privacy notice applies to everything you do on eBay.</p>
            <h2>eBay data</h2>
            <p>We use the eBay API only to display our own listings (titles, photos, prices and stock) on this website. We do not collect or store data about eBay buyers.</p>
            <h2>Your rights</h2>
            <p>Under UK GDPR you can ask to see, correct or delete the personal data we hold about you. Email us at {{ setting('contact_email') }} and we’ll help.</p>
            <h2>Contact</h2>
            <p>{{ setting('site_name') }} · {{ setting('address') }} · {{ setting('contact_email') }}</p>
        </div>
      </div>
    </section>
@endsection

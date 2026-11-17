<div class="topbar">Every order protected by the <b>eBay Money Back Guarantee</b><span class="hidden sm:inline"> · Fast UK dispatch</span></div>

<header class="site-header {{ request()->routeIs('home') ? '' : 'solid' }}" data-header>
    <div class="wrap hdr">
        <nav aria-label="Main">
            <a href="{{ route('shop') }}" class="{{ request()->routeIs('shop', 'category') ? 'on' : '' }}">Shop</a>
            <a href="{{ route('deals') }}" class="{{ request()->routeIs('deals') ? 'on' : '' }}">Price drops</a>
            <a href="{{ route('new') }}" class="{{ request()->routeIs('new') ? 'on' : '' }}">New in</a>
            <a href="{{ route('categories') }}" class="{{ request()->routeIs('categories') ? 'on' : '' }}">Categories</a>
            <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'on' : '' }}">About</a>
        </nav>
        <button type="button" class="ibtn burger" @click="$store.ui.open('menu')" aria-label="Open menu">
            <svg class="icon" viewBox="0 0 24 24"><path d="M4 8h16M4 16h16"/></svg>
        </button>
        <a class="logo" href="{{ route('home') }}" aria-label="{{ setting('site_name') }} home">
            <img src="{{ asset('images/logo.png') }}" alt="{{ setting('site_name') }}" width="52" height="52">
            <span>Bargain <i>Bugs</i></span>
        </a>
        <div class="right">
            <button type="button" class="ibtn" @click="$store.ui.open('search')" aria-label="Search">@include('partials.icon', ['name' => 'search', 'class' => 'icon'])</button>
            <button type="button" class="ibtn" @click="$store.ui.open('wish')" aria-label="Wishlist">
                @include('partials.icon', ['name' => 'heart', 'class' => 'icon'])
                <span class="ct" x-show="$store.wish.ids.length" x-cloak x-text="$store.wish.ids.length"></span>
            </button>
            <a class="pill" href="{{ setting('ebay_store_url') }}" target="_blank" rel="noopener" aria-label="Our eBay store">
                @include('partials.icon', ['name' => 'bag', 'class' => 'icon icon-sm'])<span>eBay Store</span>
            </a>
        </div>
    </div>
</header>

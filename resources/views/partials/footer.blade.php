@unless (View::hasSection('hide_newsletter'))
    <section class="pad news">
        <div class="wrap">
            <h2 data-split>Bargains, <em data-keep>first.</em></h2>
            <p data-reveal>Join the list for new drops and subscriber-only deals. One email a week, no spam.</p>
            <form class="nform" action="{{ route('subscribe') }}" method="post" data-reveal>
                @csrf
                <label for="nl-email" class="sr-only">Email address</label>
                <input id="nl-email" type="email" name="email" required placeholder="Your email address" autocomplete="email">
                <button>Subscribe →</button>
            </form>
            @if (session('subscribed'))<p class="flash">✓ {{ session('subscribed') }}</p>@endif
            @error('email')<p class="flash" style="color:var(--color-tang)">{{ $message }}</p>@enderror
        </div>
    </section>
@endunless

<footer class="site-footer">
    <div class="wrap">
        <div class="fgrid">
            <div>
                <a class="logo" href="{{ route('home') }}"><img src="{{ asset('images/logo.png') }}" alt="" width="52" height="52" loading="lazy"><span>Bargain <i>Bugs</i></span></a>
                <p>{{ setting('tagline') }} A UK seller you can trust — every order completed securely on eBay.</p>
                <div class="pay">@foreach (['PayPal', 'Visa', 'Mastercard', 'Apple Pay', 'Google Pay'] as $pm)<span>{{ $pm }}</span>@endforeach</div>
            </div>
            <div>
                <h4>Shop</h4>
                <ul>
                    <li><a href="{{ route('shop') }}">All bargains</a></li>
                    <li><a href="{{ route('deals') }}">Price drops</a></li>
                    <li><a href="{{ route('new') }}">New in</a></li>
                    @foreach ($navCategories->take(3) as $c)<li><a href="{{ route('category', $c) }}">{{ $c->name }}</a></li>@endforeach
                </ul>
            </div>
            <div>
                <h4>Help</h4>
                <ul>
                    <li><a href="{{ route('about') }}#faq">Delivery &amp; returns</a></li>
                    <li><a href="{{ route('contact') }}">Contact us</a></li>
                    <li><a href="{{ route('privacy') }}">Privacy</a></li>
                    <li><a href="{{ setting('ebay_store_url') }}" target="_blank" rel="noopener">eBay store ↗</a></li>
                </ul>
            </div>
            <div>
                <h4>Get in touch</h4>
                <ul>
                    @if (setting('contact_email'))<li><a href="mailto:{{ setting('contact_email') }}">{{ setting('contact_email') }}</a></li>@endif
                    @if (setting('whatsapp'))<li><a href="https://wa.me/{{ preg_replace('/\D/', '', setting('whatsapp')) }}" target="_blank" rel="noopener">WhatsApp us</a></li>@endif
                    <li>{{ setting('address') }}</li>
                </ul>
                @if (socials())
                    <div class="socials">
                        @foreach (socials() as $key => [$label, $url])
                            <a class="round" href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $label }}">@include('partials.icon', ['name' => $key, 'class' => 'icon icon-sm'])</a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div class="word" data-x="6" aria-hidden="true">Bargain <span>Bugs</span></div>
        <div class="legal"><span>© {{ date('Y') }} {{ setting('site_name') }}</span><span>Purchases completed on eBay. eBay is a trademark of eBay Inc.</span></div>
    </div>
</footer>

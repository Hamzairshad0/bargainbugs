{{-- Brand logo (from the eBay store profile picture).
     Options: logoSize, logoText, logoSub, logoMarkOnly — prefixed so they never clash with the parent view's variables. --}}
<a href="{{ route('home') }}" class="group inline-flex shrink-0 items-center" aria-label="{{ setting('site_name') }} — home">
    <img src="{{ asset('images/logo.png') }}" alt="{{ setting('site_name') }}" width="140" height="140"
         class="{{ $logoSize ?? 'h-14 w-14' }} object-contain drop-shadow-sm transition-transform duration-500 ease-[cubic-bezier(.16,1,.3,1)] group-hover:-rotate-6 group-hover:scale-110">
    @unless ($logoMarkOnly ?? false)
        <span class="ml-2.5 hidden leading-none sm:block">
            <span class="block text-[19px] font-extrabold tracking-tight {{ $logoText ?? 'text-plum-800' }}">Bargain<span class="text-tang-500">Bugs</span></span>
            <span class="mt-1 block text-[10px] font-bold uppercase tracking-[.22em] {{ $logoSub ?? 'text-ink-3' }}">Online e-store</span>
        </span>
    @endunless
</a>

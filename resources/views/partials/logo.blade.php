<a href="{{ route('home') }}" class="group inline-flex items-center gap-2.5 {{ $class ?? '' }}" aria-label="{{ setting('site_name') }} home">
    <img src="{{ asset('images/logo-mark.svg') }}" alt="" class="h-10 w-10 transition-transform duration-300 group-hover:rotate-[-12deg] group-hover:scale-110" width="40" height="40">
    <span class="font-display text-[22px] leading-none font-extrabold tracking-tight {{ $text ?? 'text-ink' }}">
        Bargain<span class="text-bug-500">Bugs</span>
    </span>
</a>

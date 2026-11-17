// Bargain Bugs storefront.
import Alpine from 'alpinejs';
import { initMotion, animateIn, ScrollTrigger, scrollToEl } from './motion';

const $ = (s, el = document) => el.querySelector(s);
const $$ = (s, el = document) => [...el.querySelectorAll(s)];
const store = {
    get: (k, d) => { try { return JSON.parse(localStorage.getItem(k)) ?? d; } catch { return d; } },
    set: (k, v) => { try { localStorage.setItem(k, JSON.stringify(v)); } catch { /* private mode */ } },
};

// ------------------------------------------------------------------ Alpine state
document.addEventListener('alpine:init', () => {
    Alpine.store('toast', {
        items: [],
        show(text, icon = '✓') {
            const id = Date.now() + Math.random();
            this.items.push({ id, text, icon });
            setTimeout(() => (this.items = this.items.filter((t) => t.id !== id)), 2400);
        },
    });

    Alpine.store('wish', {
        ids: store.get('bb_wish', []),
        has(id) { return this.ids.includes(id); },
        toggle(id) {
            const on = !this.has(id);
            this.ids = on ? [id, ...this.ids].slice(0, 80) : this.ids.filter((i) => i !== id);
            store.set('bb_wish', this.ids);
            Alpine.store('toast').show(on ? 'Saved to your wishlist' : 'Removed from wishlist', on ? '♥' : '♡');
        },
    });

    Alpine.store('ui', {
        panel: null, // menu | wish | search | filters
        open(p) {
            this.panel = p;
            document.body.classList.add('lock');
            window.lenis?.stop();
            if (p !== 'search') document.documentElement.style.overflow = 'hidden';
        },
        close() {
            this.panel = null;
            document.body.classList.remove('lock');
            window.lenis?.start();
            document.documentElement.style.overflow = '';
        },
    });

    Alpine.data('wishDrawer', () => ({
        html: '', loading: false,
        async load() {
            const ids = Alpine.store('wish').ids;
            if (!ids.length) { this.html = ''; return; }
            this.loading = true;
            try { this.html = await (await fetch(`/api/cards?layout=list&ids=${ids.join(',')}`)).text(); } finally { this.loading = false; }
        },
    }));

    Alpine.data('liveSearch', () => ({
        q: '', items: [], total: 0, url: '/shop', loading: false, active: -1, t: null,
        input() {
            clearTimeout(this.t); this.active = -1;
            if (this.q.trim().length < 2) { this.items = []; this.total = 0; return; }
            this.t = setTimeout(() => this.fetch(), 200);
        },
        async fetch() {
            this.loading = true;
            try {
                const d = await (await fetch(`/api/suggest?q=${encodeURIComponent(this.q.trim())}`)).json();
                this.items = d.items; this.total = d.total; this.url = d.url;
            } finally { this.loading = false; }
        },
        move(n) { if (this.items.length) this.active = (this.active + n + this.items.length) % this.items.length; },
        submit(e) { if (this.active >= 0) { e.preventDefault(); location.href = this.items[this.active].url; } },
    }));
});

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && Alpine.store('ui').panel) Alpine.store('ui').close(); });

// ------------------------------------------------------------------ Motion + sliders
initMotion();
if ($('.swiper')) import('./sliders').then((m) => m.initSliders());

// ------------------------------------------------------------------ Product catalogue (chips, sort, load more)
$$('[data-catalog]').forEach((cat) => {
    const grid = $('[data-grid]', cat);
    const pushUrl = cat.dataset.catalog === 'push';
    let base = cat.dataset.base || location.pathname + location.search;

    const render = (d, append) => {
        const tmp = document.createElement('div');
        tmp.innerHTML = d.html;
        const items = [...tmp.children];
        if (!append) grid.innerHTML = '';
        items.forEach((i) => grid.appendChild(i));
        animateIn(items);
        $$('[data-shown]', cat).forEach((e) => (e.textContent = d.shown));
        $$('[data-total]', cat).forEach((e) => (e.textContent = d.total));
        const bar = $('[data-bar]', cat); if (bar) bar.style.width = (d.total ? (d.shown / d.total) * 100 : 0) + '%';
        const more = $('[data-more]', cat);
        if (more) {
            more.hidden = !d.next;
            if (d.next) { more.href = d.next; $('[data-more-label]', more).textContent = `Load ${d.nextCount} more`; }
        }
        requestAnimationFrame(() => ScrollTrigger.refresh());
    };

    let busy = false;
    const load = async (url, append = false) => {
        if (append && busy) return;
        busy = true;
        grid.classList.toggle('loading', !append);
        const u = new URL(url, location.origin);
        u.searchParams.set('partial', '1');
        try {
            const d = await (await fetch(u, { headers: { Accept: 'application/json' } })).json();
            render(d, append);
        } finally { grid.classList.remove('loading'); busy = false; }
    };

    // Infinite scroll: fetch the next 24 automatically as the "Load more" button nears the screen.
    const moreBtn = $('[data-more]', cat);
    if (moreBtn) {
        const loadNext = () => {
            if (busy || moreBtn.hidden || moreBtn.getAttribute('href') === '#') return;
            $('[data-more-label]', moreBtn).textContent = 'Loading…';
            load(moreBtn.href, true).then(() => {
                // still near the screen (fast scroll / tall screen)? keep going
                if (!moreBtn.hidden && moreBtn.getBoundingClientRect().top < innerHeight + 600) loadNext();
            });
        };
        const near = () => !moreBtn.hidden && moreBtn.getBoundingClientRect().top < innerHeight + 600;
        if ('IntersectionObserver' in window) {
            new IntersectionObserver((es) => es.some((e) => e.isIntersecting) && loadNext(), { rootMargin: '0px 0px 600px 0px' }).observe(moreBtn);
        }
        let tick = 0; // cheap scroll fallback (older browsers, throttled observers)
        addEventListener('scroll', () => { if (!tick++) setTimeout(() => { tick = 0; if (near()) loadNext(); }, 200); }, { passive: true });
    }

    cat.addEventListener('click', (e) => {
        const chip = e.target.closest('[data-chip]');
        const more = e.target.closest('[data-more]');
        if (chip) {
            e.preventDefault();
            $$('[data-chip]', cat).forEach((c) => c.classList.toggle('on', c === chip));
            const sort = $('[data-sort]', cat)?.value;
            const u = new URL(chip.href, location.origin);
            if (sort && sort !== 'featured') u.searchParams.set('sort', sort);
            base = u.pathname + u.search;
            if (pushUrl) history.replaceState(null, '', base);
            load(base);
            chip.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' });
        } else if (more) {
            e.preventDefault();
            if (busy) return;
            more.querySelector('[data-more-label]').textContent = 'Loading…';
            load(more.href, true);
        }
    });

    $('[data-sort]', cat)?.addEventListener('change', (e) => {
        const u = new URL(base, location.origin);
        u.searchParams.delete('page');
        e.target.value === 'featured' ? u.searchParams.delete('sort') : u.searchParams.set('sort', e.target.value);
        base = u.pathname + u.search;
        if (pushUrl) history.replaceState(null, '', base);
        load(base);
        const top = cat.getBoundingClientRect().top;
        if (top < 0) scrollToEl(cat);
    });
});

// ------------------------------------------------------------------ Share / copy
document.addEventListener('click', async (e) => {
    const share = e.target.closest('[data-share]');
    const copy = e.target.closest('[data-copy]');
    const url = share?.dataset.url || copy?.dataset.copy;
    if (share && navigator.share) {
        try { await navigator.share({ title: share.dataset.title, url }); } catch { /* cancelled */ }
    } else if (url) {
        navigator.clipboard?.writeText(url).then(() => Alpine.store('toast').show('Link copied', '🔗'));
    }
});

// Recently viewed (product page)
const rid = $('[data-recent-id]')?.dataset.recentId;
if (rid) store.set('bb_recent', [Number(rid), ...store.get('bb_recent', []).filter((i) => i !== Number(rid))].slice(0, 12));

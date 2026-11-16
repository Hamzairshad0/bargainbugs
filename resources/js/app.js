// Bargain Bugs — tiny vanilla JS, no framework needed.

const $ = (s, el = document) => el.querySelector(s);
const $$ = (s, el = document) => [...el.querySelectorAll(s)];

// Generic toggles: <button data-toggle="#menu"> toggles .hidden on #menu
document.addEventListener('click', (e) => {
    const t = e.target.closest('[data-toggle]');
    if (!t) return;
    const target = $(t.dataset.toggle);
    if (!target) return;
    target.classList.toggle('hidden');
    t.setAttribute('aria-expanded', String(!target.classList.contains('hidden')));
    const focus = target.querySelector('[data-autofocus]');
    if (focus && !target.classList.contains('hidden')) focus.focus();
});

// Product gallery
$$('[data-gallery]').forEach((g) => {
    const main = $('[data-gallery-main]', g);
    const thumbs = $$('[data-gallery-thumb]', g);
    if (!main || !thumbs.length) return;
    let idx = 0;
    const show = (i) => {
        idx = (i + thumbs.length) % thumbs.length;
        main.src = thumbs[idx].dataset.src;
        thumbs.forEach((t, n) => t.classList.toggle('!border-bug-500', n === idx));
        const counter = $('[data-gallery-count]', g);
        if (counter) counter.textContent = `${idx + 1} / ${thumbs.length}`;
    };
    thumbs.forEach((t, i) => t.addEventListener('click', () => show(i)));
    $('[data-gallery-prev]', g)?.addEventListener('click', () => show(idx - 1));
    $('[data-gallery-next]', g)?.addEventListener('click', () => show(idx + 1));

    let x0 = null;
    main.addEventListener('touchstart', (e) => (x0 = e.touches[0].clientX), { passive: true });
    main.addEventListener('touchend', (e) => {
        if (x0 === null) return;
        const dx = e.changedTouches[0].clientX - x0;
        if (Math.abs(dx) > 40) show(idx + (dx < 0 ? 1 : -1));
        x0 = null;
    });
});

// Share: native share sheet on phones, copy link elsewhere
$$('[data-share]').forEach((btn) => {
    btn.addEventListener('click', async () => {
        const data = { title: btn.dataset.title || document.title, url: btn.dataset.url || location.href };
        if (navigator.share) {
            try { await navigator.share(data); } catch (_) { /* cancelled */ }
            return;
        }
        copy(data.url, btn);
    });
});
$$('[data-copy]').forEach((btn) => btn.addEventListener('click', () => copy(btn.dataset.copy, btn)));

function copy(text, btn) {
    navigator.clipboard?.writeText(text).then(() => {
        const label = $('[data-label]', btn) || btn;
        const old = label.textContent;
        label.textContent = 'Link copied!';
        setTimeout(() => (label.textContent = old), 1600);
    });
}

// Reveal on scroll (content stays visible if JS fails; safety net reveals everything after 2.5s)
document.documentElement.classList.add('js');
setTimeout(() => $$('.reveal').forEach((el) => el.classList.add('is-in')), 2500);
const io = 'IntersectionObserver' in window
    ? new IntersectionObserver((entries) => entries.forEach((en) => {
        if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); }
    }), { rootMargin: '0px 0px -40px 0px' })
    : null;
$$('.reveal').forEach((el) => (io ? io.observe(el) : el.classList.add('is-in')));

// Auto-submit filters
$$('[data-autosubmit]').forEach((el) => el.addEventListener('change', () => el.form?.submit()));

// Header shadow on scroll
const header = $('[data-header]');
if (header) {
    const onScroll = () => header.classList.toggle('shadow-soft', window.scrollY > 8);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
}

// Admin: select-all checkboxes
$$('[data-check-all]').forEach((all) => all.addEventListener('change', () => {
    $$(all.dataset.checkAll).forEach((c) => (c.checked = all.checked));
}));

// Confirm dialogs: <form data-confirm="Sure?"> or <button data-confirm="...">
document.addEventListener('submit', (e) => {
    const msg = e.submitter?.dataset.confirm || e.target.dataset.confirm;
    if (msg && !confirm(msg)) e.preventDefault();
});

// Admin: image URL list preview
$$('[data-images-input]').forEach((ta) => {
    const preview = $(ta.dataset.imagesInput);
    const render = () => {
        preview.innerHTML = '';
        ta.value.split(/\n+/).map((s) => s.trim()).filter(Boolean).forEach((src) => {
            const img = document.createElement('img');
            img.src = src;
            img.className = 'h-20 w-20 rounded-xl object-cover border border-black/10 bg-white';
            preview.appendChild(img);
        });
    };
    ta.addEventListener('input', render);
    render();
});

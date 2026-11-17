// Swiper carousels (loaded only on pages that contain one).
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, EffectFade, FreeMode, Thumbs, Keyboard, A11y, Mousewheel } from 'swiper/modules';

const $ = (s, el = document) => el.querySelector(s);
const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

export function initSliders() {
    // Hero product slider + synced price tag
    const hero = $('[data-hero-swiper]');
    if (hero) {
        const bar = $('[data-hero-bar]');
        const restartBar = () => { if (!bar) return; bar.classList.remove('run'); void bar.offsetWidth; bar.classList.add('run'); };
        const sync = (s) => {
            const d = s.slides[s.activeIndex]?.dataset || {};
            const set = (sel, v) => { const el = $(sel); if (el) el.textContent = v || ''; };
            set('[data-tag-cat]', d.cat); set('[data-tag-price]', d.price); set('[data-tag-was]', d.was);
            const off = $('[data-tag-off]'); if (off) { off.textContent = d.off || ''; off.hidden = !d.off; }
            set('[data-hero-count]', String(s.realIndex + 1).padStart(2, '0'));
            restartBar();
        };
        const count = hero.querySelectorAll('.swiper-slide').length;
        new Swiper(hero, {
            modules: [EffectFade, Autoplay, Navigation, Keyboard],
            effect: 'fade', fadeEffect: { crossFade: true }, speed: 1000, loop: count > 1,
            autoplay: count > 1 && !reduce ? { delay: 5500, disableOnInteraction: false } : false,
            navigation: { prevEl: '[data-hero-prev]', nextEl: '[data-hero-next]' },
            keyboard: { enabled: true },
            on: { init: sync, slideChange: sync },
        });
    }

    document.querySelectorAll('[data-carousel]').forEach((el) => {
        const wrap = el.closest('[data-carousel-wrap]') || el.parentElement;
        new Swiper(el, {
            modules: [Navigation, FreeMode, Mousewheel, A11y],
            slidesPerView: 'auto', spaceBetween: 18, freeMode: { enabled: true, sticky: false }, grabCursor: true,
            mousewheel: { forceToAxis: true },
            navigation: { prevEl: $('[data-prev]', wrap), nextEl: $('[data-next]', wrap) },
        });
    });

    const rev = $('[data-reviews]');
    if (rev) {
        new Swiper(rev, {
            modules: [EffectFade, Autoplay, Pagination],
            effect: 'fade', fadeEffect: { crossFade: true }, loop: rev.querySelectorAll('.swiper-slide').length > 1, autoHeight: true,
            autoplay: reduce ? false : { delay: 6000 }, pagination: { el: $('.swiper-pagination', rev), clickable: true },
        });
    }

    const gmain = $('[data-gallery-main]');
    if (gmain) {
        const tEl = $('[data-gallery-thumbs]');
        const thumbs = tEl ? new Swiper(tEl, { modules: [FreeMode], slidesPerView: 'auto', spaceBetween: 10, freeMode: true, watchSlidesProgress: true }) : null;
        const count = $('[data-gallery-count]');
        new Swiper(gmain, {
            modules: [Navigation, Thumbs, Keyboard, A11y],
            spaceBetween: 10, keyboard: { enabled: true },
            navigation: { prevEl: '[data-gallery-prev]', nextEl: '[data-gallery-next]' },
            thumbs: thumbs ? { swiper: thumbs } : undefined,
            on: { slideChange: (s) => { if (count) count.textContent = `${s.activeIndex + 1} / ${s.slides.length}`; } },
        });
    }
}

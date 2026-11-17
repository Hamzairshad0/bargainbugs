// Motion: Lenis smooth scroll (desktop only) + GSAP ScrollTrigger reveals, split text, parallax, counters.
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const $$ = (s, el = document) => [...el.querySelectorAll(s)];
export const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
export const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
export let lenis = null;

export async function initMotion() {
    const root = document.documentElement;

    // Smooth scrolling only on desktop with a mouse/trackpad — phones keep fast native scrolling.
    if (!reduce && finePointer) {
        const { default: Lenis } = await import('lenis');
        lenis = new Lenis({ lerp: 0.12 });
        window.lenis = lenis;
        lenis.on('scroll', ScrollTrigger.update);
        gsap.ticker.add((t) => lenis.raf(t * 1000));
        gsap.ticker.lagSmoothing(0);
    }

    onScroll(headerAndProgress());

    if (reduce) {
        root.classList.remove('motion');
        return;
    }

    splitText();
    reveals();
    if (finePointer) { parallax(); magnetic(); }
    counters();

    root.classList.remove('motion');
    window.addEventListener('load', () => ScrollTrigger.refresh());
}

export function onScroll(fn) {
    if (lenis) lenis.on('scroll', ({ scroll }) => fn(scroll));
    else window.addEventListener('scroll', () => fn(window.scrollY), { passive: true });
    fn(window.scrollY);
}

export function scrollToEl(el, offset = -100) {
    if (lenis) lenis.scrollTo(el, { offset });
    else window.scrollTo({ top: el.getBoundingClientRect().top + window.scrollY + offset, behavior: 'smooth' });
}

function headerAndProgress() {
    const header = document.querySelector('[data-header]');
    const bar = document.querySelector('[data-progress]');
    let last = 0;
    return (y) => {
        if (header) {
            header.classList.toggle('scrolled', y > 20);
            header.classList.toggle('hide', y > last && y > 400 && !document.body.classList.contains('lock'));
        }
        if (bar) {
            const max = document.documentElement.scrollHeight - window.innerHeight;
            bar.style.transform = `scaleX(${max > 0 ? Math.min(1, y / max) : 0})`;
        }
        last = y;
    };
}

/** Wrap words of [data-split] in masks and slide them up. [data-keep] children stay whole. */
function splitText() {
    $$('[data-split]').forEach((el) => {
        const walk = (node) => [...node.childNodes].forEach((ch) => {
            if (ch.nodeType === 3) {
                const f = document.createDocumentFragment();
                ch.textContent.split(/(\s+)/).forEach((w) => {
                    if (!w) return;
                    if (/^\s+$/.test(w)) { f.appendChild(document.createTextNode(w)); return; }
                    const o = document.createElement('span'); o.className = 'sw';
                    const i = document.createElement('span'); i.textContent = w;
                    o.appendChild(i); f.appendChild(o);
                });
                ch.replaceWith(f);
            } else if (ch.nodeType === 1 && ch.matches('[data-keep]')) {
                const o = document.createElement('span'); o.className = 'sw';
                const i = document.createElement('span');
                ch.replaceWith(o); i.appendChild(ch); o.appendChild(i);
            } else if (ch.nodeType === 1 && ch.tagName !== 'BR') {
                walk(ch);
            }
        });
        walk(el);
        el.style.opacity = 1;
        gsap.from($$('.sw > span', el), {
            yPercent: 115, rotate: 4, duration: 1.1, ease: 'expo.out', stagger: 0.05,
            scrollTrigger: el.hasAttribute('data-now') ? undefined : { trigger: el, start: 'top 90%' },
        });
    });
}

function reveals() {
    gsap.set('[data-reveal]', { opacity: 0, y: 40 });
    ScrollTrigger.batch('[data-reveal]', {
        start: 'top 94%',
        once: true,
        onEnter: (b) => gsap.to(b, { opacity: 1, y: 0, duration: 1, ease: 'expo.out', stagger: 0.07, overwrite: true }),
    });
}

function parallax() {
    $$('[data-speed]').forEach((el) => {
        const s = parseFloat(el.dataset.speed);
        gsap.fromTo(el, { yPercent: -s * 50 }, {
            yPercent: s * 50, ease: 'none',
            scrollTrigger: { trigger: el.closest('section,footer') || el, start: 'top bottom', end: 'bottom top', scrub: true },
        });
    });
    $$('[data-x]').forEach((el) => {
        const s = parseFloat(el.dataset.x);
        gsap.fromTo(el, { xPercent: -s }, { xPercent: s, ease: 'none', scrollTrigger: { trigger: el, start: 'top bottom', end: 'bottom top', scrub: true } });
    });
}

function counters() {
    $$('[data-count]').forEach((el) => {
        const end = parseFloat(el.dataset.count), dec = +(el.dataset.dec || 0), o = { v: 0 };
        gsap.to(o, {
            v: end, duration: 2, ease: 'power3.out', scrollTrigger: { trigger: el, start: 'top 95%', once: true },
            onUpdate: () => (el.textContent = o.v.toLocaleString('en-GB', { minimumFractionDigits: dec, maximumFractionDigits: dec })),
        });
    });
}

function magnetic() {
    $$('[data-magnetic]').forEach((el) => {
        const x = gsap.quickTo(el, 'x', { duration: 0.5, ease: 'power3' }), y = gsap.quickTo(el, 'y', { duration: 0.5, ease: 'power3' });
        el.addEventListener('mousemove', (e) => { const r = el.getBoundingClientRect(); x((e.clientX - r.left - r.width / 2) * 0.3); y((e.clientY - r.top - r.height / 2) * 0.3); });
        el.addEventListener('mouseleave', () => { x(0); y(0); });
    });
}

/** Fade in freshly inserted cards. */
export function animateIn(els) {
    if (reduce || !els.length) return;
    gsap.fromTo(els, { opacity: 0, y: 34 }, { opacity: 1, y: 0, duration: 0.8, ease: 'expo.out', stagger: 0.03 });
}

export { gsap, ScrollTrigger };

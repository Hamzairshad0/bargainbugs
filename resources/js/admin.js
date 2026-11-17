// Admin panel helpers (vanilla JS).
const $ = (s, el = document) => el.querySelector(s);
const $$ = (s, el = document) => [...el.querySelectorAll(s)];

// <button data-toggle="#id"> toggles .hidden on #id
document.addEventListener('click', (e) => {
    const t = e.target.closest('[data-toggle]');
    if (!t) return;
    $(t.dataset.toggle)?.classList.toggle('hidden');
});

// Copy buttons
$$('[data-copy]').forEach((btn) => btn.addEventListener('click', () => {
    navigator.clipboard?.writeText(btn.dataset.copy).then(() => {
        const label = $('[data-label]', btn) || btn;
        const old = label.textContent;
        label.textContent = 'Copied!';
        setTimeout(() => (label.textContent = old), 1500);
    });
}));

$$('[data-autosubmit]').forEach((el) => el.addEventListener('change', () => el.form?.submit()));

$$('[data-check-all]').forEach((all) => all.addEventListener('change', () => {
    $$(all.dataset.checkAll).forEach((c) => (c.checked = all.checked));
}));

document.addEventListener('submit', (e) => {
    const msg = e.submitter?.dataset.confirm || e.target.dataset.confirm;
    if (msg && !confirm(msg)) e.preventDefault();
});

// Image URL list preview on the product form
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

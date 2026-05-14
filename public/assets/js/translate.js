// ============================================================
// PAGE CONTENT TRANSLATION via Google Translate free endpoint
// ============================================================
(function () {
    'use strict';

    const cache = {};

    async function gt(text, to) {
        const t = text.trim();
        if (!t || t.length < 2) return text;
        const key = to + ':' + t;
        if (cache[key] !== undefined) return cache[key];
        try {
            const url = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=fr&tl='
                + to + '&dt=t&q=' + encodeURIComponent(t);
            const r = await fetch(url);
            const d = await r.json();
            let out = '';
            if (d && d[0]) d[0].forEach(p => { if (p && p[0]) out += p[0]; });
            cache[key] = out || t;
            return cache[key];
        } catch (e) {
            return text;
        }
    }

    // Translate only direct text nodes of an element (preserves child elements)
    async function translateTextNodes(el, toLang) {
        const tasks = [];
        el.childNodes.forEach(node => {
            if (node.nodeType === Node.TEXT_NODE) {
                const orig = node.textContent.trim();
                if (orig.length > 1) {
                    tasks.push(
                        gt(orig, toLang).then(translated => {
                            node.textContent = node.textContent.replace(orig, translated);
                        })
                    );
                }
            }
        });
        await Promise.all(tasks);
    }

    // Selectors → what to do with them
    // 'text' = replace full textContent (no child elements)
    // 'nodes' = walk text nodes only (has child elements like <a>, <strong>)
    const TARGETS = [
        { sel: '.post-header h3 a',                      mode: 'text' },
        { sel: '.post-content > p',                       mode: 'text' },
        { sel: '.recent-subject-title',                   mode: 'text' },
        { sel: '.post-detail .post-header h1',            mode: 'text' },
        { sel: '.post-detail .post-content .rich-content',mode: 'nodes' },
        { sel: '.comment-content .rich-content',          mode: 'nodes' },
    ];

    async function translateAll(toLang) {
        const tasks = [];

        TARGETS.forEach(({ sel, mode }) => {
            document.querySelectorAll(sel).forEach(el => {
                const raw = el.textContent.trim();
                if (raw.length < 2) return;

                if (mode === 'text') {
                    // Simple leaf element
                    if (!el.dataset.orig) el.dataset.orig = raw;
                    tasks.push(
                        toLang === 'fr'
                            ? Promise.resolve().then(() => { el.textContent = el.dataset.orig; })
                            : gt(el.dataset.orig, toLang).then(t => { el.textContent = t; })
                    );
                } else {
                    // Has child elements — translate text nodes individually
                    if (!el.dataset.orig) el.dataset.orig = '1'; // mark as seen
                    tasks.push(translateTextNodes(el, toLang));
                }
            });
        });

        await Promise.all(tasks);
    }

    function init() {
        const lang = (document.documentElement.lang || 'fr').toLowerCase();
        if (lang !== 'fr') {
            translateAll(lang);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

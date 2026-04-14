// ============================================================
//  validation.js — Controle de saisie SANS HTML5
//  Regles : required | minlen:N | maxlen:N | email | number
//           min:N | max:N | date | time | letters | phone
// ============================================================

const Validator = {

    rules: {
        required: (v)      => v.trim() !== '' ? null : 'Ce champ est obligatoire.',
        minlen:   (v, n)   => v.trim().length >= +n ? null : 'Minimum ' + n + ' caracteres.',
        maxlen:   (v, n)   => v.trim().length <= +n ? null : 'Maximum ' + n + ' caracteres.',
        email:    (v)      => {
            if (!v.trim()) return null;
            return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim()) ? null : 'Email invalide (ex: nom@domaine.com).';
        },
        number:   (v)      => {
            if (!v.trim()) return null;
            return /^\d+(\.\d+)?$/.test(v.trim()) ? null : 'Nombre invalide.';
        },
        min:      (v, n)   => !v.trim() || +v >= +n ? null : 'Valeur minimum : ' + n + '.',
        max:      (v, n)   => !v.trim() || +v <= +n ? null : 'Valeur maximum : ' + n + '.',
        date:     (v)      => {
            if (!v.trim()) return null;
            if (!/^\d{4}-\d{2}-\d{2}$/.test(v.trim())) return 'Format: YYYY-MM-DD (ex: 2026-04-20).';
            const d = new Date(v.trim());
            return isNaN(d) ? 'Date invalide.' : null;
        },
        time:     (v)      => {
            if (!v.trim()) return null;
            return /^([01]\d|2[0-3]):[0-5]\d$/.test(v.trim()) ? null : 'Format: HH:MM (ex: 09:30).';
        },
        letters:  (v)      => {
            if (!v.trim()) return null;
            return /^[a-zA-ZÀ-ÿ\s\-']{2,}$/.test(v.trim()) ? null : 'Lettres uniquement.';
        },
        phone:    (v)      => {
            if (!v.trim()) return null;
            return /^[\d\s\+\-\(\)]{8,20}$/.test(v.trim()) ? null : 'Telephone invalide (8-20 chiffres).';
        },
    },

    check(input) {
        const raw = input.getAttribute('data-validate') || '';
        if (!raw) return true;
        const errEl = document.getElementById('e-' + input.id);
        for (const rule of raw.split('|')) {
            const [name, param] = rule.split(':');
            const fn = this.rules[name];
            if (!fn) continue;
            const msg = fn(input.value, param);
            if (msg) {
                input.classList.add('err');
                input.classList.remove('ok');
                if (errEl) { errEl.textContent = msg; errEl.style.display = 'block'; }
                return false;
            }
        }
        input.classList.remove('err');
        input.classList.add('ok');
        if (errEl) { errEl.textContent = ''; errEl.style.display = 'none'; }
        return true;
    },

    checkForm(form) {
        let valid = true, first = null;
        form.querySelectorAll('[data-validate]').forEach(inp => {
            if (!this.check(inp)) { valid = false; if (!first) first = inp; }
        });
        if (first) { first.scrollIntoView({behavior:'smooth',block:'center'}); first.focus(); }
        return valid;
    },

    init() {
        document.querySelectorAll('form[novalidate]').forEach(form => {
            form.querySelectorAll('[data-validate]').forEach(inp => {
                inp.addEventListener('blur',  () => this.check(inp));
                inp.addEventListener('input', () => {
                    inp.classList.remove('err');
                    const e = document.getElementById('e-' + inp.id);
                    if (e) e.textContent = '';
                });
            });
            form.addEventListener('submit', e => {
                e.preventDefault();
                if (this.checkForm(form)) form.submit();
            });
        });
    }
};

document.addEventListener('DOMContentLoaded', () => Validator.init());

// ── HELPERS UI ───────────────────────────────────────────────
function confirmDel(msg) {
    return confirm(msg || 'Confirmer la suppression ?');
}

function toast(msg, type) {
    type = type || 'success';
    const icons = {success:'✅', error:'❌', warning:'⚠️'};
    const wrap = document.getElementById('toasts');
    if (!wrap) return;
    const t = document.createElement('div');
    t.className = 'toast' + (type === 'error' ? ' t-err' : type === 'warning' ? ' t-warn' : '');
    t.innerHTML = '<span>' + (icons[type]||'✅') + '</span><span style="flex:1;font-size:.83rem;font-weight:500">' + msg + '</span>'
                + '<button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;opacity:.5">✕</button>';
    wrap.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-close alerts
    document.querySelectorAll('.alert').forEach(function(a) {
        setTimeout(function(){ a.style.opacity='0'; a.style.transition='opacity .5s'; }, 4000);
        setTimeout(function(){ a.remove(); }, 4600);
    });
});

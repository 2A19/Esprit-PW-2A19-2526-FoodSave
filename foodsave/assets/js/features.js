/* ================================================================
   features.js — Metiers FoodSave (version corrigee & amelioree)
   ================================================================ */

var currentLang = localStorage.getItem('fs_lang') || 'fr';
var ttsSpeaking = false;

var DICT_AR = {
    'Gestion':'الإدارة','Acces rapide':'وصول سريع','Administrateur':'المسؤول',
    'BackOffice':'الإدارة الخلفية','Voir le site':'عرض الموقع',
    'Gestion des Participants':'إدارة المشاركين','Total':'المجموع',
    'Confirmes':'مؤكّد','En attente':'قيد الانتظار','Annules':'ملغى',
    'Recommandations IA':'توصيات الذكاء الاصطناعي','Analyser':'تحليل',
    'Statut':'الحالة','Actions':'الإجراءات','Inscrit le':'تاريخ التسجيل',
    'Gestion des Evenements':'إدارة الفعاليات','Statistiques':'الإحصائيات',
    'A venir':'قادمة','En cours':'جارية','Termines':'منتهية',
    'Tous statuts':'كل الحالات','Tous evenements':'كل الفعاليات',
    'Reset':'إعادة تعيين','Accueil':'الرئيسية','Evenements':'الفعاليات',
    'Inscription':'التسجيل','Complet':'اكتمل','Places disponibles':'مقاعد متاحة',
    'Voir le site':'عرض الموقع','Nouveau participant':'مشارك جديد',
    'Nouvel evenement':'فعالية جديدة','Annuler':'إلغاء','Envoyer':'إرسال'
};

// 1. SORT
function initSort() {
    var url = new URL(window.location.href);
    var cur = url.searchParams.get('sort');
    var dir = url.searchParams.get('dir') || 'asc';
    document.querySelectorAll('th[data-sort]').forEach(function(th) {
        th.style.cursor = 'pointer';
        th.title = 'Cliquer pour trier';
        var col = th.dataset.sort;
        if (cur === col) {
            th.innerHTML += dir === 'asc' ? ' ▲' : ' ▼';
            th.style.color = 'var(--green)';
        }
        th.addEventListener('click', function() {
            var newDir = (cur === col && dir === 'asc') ? 'desc' : 'asc';
            url.searchParams.set('sort', col);
            url.searchParams.set('dir', newDir);
            window.location.href = url.toString();
        });
    });
}

// 2. TRANSLATION
function applyTranslation(lang) {
    if (lang === 'ar') {
        document.documentElement.dir  = 'rtl';
        document.documentElement.lang = 'ar';
        if (!document.getElementById('fs-rtl')) {
            var s = document.createElement('style');
            s.id = 'fs-rtl';
            s.textContent = 'body{font-family:"Segoe UI",Tahoma,Arial,sans-serif}th,td{text-align:right}.filter-bar,.stats-grid,.page-strip{direction:rtl}.topbar>div:last-child{flex-direction:row-reverse}';
            document.head.appendChild(s);
        }
        document.querySelectorAll('[data-tr]').forEach(function(el) {
            var k = el.dataset.tr;
            if (DICT_AR[k]) el.textContent = DICT_AR[k];
        });
        document.querySelectorAll('#clientFilter').forEach(function(el) {
            el.placeholder = '⚡ تصفية فورية...';
        });
        document.querySelectorAll('select option').forEach(function(opt) {
            var t = opt.textContent.trim();
            if (DICT_AR[t]) opt.textContent = DICT_AR[t];
        });
    } else {
        document.documentElement.dir  = 'ltr';
        document.documentElement.lang = 'fr';
        var rtlS = document.getElementById('fs-rtl');
        if (rtlS) rtlS.remove();
        document.querySelectorAll('[data-tr]').forEach(function(el) {
            el.textContent = el.dataset.tr;
        });
        document.querySelectorAll('#clientFilter').forEach(function(el) {
            el.placeholder = '⚡ Filtre instant...';
        });
    }
}

function initTranslation() {
    document.querySelectorAll('[data-lang-btn]').forEach(function(btn) {
        if (btn.dataset.langBtn === currentLang) btn.classList.add('active-lang');
        btn.addEventListener('click', function() {
            currentLang = btn.dataset.langBtn;
            localStorage.setItem('fs_lang', currentLang);
            document.querySelectorAll('[data-lang-btn]').forEach(function(b) {
                b.classList.toggle('active-lang', b.dataset.langBtn === currentLang);
            });
            applyTranslation(currentLang);
        });
    });
    if (currentLang !== 'fr') applyTranslation(currentLang);
}

// 3. NOTIFICATIONS
function initNotifications() {
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
}

function pushNotif(title, body) {
    toast(body || title, 'success');
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(title, { body: body || title });
    }
}

// 4. FILTER
function initClientFilter() {
    var input = document.getElementById('clientFilter');
    if (!input) return;

    function doFilter() {
        var q = input.value.toLowerCase().trim();
        var visible = 0;
        var rows  = document.querySelectorAll('tbody tr[data-filterable]');
        var cards = document.querySelectorAll('[data-filterable-card]');
        rows.forEach(function(tr) {
            var m = q === '' || tr.textContent.toLowerCase().includes(q);
            tr.style.display = m ? '' : 'none';
            if (m) visible++;
        });
        cards.forEach(function(c) {
            var m = q === '' || c.textContent.toLowerCase().includes(q);
            c.style.display = m ? '' : 'none';
            if (m) visible++;
        });
        var counter = document.getElementById('filterCount');
        if (counter) {
            var total = rows.length + cards.length;
            counter.textContent = q ? (visible + ' / ' + total + ' resultat(s)') : '';
        }
    }
    input.addEventListener('input', doFilter);
    if (input.value) doFilter();
}

// 5. COUNTDOWN
function initTimers() {
    document.querySelectorAll('[data-countdown]').forEach(function(el) {
        var target = new Date(el.dataset.countdown).getTime();
        if (isNaN(target)) return;
        function tick() {
            var diff = target - Date.now();
            if (diff <= 0) { el.innerHTML = '<span class="timer-done">En cours!</span>'; return; }
            var d = Math.floor(diff/86400000);
            var h = Math.floor((diff%86400000)/3600000);
            var m = Math.floor((diff%3600000)/60000);
            var s = Math.floor((diff%60000)/1000);
            el.innerHTML =
                '<span class="timer-unit">'+d+'<small>j</small></span><span class="timer-sep">:</span>'+
                '<span class="timer-unit">'+String(h).padStart(2,'0')+'<small>h</small></span><span class="timer-sep">:</span>'+
                '<span class="timer-unit">'+String(m).padStart(2,'0')+'<small>m</small></span><span class="timer-sep">:</span>'+
                '<span class="timer-unit">'+String(s).padStart(2,'0')+'<small>s</small></span>';
        }
        tick(); setInterval(tick, 1000);
    });
}

// 6. TTS
function initTTS() {
    document.querySelectorAll('.tts-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!('speechSynthesis' in window)) { toast('TTS non supporte', 'warning'); return; }
            if (ttsSpeaking) {
                window.speechSynthesis.cancel(); ttsSpeaking = false;
                document.querySelectorAll('.tts-btn').forEach(function(b) { b.innerHTML = b.dataset.ttsLabel || '🔊'; });
                return;
            }
            var text = this.dataset.text || '';
            if (!text) return;
            var utt = new SpeechSynthesisUtterance(text.replace(/\s+/g,' ').trim());
            utt.lang = currentLang === 'ar' ? 'ar-TN' : 'fr-FR';
            utt.rate = 0.92;
            this.dataset.ttsLabel = this.innerHTML;
            utt.onend = function() {
                ttsSpeaking = false;
                document.querySelectorAll('.tts-btn').forEach(function(b) { b.innerHTML = b.dataset.ttsLabel || '🔊'; });
            };
            ttsSpeaking = true;
            document.querySelectorAll('.tts-btn').forEach(function(b) { b.innerHTML = '⏹ Stop'; });
            window.speechSynthesis.speak(utt);
        });
    });
}

// 7. QR CODE
function generateQR(text, targetEl, size) {
    size = size || 190;
    var container = document.createElement('div');
    container.style.cssText = 'text-align:center;padding:12px 8px;';

    var img = document.createElement('img');
    img.src = 'https://api.qrserver.com/v1/create-qr-code/?size='+size+'x'+size+'&data='+encodeURIComponent(text)+'&ecc=H&margin=10';
    img.alt = 'QR Code';
    img.style.cssText = 'border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,.2);max-width:100%;border:5px solid #fff;';

    var parts = text.split('|').filter(Boolean);
    var legend = document.createElement('div');
    legend.style.cssText = 'font-size:.67rem;color:#546E7A;margin-top:8px;line-height:1.6;max-width:210px;margin-left:auto;margin-right:auto;word-break:break-word;background:#f0f4f8;border-radius:6px;padding:6px 8px;text-align:left;';
    legend.innerHTML = parts.map(function(p) {
        var sep = p.indexOf(':');
        if (sep > 0 && sep < 20) {
            return '<strong>'+p.substring(0,sep+1)+'</strong>'+p.substring(sep+1);
        }
        return p;
    }).join('<br>');

    var dlBtn = document.createElement('a');
    dlBtn.innerHTML = '⬇ Telecharger PNG';
    dlBtn.style.cssText = 'display:inline-block;margin-top:8px;padding:4px 12px;border-radius:6px;background:var(--green);color:#fff;font-size:.7rem;font-weight:600;text-decoration:none;';
    dlBtn.href = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data='+encodeURIComponent(text)+'&ecc=H&format=png';
    dlBtn.download = 'qr_foodsave.png';
    dlBtn.target = '_blank';

    container.appendChild(img);
    container.appendChild(legend);
    container.appendChild(dlBtn);
    targetEl.innerHTML = '';
    targetEl.appendChild(container);
}

function initQRButtons() {
    document.querySelectorAll('[data-qr]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var target = document.getElementById(this.dataset.qrTarget || 'qrContainer');
            if (!target) return;
            if (target.style.display === 'block') {
                target.style.display = 'none';
                this.innerHTML = '📲 QR';
            } else {
                generateQR(this.dataset.qr, target);
                target.style.display = 'block';
                this.innerHTML = '✕ Fermer';
            }
        });
    });
}

// 8. GIFs
var GIF_MAP = {
    'food':    'https://media.giphy.com/media/3o7aDczpCChShEG27S/giphy.gif',
    'success': 'https://media.giphy.com/media/111ebonMs90YLu/giphy.gif',
    'sms':     'https://media.giphy.com/media/ASd0Ukj0y3qMM/giphy.gif',
    'welcome': 'https://media.giphy.com/media/3oEjI6SIIHBdRxXI40/giphy.gif'
};

function showGif(containerId, key) {
    var el = document.getElementById(containerId);
    if (!el) return;
    el.innerHTML = '<img src="'+(GIF_MAP[key]||GIF_MAP.success)+'" alt="gif" style="max-width:180px;border-radius:10px;">';
}

// 9. SENTIMENT ANALYSIS
async function analyzeSentiment(text, resultEl) {
    if (!resultEl) return;
    resultEl.innerHTML = '<div class="ai-loading"><span class="spinner"></span> Analyse en cours...</div>';
    try {
        var fd = new FormData();
        fd.append('action', 'sentiment');
        fd.append('data', JSON.stringify({ text: text }));
        var res  = await fetch('ajax/ai_proxy.php', { method:'POST', body:fd });
        var json = await res.json();
        if (json.error) throw new Error(json.error);
        var d = json.data;
        var colors = { 'positif':'#4CAF50','neutre':'#FFA726','negatif':'#ef5350','négatif':'#ef5350' };
        var color = colors[d.sentiment] || '#90A4AE';
        var tags = (d.mots_cles||[]).map(function(m) {
            return '<span style="background:'+color+'22;color:'+color+';padding:2px 8px;border-radius:20px;font-size:.68rem;margin-right:3px;font-weight:700">'+m+'</span>';
        }).join('');
        resultEl.innerHTML =
            '<div style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:#f8f9fa;border-radius:10px;border-left:4px solid '+color+'">'+
            '<span style="font-size:2rem">'+(d.emoji||'😐')+'</span><div>'+
            '<div style="font-weight:700;color:'+color+'">'+((d.sentiment||'').toUpperCase())+' -- '+(d.score||0)+'%</div>'+
            '<div style="font-size:.8rem;color:#546E7A;margin-top:2px">'+(d.resume||'')+'</div>'+
            (tags?'<div style="margin-top:6px">'+tags+'</div>':'')+
            '</div></div>';
    } catch(e) {
        resultEl.innerHTML = '<span style="color:#ef5350;font-size:.8rem">⚠ '+e.message+'</span>';
    }
}

// 10. AI RECOMMENDATIONS
async function getRecommendations(context, resultEl) {
    if (!resultEl) return;
    resultEl.innerHTML = '<div class="ai-loading"><span class="spinner"></span> IA en reflexion...</div>';
    try {
        var fd = new FormData();
        fd.append('action', 'recommend');
        fd.append('data', JSON.stringify(context));
        var res  = await fetch('ajax/ai_proxy.php', { method:'POST', body:fd });
        var json = await res.json();
        if (json.error) throw new Error(json.error);
        var recs = json.data.recommendations || [];
        var pC   = { 'haute':'#ef5350','moyenne':'#FFA726','basse':'#4CAF50' };
        resultEl.innerHTML = recs.map(function(r) {
            var c = pC[r.priorite] || '#90A4AE';
            return '<div style="padding:12px 15px;background:#fff;border-radius:10px;border:1px solid #ECEFF1;margin-bottom:10px;border-left:4px solid '+c+'">'+
                '<div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">'+
                '<span style="font-size:1.2rem">'+(r.icone||'💡')+'</span>'+
                '<strong style="font-size:.88rem">'+r.titre+'</strong>'+
                '<span style="margin-left:auto;font-size:.65rem;padding:2px 8px;border-radius:10px;background:'+c+'22;color:'+c+';font-weight:700">'+(r.priorite||'')+'</span>'+
                '</div><p style="font-size:.8rem;color:#546E7A;margin:0;line-height:1.5">'+r.description+'</p></div>';
        }).join('') || '<span style="color:#90A4AE;font-size:.85rem">Aucune recommandation.</span>';
    } catch(e) {
        resultEl.innerHTML = '<span style="color:#ef5350;font-size:.8rem">⚠ '+e.message+'</span>';
    }
}

// 11. EMAIL
async function sendEmail(email, message, btnEl) {
    if (btnEl) { btnEl.disabled = true; btnEl.textContent = '📤 Envoi...'; }
    try {
        var fd = new FormData();
        fd.append('phone', email); fd.append('message', message);
        var res  = await fetch('ajax/send_sms.php', { method:'POST', body:fd });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        var data = await res.json();
        if (data.success) {
            toast('✅ Email envoye a ' + email, 'success');
            showGif('gifZone', 'success');
            if (typeof closeEmailModal === 'function') closeEmailModal();
        } else {
            toast('Email: ' + (data.error || 'Erreur inconnue'), 'warning');
        }
    } catch(e) {
        toast('Email indisponible : ' + e.message, 'warning');
    } finally {
        if (btnEl) { btnEl.disabled = false; btnEl.textContent = '📤 Envoyer'; }
    }
}

// 12. TOAST
function toast(msg, type) {
    type = type || 'info';
    var wrap = document.getElementById('toasts');
    if (!wrap) return;
    var t = document.createElement('div');
    t.style.cssText = 'padding:10px 16px;border-radius:8px;margin-bottom:8px;font-size:.82rem;font-weight:600;box-shadow:0 4px 16px rgba(0,0,0,.18);color:#fff;';
    var c = {success:'#4CAF50',warning:'#FF9800',danger:'#ef5350',info:'#2196F3'};
    t.style.background = c[type] || c.info;
    t.textContent = msg;
    wrap.appendChild(t);
    setTimeout(function() { t.remove(); }, 4000);
}

// 13. CONFIRM
function confirmDel(msg) { return confirm(msg || 'Confirmer la suppression ?'); }

// INIT
document.addEventListener('DOMContentLoaded', function() {
    initSort();
    initTranslation();
    initNotifications();
    initClientFilter();
    initTimers();
    initTTS();
    initQRButtons();
});

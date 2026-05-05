/**
 * FoodSave — app.js
 * CRUD Déchets / Catégories / Collectes — sans relation utilisateur
 */

// ============================================================
//  API URLs
// ============================================================
const API_DECHETS    = 'api/dechets.php';
const API_CATEGORIES = 'api/categories.php';
const API_COLLECTES  = 'api/collectes.php';

// ============================================================
//  STATE
// ============================================================
let dechets    = [];
let categories = [];
let collectes  = [];

let editingId          = null;
let deleteTargetId     = null;
let editingCatId       = null;
let deleteCatTargetId  = null;
let editingColId       = null;
let deleteColTargetId  = null;

let currentPage = 'dashboard';

// ============================================================
//  NAVIGATION
// ============================================================
function navigate(page) {
  currentPage = page;
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));

  const target  = document.getElementById('page-' + page);
  if (target) target.classList.add('active');

  const navItem = document.querySelector(`[data-page="${page}"]`);
  if (navItem) navItem.classList.add('active');

  const titles = {
    dashboard:  'Vue d\'ensemble',
    list:       'Déchets alimentaires',
    add:        'Nouveau déchet',
    historique: 'Tableau de jointure',
    categories: 'Catégories',
    collectes:  'Collectes',
  };
  document.getElementById('page-title').textContent = titles[page] || 'FoodSave';

  if (page === 'dashboard')  renderDashboard();
  if (page === 'list')       renderTable();
  if (page === 'historique') renderHistorique();
  if (page === 'categories') loadCategories();
  if (page === 'collectes')  loadCollectes();
  if (page === 'add') { populateCollecteSelect(); }
}

// ============================================================
//  API HELPER
// ============================================================
async function apiFetch(url, options = {}) {
  const res = await fetch(url, {
    headers: { 'Content-Type': 'application/json', Accept: 'application/json', ...options.headers },
    ...options,
  });
  const json = await res.json();
  if (!json.success) throw new Error(json.message || 'Erreur API');
  return json;
}

// ============================================================
//  LOAD DATA
// ============================================================
async function loadDechets() {
  const json = await apiFetch(API_DECHETS);
  dechets = json.data || [];
}

async function loadCategories() {
  const json = await apiFetch(API_CATEGORIES);
  categories = json.data || [];
  renderCategories();
}

async function loadCollectes() {
  const json = await apiFetch(API_COLLECTES);
  collectes = json.data || [];
  renderCollectes();
  renderCollectesStats();
}

async function refresh() {
  await loadDechets();
  if (currentPage === 'dashboard')  renderDashboard();
  if (currentPage === 'list')       renderTable();
  if (currentPage === 'historique') renderHistorique();
  if (currentPage === 'add')        populateCollecteSelect();
}

// ============================================================
//  VALIDATION (aucune HTML5, conforme contrainte prof)
// ============================================================
function validateField(fieldId, errorId, rules) {
  const field = document.getElementById(fieldId);
  const error = document.getElementById(errorId);
  const val   = field ? field.value.trim() : '';
  let msg = '';

  if (rules.required && val === '')          msg = 'Ce champ est obligatoire.';
  else if (rules.pattern && !rules.pattern.test(val)) msg = rules.patternMsg || 'Format invalide.';
  else if (rules.min !== undefined && parseFloat(val) < rules.min) msg = `Min : ${rules.min}.`;
  else if (rules.max !== undefined && parseFloat(val) > rules.max) msg = `Max : ${rules.max}.`;
  else if (rules.isDate && val && isNaN(Date.parse(val))) msg = 'Date invalide.';
  else if (rules.futureDate && val && new Date(val) > new Date()) msg = 'La date ne peut pas être dans le futur.';

  if (error) {
    error.textContent = msg;
    error.classList.toggle('show', !!msg);
  }
  if (field) field.classList.toggle('error', !!msg);
  return !msg;
}

function clearErrors(formId) {
  const form = document.getElementById(formId);
  if (!form) return;
  form.querySelectorAll('input, select, textarea').forEach(el => el.classList.remove('error'));
  form.querySelectorAll('.error-msg').forEach(el => { el.textContent = ''; el.classList.remove('show'); });
}

function validateDechetForm(prefix) {
  let valid = true;
  [
    { field: `${prefix}-type`,     error: `${prefix}-type-err`,     rules: { required: true } },
    { field: `${prefix}-quantite`, error: `${prefix}-quantite-err`, rules: { required: true, pattern: /^\d+(\.\d{1,3})?$/, patternMsg: 'Nombre valide requis (ex: 1.5)', min: 0.001, max: 9999 } },
    { field: `${prefix}-unite`,    error: `${prefix}-unite-err`,    rules: { required: true } },
    { field: `${prefix}-date`,     error: `${prefix}-date-err`,     rules: { required: true, isDate: true, futureDate: true } },
    { field: `${prefix}-raison`,   error: `${prefix}-raison-err`,   rules: { required: true } },
  ].forEach(c => { if (!validateField(c.field, c.error, c.rules)) valid = false; });
  return valid;
}

// ============================================================
//  CRUD DECHETS
// ============================================================

/** CREATE */
// Remplit le select collecte dans le formulaire d'ajout
function populateCollecteSelect() {
  const sel = document.getElementById('add-collecte');
  if (!sel) return;
  // Garder la sélection actuelle
  const current = sel.value;
  sel.innerHTML = '<option value="">— Aucune collecte —</option>';
  // Afficher seulement collectes actives (pas annulées)
  collectes
    .filter(c => c.statut !== 'annulee')
    .forEach(c => {
      const sb = STATUT_BADGE[c.statut] || { label: c.statut };
      const opt = document.createElement('option');
      opt.value = c.id;
      opt.textContent = `${sb.label}  #${c.id} — ${c.titre} (${c.lieu})`;
      if (String(c.id) === String(current)) opt.selected = true;
      sel.appendChild(opt);
    });
}

async function submitAdd() {
  if (!validateDechetForm('add')) return;

  const payload = {
    type_aliment: document.getElementById('add-type').value,
    quantite:     parseFloat(document.getElementById('add-quantite').value),
    unite:        document.getElementById('add-unite').value,
    date_dechet:  document.getElementById('add-date').value,
    raison:       document.getElementById('add-raison').value,
    notes:        document.getElementById('add-notes').value.trim(),
  };

  const collecteId = document.getElementById('add-collecte')?.value
    ? parseInt(document.getElementById('add-collecte').value)
    : null;

  try {
    // 1. Sauvegarder le déchet
    const res = await apiFetch(API_DECHETS, { method: 'POST', body: JSON.stringify(payload) });
    const newId = res?.id || res?.data?.id || null;

    // 2. Si une collecte est sélectionnée → sauvegarder dans la jointure
    if (collecteId && newId) {
      // Côté API (quand connecté à la BDD)
      try {
        await apiFetch(API_COLLECTES, {
          method: 'POST',
          body: JSON.stringify({ action: 'link', collecte_id: collecteId, dechet_id: newId }),
        });
      } catch (_) { /* mode démo : pas de BDD */ }

      // Côté localStorage — mise à jour immédiate du COLLECTE_DECHETS_MAP
      if (!COLLECTE_DECHETS_MAP[collecteId]) COLLECTE_DECHETS_MAP[collecteId] = [];
      if (!COLLECTE_DECHETS_MAP[collecteId].includes(newId)) {
        COLLECTE_DECHETS_MAP[collecteId].push(newId);
      }
      // Persister dans localStorage
      localStorage.setItem('fs_jointure_map', JSON.stringify(COLLECTE_DECHETS_MAP));
    }

    await refresh();
    populateCollecteSelect();

    const msg = collecteId
      ? `✅ Déchet enregistré et rattaché à la collecte #${collecteId} !`
      : '✅ Déchet enregistré avec succès !';
    showAlert('add-alert', msg, 'success');
    document.getElementById('form-add').reset();
    document.getElementById('add-collecte-confirm').style.display = 'none';
    clearErrors('form-add');
    setTimeout(() => navigate(collecteId ? 'historique' : 'list'), 1400);
  } catch (err) {
    showAlert('add-alert', `❌ ${err.message}`, 'error');
  }
}

/** READ — table */
function renderTable(filter = '') {
  const tbody = document.getElementById('table-body');
  let data = [...dechets];
  if (filter) data = data.filter(d =>
    d.type_aliment.toLowerCase().includes(filter.toLowerCase()) ||
    d.raison.toLowerCase().includes(filter.toLowerCase())
  );

  if (!data.length) {
    tbody.innerHTML = `<tr><td colspan="7">
      <div class="empty-state"><div class="icon">🗑️</div>
      <h3>Aucun déchet trouvé</h3><p>Aucun résultat.</p></div></td></tr>`;
    return;
  }

  tbody.innerHTML = data.map(d => `
    <tr>
      <td><span class="badge badge-gray">#${d.id}</span></td>
      <td><strong>${esc(d.type_aliment)}</strong></td>
      <td>${Number(d.quantite).toFixed(3)} ${esc(d.unite)}</td>
      <td>${formatDate(d.date_dechet)}</td>
      <td>${esc(d.raison)}</td>
      <td><span class="badge badge-green">Enregistré</span></td>
      <td>
        <div style="display:flex;gap:6px;">
          <button class="btn btn-sm btn-outline" onclick="openEditModal(${d.id})">✏️ Modifier</button>
          <button class="btn btn-sm btn-danger"  onclick="openDeleteConfirm(${d.id})">🗑️ Supprimer</button>
        </div>
      </td>
    </tr>`).join('');
}

/** UPDATE — open modal */
function openEditModal(id) {
  const d = dechets.find(x => x.id === id);
  if (!d) return;
  editingId = id;
  clearErrors('form-edit');

  document.getElementById('edit-type').value     = d.type_aliment;
  document.getElementById('edit-quantite').value = d.quantite;
  document.getElementById('edit-unite').value    = d.unite;
  document.getElementById('edit-date').value     = d.date_dechet;
  document.getElementById('edit-raison').value   = d.raison;
  document.getElementById('edit-notes').value    = d.notes || '';

  openModal('modal-edit');
}

async function submitEdit() {
  if (!validateDechetForm('edit')) return;
  if (!editingId) return;

  try {
    await apiFetch(API_DECHETS, {
      method: 'PUT',
      body: JSON.stringify({
        id:           editingId,
        type_aliment: document.getElementById('edit-type').value,
        quantite:     parseFloat(document.getElementById('edit-quantite').value),
        unite:        document.getElementById('edit-unite').value,
        date_dechet:  document.getElementById('edit-date').value,
        raison:       document.getElementById('edit-raison').value,
        notes:        document.getElementById('edit-notes').value.trim(),
      }),
    });
    await refresh();
    closeModal('modal-edit');
    showAlert('list-alert', '✅ Déchet modifié avec succès !', 'success');
  } catch (err) {
    showAlert('list-alert', `❌ ${err.message}`, 'error');
  }
}

/** DELETE */
function openDeleteConfirm(id) {
  deleteTargetId = id;
  const d = dechets.find(x => x.id === id);
  if (!d) return;
  document.getElementById('delete-item-name').textContent = `${d.type_aliment} (${Number(d.quantite).toFixed(3)} ${d.unite})`;
  openModal('modal-delete');
}

async function confirmDelete() {
  if (!deleteTargetId) return;
  try {
    await apiFetch(API_DECHETS, { method: 'DELETE', body: JSON.stringify({ id: deleteTargetId }) });
    await refresh();
    closeModal('modal-delete');
    showAlert('list-alert', '🗑️ Déchet supprimé.', 'success');
  } catch (err) {
    showAlert('list-alert', `❌ ${err.message}`, 'error');
  } finally {
    deleteTargetId = null;
  }
}

// ============================================================
//  DASHBOARD
// ============================================================
function renderDashboard() {
  const total = dechets.reduce((s, d) => s + Number(d.quantite), 0);
  document.getElementById('stat-total').textContent = total.toFixed(1) + ' kg';
  document.getElementById('stat-count').textContent = dechets.length;
  document.getElementById('stat-today').textContent = getTodayCount();
  document.getElementById('stat-saved').textContent = (total * 0.4).toFixed(1) + ' kg';
  renderChart();
  renderTopRaisons();
}

function getTodayCount() {
  const today = new Date().toISOString().split('T')[0];
  return dechets.filter(d => d.date_dechet === today).length;
}

function renderChart() {
  const byType = {};
  dechets.forEach(d => { byType[d.type_aliment] = (byType[d.type_aliment] || 0) + Number(d.quantite); });
  const colors = ['#4CAF50','#FFA726','#EF5350','#42A5F5','#AB47BC','#26C6DA','#FF7043','#29B6F6'];
  const entries = Object.entries(byType).sort((a, b) => b[1] - a[1]);
  const total = entries.reduce((s, [, v]) => s + v, 0) || 1;

  const chartEl = document.getElementById('chart');
  const legendEl = document.getElementById('chart-legend');

  if (entries.length === 0) {
    chartEl.innerHTML = '<span style="color:var(--text-muted);font-size:0.9rem;">Aucune donnée</span>';
    if (legendEl) legendEl.innerHTML = '';
    return;
  }

  // --- Donut SVG ---
  const size = 220, cx = 110, cy = 110, R = 85, r = 52;
  let startAngle = -Math.PI / 2;
  let slices = '';

  entries.forEach(([type, val], i) => {
    const angle = (val / total) * 2 * Math.PI;
    const endAngle = startAngle + angle;
    const x1 = cx + R * Math.cos(startAngle), y1 = cy + R * Math.sin(startAngle);
    const x2 = cx + R * Math.cos(endAngle),   y2 = cy + R * Math.sin(endAngle);
    const xi1 = cx + r * Math.cos(startAngle), yi1 = cy + r * Math.sin(startAngle);
    const xi2 = cx + r * Math.cos(endAngle),   yi2 = cy + r * Math.sin(endAngle);
    const large = angle > Math.PI ? 1 : 0;
    const color = colors[i % colors.length];
    const pct = ((val / total) * 100).toFixed(1);
    const safeType = type.replace(/'/g, "\'");

    slices += `<path d="M ${x1.toFixed(2)} ${y1.toFixed(2)} A ${R} ${R} 0 ${large} 1 ${x2.toFixed(2)} ${y2.toFixed(2)} L ${xi2.toFixed(2)} ${yi2.toFixed(2)} A ${r} ${r} 0 ${large} 0 ${xi1.toFixed(2)} ${yi1.toFixed(2)} Z"
      fill="${color}" stroke="#fff" stroke-width="2.5"
      style="transition:opacity 0.2s;cursor:pointer;"
      onmouseover="this.style.opacity='0.8';document.getElementById('donut-tip').textContent='${safeType} : ${val.toFixed(1)} kg (${pct}%)';"
      onmouseout="this.style.opacity='1';document.getElementById('donut-tip').textContent='${total.toFixed(1)} kg total';"
    />`;
    startAngle = endAngle;
  });

  chartEl.innerHTML = `
    <svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}" style="filter:drop-shadow(0 4px 12px rgba(0,0,0,0.10));">
      ${slices}
      <circle cx="${cx}" cy="${cy}" r="${r - 2}" fill="white"/>
      <text x="${cx}" y="${cy - 10}" text-anchor="middle" font-size="13" font-weight="700" fill="#333" id="donut-tip">${total.toFixed(1)} kg total</text>
      <text x="${cx}" y="${cy + 10}" text-anchor="middle" font-size="11" fill="#888">gaspillés</text>
    </svg>`;

  // --- Legend ---
  if (legendEl) {
    legendEl.innerHTML = entries.map(([type, val], i) => `
      <div style="display:flex;align-items:center;gap:8px;">
        <span style="width:12px;height:12px;border-radius:50%;background:${colors[i%colors.length]};flex-shrink:0;display:inline-block;"></span>
        <span style="font-size:0.82rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:110px;" title="${esc(type)}">${esc(type)}</span>
        <span style="font-size:0.8rem;font-weight:700;color:#333;margin-left:auto;">${((val/total)*100).toFixed(0)}%</span>
      </div>`).join('');
  }
}

function renderTopRaisons() {
  const byRaison = {};
  const total = dechets.reduce((s, d) => s + Number(d.quantite), 0) || 1;
  dechets.forEach(d => { byRaison[d.raison] = (byRaison[d.raison] || 0) + Number(d.quantite); });
  const sorted = Object.entries(byRaison).sort((a, b) => b[1] - a[1]).slice(0, 4);
  const colors = ['#4CAF50','#FFA726','#EF5350','#42A5F5'];
  document.getElementById('top-raisons').innerHTML = sorted.map(([r, v], i) => `
    <div style="margin-bottom:14px;">
      <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
        <span style="font-size:0.85rem;font-weight:700;">${esc(r)}</span>
        <span style="font-size:0.82rem;color:var(--text-muted)">${v.toFixed(1)} kg</span>
      </div>
      <div class="progress-bar">
        <div class="progress-fill" style="width:${(v/total)*100}%;background:${colors[i]};"></div>
      </div>
    </div>`).join('');
}

// ============================================================
//  HISTORIQUE
// ============================================================
// ============================================================
//  JOINTURE TABLE — collectes × dechets × categories
// ============================================================

// Données de jointure simulées (reflètent collecte_dechets en base)
// Chargées depuis localStorage si disponible
const _savedMap = (() => {
  try { return JSON.parse(localStorage.getItem('fs_jointure_map') || 'null'); } catch { return null; }
})();
const COLLECTE_DECHETS_MAP = _savedMap || {
  1: [1, 3, 4, 5],
  2: [11, 12, 13],
  3: [16, 17, 18],
  4: [7, 8, 14, 15, 19, 20],
  5: [2, 6, 9, 10],
};

const STATUT_BADGE = {
  terminee:  { label: '✅ Terminée',  bg: '#e8f5e9', color: '#2e7d32' },
  en_cours:  { label: '🔄 En cours',  bg: '#fff8e1', color: '#f57c00' },
  planifiee: { label: '📅 Planifiée', bg: '#e3f2fd', color: '#1565c0' },
  annulee:   { label: '❌ Annulée',   bg: '#fce4ec', color: '#c62828' },
};

function buildJointureRows() {
  const rows = [];
  collectes.forEach(col => {
    const dechetIds = COLLECTE_DECHETS_MAP[col.id] || [];
    if (!dechetIds.length) return;
    dechetIds.forEach(did => {
      const d = dechets.find(x => Number(x.id) === did);
      if (!d) return;
      const cat = categories.find(c => Number(c.id) === Number(d.categorie_id)) || null;
      rows.push({ col, d, cat });
    });
  });
  return rows;
}

function populateCatFilter() {
  const sel = document.getElementById('join-filter-cat');
  if (!sel || sel.options.length > 1) return;
  categories.forEach(c => {
    const o = document.createElement('option');
    o.value = c.nom;
    o.textContent = (c.icone || '🏷️') + ' ' + c.nom;
    sel.appendChild(o);
  });
}

function renderHistorique() { renderJointure(); }

function renderJointure() {
  populateCatFilter();
  const q      = (document.getElementById('join-search')?.value || '').toLowerCase();
  const fStat  = document.getElementById('join-filter-statut')?.value || '';
  const fCat   = document.getElementById('join-filter-cat')?.value || '';

  let rows = buildJointureRows();

  // Filtres
  if (q)     rows = rows.filter(r =>
    r.col.titre.toLowerCase().includes(q) ||
    r.d.type_aliment.toLowerCase().includes(q) ||
    (r.cat?.nom || '').toLowerCase().includes(q) ||
    r.col.lieu.toLowerCase().includes(q)
  );
  if (fStat) rows = rows.filter(r => r.col.statut === fStat);
  if (fCat)  rows = rows.filter(r => r.cat?.nom === fCat);

  // KPIs
  const uniqCol = new Set(rows.map(r => r.col.id));
  const uniqCat = new Set(rows.map(r => r.cat?.id).filter(Boolean));
  const totalKg = rows.reduce((s, r) => s + parseFloat(r.d.quantite || 0), 0);
  const setText = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
  setText('jkpi-col', uniqCol.size);
  setText('jkpi-dec', rows.length);
  setText('jkpi-cat', uniqCat.size);
  setText('jkpi-kg',  totalKg.toFixed(2) + ' kg');
  setText('join-count', rows.length + ' ligne(s)');

  const tbody = document.getElementById('join-tbody');
  if (!tbody) return;

  if (!rows.length) {
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:48px;color:var(--text-muted);">
      <div style="font-size:2.5rem;margin-bottom:12px;">🔍</div>
      <div style="font-weight:600;">Aucun résultat trouvé</div>
      <div style="font-size:12px;margin-top:6px;">Modifiez les filtres ou ajoutez des données liées.</div>
    </td></tr>`;
    return;
  }

  // Regrouper les lignes consécutives de la même collecte pour rowspan visuel
  let prevColId = null;
  let colRowCount = {};
  rows.forEach(r => { colRowCount[r.col.id] = (colRowCount[r.col.id] || 0) + 1; });
  const usedCol = new Set();

  tbody.innerHTML = rows.map((r, i) => {
    const { col, d, cat } = r;
    const sb    = STATUT_BADGE[col.statut] || { label: col.statut, bg: '#eee', color: '#555' };
    const couleur = cat?.couleur || '#888';
    const isNewCol = col.id !== prevColId;
    prevColId = col.id;

    // Ligne zébrée par collecte
    const bg = i % 2 === 0 ? 'transparent' : 'var(--bg-secondary)';

    const colCell = isNewCol ? `
      <td rowspan="${colRowCount[col.id]}" style="padding:12px 14px;vertical-align:top;border-right:3px solid ${sb.bg === '#eee' ? '#ccc' : sb.bg};background:${sb.bg}20;">
        <div style="font-weight:700;font-size:13px;color:var(--text);margin-bottom:4px;">${esc(col.titre)}</div>
        <div style="font-size:11px;color:var(--text-muted);">#${col.id} · ${colRowCount[col.id]} déchet(s)</div>
      </td>` : '';

    const dateCell = isNewCol ? `
      <td rowspan="${colRowCount[col.id]}" style="padding:12px 14px;vertical-align:top;white-space:nowrap;color:var(--text-muted);font-size:12px;">
        ${formatDate(col.date_collecte)}
      </td>` : '';

    const lieuCell = isNewCol ? `
      <td rowspan="${colRowCount[col.id]}" style="padding:12px 14px;vertical-align:top;font-size:12px;color:var(--text-muted);max-width:160px;">
        ${esc(col.lieu)}
      </td>` : '';

    const statutCell = isNewCol ? `
      <td rowspan="${colRowCount[col.id]}" style="padding:12px 14px;vertical-align:top;text-align:center;">
        <span style="display:inline-block;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;background:${sb.bg};color:${sb.color};white-space:nowrap;">
          ${sb.label}
        </span>
      </td>` : '';

    return `<tr style="border-bottom:1px solid var(--border);">
      ${colCell}
      ${dateCell}
      ${lieuCell}
      <td style="padding:10px 14px;font-weight:600;">🍽️ ${esc(d.type_aliment)}</td>
      <td style="padding:10px 14px;text-align:right;font-weight:700;color:${couleur};white-space:nowrap;">
        ${parseFloat(d.quantite).toFixed(3)} ${esc(d.unite)}
      </td>
      <td style="padding:10px 14px;color:var(--text-muted);font-size:12px;">${esc(d.raison)}</td>
      <td style="padding:10px 14px;">
        ${cat
          ? `<span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;background:${couleur}20;color:${couleur};">
              ${cat.icone || '🏷️'} ${esc(cat.nom)}
            </span>`
          : `<span style="color:var(--text-muted);font-size:12px;">— Sans catégorie</span>`}
      </td>
      ${statutCell}
    </tr>`;
  }).join('');
}

// ============================================================
//  CATEGORIES
// ============================================================
function renderCategories() {
  const grid = document.getElementById('cat-grid');
  if (!grid) return;

  const totalDechets = categories.reduce((s, c) => s + (Number(c.nombre_dechets) || 0), 0);
  const el1 = document.getElementById('cat-stat-total');
  const el2 = document.getElementById('cat-stat-dechets');
  if (el1) el1.textContent = categories.length;
  if (el2) el2.textContent = totalDechets;

  if (!categories.length) {
    grid.innerHTML = `<div style="text-align:center;padding:40px;color:var(--text-muted);grid-column:1/-1;">
      <div style="font-size:3rem;margin-bottom:12px;">🏷️</div>
      <p>Aucune catégorie. Créez-en une ci-dessus.</p></div>`;
    return;
  }

  grid.innerHTML = categories.map(c => {
    const icone  = c.icone && c.icone.length <= 4 ? c.icone : '🏷️';
    const couleur = c.couleur || '#4CAF50';
    return `
    <div style="background:var(--white);border-radius:var(--radius);padding:20px;border:1.5px solid ${couleur}40;transition:box-shadow .2s;"
         onmouseenter="this.style.boxShadow='0 4px 20px ${couleur}33'" onmouseleave="this.style.boxShadow=''">
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:12px;">
        <div style="width:48px;height:48px;background:${couleur}20;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0;">${esc(icone)}</div>
        <div style="flex:1;min-width:0;">
          <div style="font-weight:800;font-size:1rem;">${esc(c.nom)}</div>
          <div style="font-size:0.78rem;color:var(--text-muted);margin-top:2px;">${Number(c.nombre_dechets)||0} déchet(s)</div>
        </div>
        <div style="width:11px;height:11px;background:${couleur};border-radius:50%;flex-shrink:0;"></div>
      </div>
      ${c.description ? `<p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:14px;line-height:1.4;">${esc(c.description)}</p>` : ''}
      <div style="display:flex;gap:8px;">
        <button class="btn btn-sm btn-outline" style="flex:1;" onclick="openEditCatModal(${c.id})">✏️ Modifier</button>
        <button class="btn btn-sm btn-danger" onclick="openDeleteCatConfirm(${c.id}, '${esc(c.nom)}')">🗑️</button>
      </div>
    </div>`;
  }).join('');
}

function toggleCatForm() {
  const w = document.getElementById('cat-form-wrap');
  w.style.display = w.style.display === 'none' ? 'block' : 'none';
}

function resetCatForm() {
  document.getElementById('form-cat').reset();
  document.getElementById('cat-couleur').value = '#4CAF50';
  document.getElementById('cat-couleur-hex').textContent = '#4CAF50';
}

async function submitCategory() {
  const nom   = document.getElementById('cat-nom').value.trim();
  const errEl = document.getElementById('cat-nom-err');
  if (!nom) {
    errEl.textContent = 'Le nom est obligatoire.';
    errEl.classList.add('show');
    document.getElementById('cat-nom').classList.add('error');
    return;
  }
  errEl.classList.remove('show');
  document.getElementById('cat-nom').classList.remove('error');

  try {
    await apiFetch(API_CATEGORIES, {
      method: 'POST',
      body: JSON.stringify({
        nom,
        description: document.getElementById('cat-description').value.trim(),
        couleur:     document.getElementById('cat-couleur').value,
        icone:       document.getElementById('cat-icone').value.trim() || 'tag',
      }),
    });
    showAlert('cat-alert', '✅ Catégorie créée !', 'success');
    resetCatForm();
    document.getElementById('cat-form-wrap').style.display = 'none';
    await loadCategories();
  } catch (err) {
    showAlert('cat-alert', `❌ ${err.message}`, 'error');
  }
}

function openEditCatModal(id) {
  const c = categories.find(x => Number(x.id) === id);
  if (!c) return;
  editingCatId = id;
  document.getElementById('ecat-nom').value         = c.nom || '';
  document.getElementById('ecat-description').value = c.description || '';
  document.getElementById('ecat-couleur').value     = c.couleur || '#4CAF50';
  document.getElementById('ecat-couleur-hex').textContent = c.couleur || '#4CAF50';
  document.getElementById('ecat-icone').value       = c.icone || '';
  openModal('modal-edit-cat');
}

async function submitEditCategory() {
  const nom   = document.getElementById('ecat-nom').value.trim();
  const errEl = document.getElementById('ecat-nom-err');
  if (!nom) { errEl.textContent = 'Le nom est obligatoire.'; errEl.classList.add('show'); return; }
  errEl.classList.remove('show');

  try {
    await apiFetch(API_CATEGORIES, {
      method: 'PUT',
      body: JSON.stringify({
        id:          editingCatId,
        nom,
        description: document.getElementById('ecat-description').value.trim(),
        couleur:     document.getElementById('ecat-couleur').value,
        icone:       document.getElementById('ecat-icone').value.trim() || 'tag',
      }),
    });
    closeModal('modal-edit-cat');
    showAlert('cat-alert', '✅ Catégorie modifiée !', 'success');
    await loadCategories();
  } catch (err) {
    showAlert('cat-alert', `❌ ${err.message}`, 'error');
  }
}

function openDeleteCatConfirm(id, nom) {
  deleteCatTargetId = id;
  document.getElementById('delete-cat-name').textContent = nom;
  openModal('modal-delete-cat');
}

async function confirmDeleteCategory() {
  if (!deleteCatTargetId) return;
  try {
    await apiFetch(API_CATEGORIES, { method: 'DELETE', body: JSON.stringify({ id: deleteCatTargetId }) });
    closeModal('modal-delete-cat');
    showAlert('cat-alert', '🗑️ Catégorie supprimée.', 'success');
    await loadCategories();
  } catch (err) {
    showAlert('cat-alert', `❌ ${err.message}`, 'error');
  } finally { deleteCatTargetId = null; }
}

// ============================================================
//  COLLECTES
// ============================================================
const STATUT_LABELS = {
  planifiee: { label: '📅 Planifiée',  cls: 'badge-planifiee' },
  en_cours:  { label: '🔄 En cours',   cls: 'badge-en_cours'  },
  terminee:  { label: '✅ Terminée',   cls: 'badge-terminee'  },
  annulee:   { label: '❌ Annulée',    cls: 'badge-annulee'   },
};

function renderCollectesStats() {
  const s = { total: collectes.length, terminees: 0, en_cours: 0, planifiees: 0 };
  collectes.forEach(c => {
    if (c.statut === 'terminee')  s.terminees++;
    if (c.statut === 'en_cours')  s.en_cours++;
    if (c.statut === 'planifiee') s.planifiees++;
  });
  const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
  set('col-stat-total',     s.total);
  set('col-stat-terminees', s.terminees);
  set('col-stat-encours',   s.en_cours);
  set('col-stat-planifiees',s.planifiees);
}

function renderCollectes(filter = '') {
  const tbody = document.getElementById('col-table-body');
  if (!tbody) return;
  let data = [...collectes];
  if (filter) data = data.filter(c =>
    c.titre.toLowerCase().includes(filter.toLowerCase()) ||
    c.lieu.toLowerCase().includes(filter.toLowerCase())
  );

  if (!data.length) {
    tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><div class="icon">🚛</div><h3>Aucune collecte trouvée</h3></div></td></tr>`;
    return;
  }

  tbody.innerHTML = data.map(c => {
    const s = STATUT_LABELS[c.statut] || { label: c.statut, cls: 'badge-gray' };
    return `<tr>
      <td><span class="badge badge-gray">#${c.id}</span></td>
      <td><strong>${esc(c.titre)}</strong></td>
      <td>${esc(c.lieu)}</td>
      <td>${formatDate(c.date_collecte)}</td>
      <td>${Number(c.quantite_totale || 0).toFixed(1)} kg</td>
      <td><span class="badge ${s.cls}">${s.label}</span></td>
      <td><div style="display:flex;gap:6px;">
        <button class="btn btn-sm btn-outline" onclick="openDetailCol(${c.id})" title="Voir les déchets collectés">🔗 Détail</button>
        <button class="btn btn-sm btn-outline" onclick="openEditColModal(${c.id})">✏️ Modifier</button>
        <button class="btn btn-sm btn-danger"  onclick="openDeleteColConfirm(${c.id}, '${esc(c.titre)}')">🗑️</button>
      </div></td>
    </tr>`;
  }).join('');
}

function toggleColForm() {
  const w = document.getElementById('col-form-wrap');
  w.style.display = w.style.display === 'none' ? 'block' : 'none';
}

function resetColForm() {
  document.getElementById('form-col').reset();
  document.getElementById('col-date').value = new Date().toISOString().split('T')[0];
}

function validateCollecteForm(prefix) {
  let valid = true;
  [
    { field: `${prefix}-titre`, error: `${prefix}-titre-err`, rules: { required: true } },
    { field: `${prefix}-lieu`,  error: `${prefix}-lieu-err`,  rules: { required: true } },
    { field: `${prefix}-date`,  error: `${prefix}-date-err`,  rules: { required: true, isDate: true } },
  ].forEach(c => { if (!validateField(c.field, c.error, c.rules)) valid = false; });
  return valid;
}

async function submitCollecte() {
  if (!validateCollecteForm('col')) return;

  try {
    await apiFetch(API_COLLECTES, {
      method: 'POST',
      body: JSON.stringify({
        titre:           document.getElementById('col-titre').value.trim(),
        description:     document.getElementById('col-description').value.trim(),
        date_collecte:   document.getElementById('col-date').value,
        lieu:            document.getElementById('col-lieu').value.trim(),
        quantite_totale: parseFloat(document.getElementById('col-quantite').value || '0'),
        unite:           'kg',
        statut:          document.getElementById('col-statut').value,
      }),
    });
    showAlert('col-alert', '✅ Collecte créée !', 'success');
    resetColForm();
    document.getElementById('col-form-wrap').style.display = 'none';
    await loadCollectes();
  } catch (err) {
    showAlert('col-alert', `❌ ${err.message}`, 'error');
  }
}

function openEditColModal(id) {
  const c = collectes.find(x => Number(x.id) === id);
  if (!c) return;
  editingColId = id;
  document.getElementById('ecol-titre').value       = c.titre || '';
  document.getElementById('ecol-lieu').value        = c.lieu || '';
  document.getElementById('ecol-date').value        = c.date_collecte || '';
  document.getElementById('ecol-quantite').value    = c.quantite_totale || '0';
  document.getElementById('ecol-statut').value      = c.statut || 'planifiee';
  document.getElementById('ecol-description').value = c.description || '';
  openModal('modal-edit-col');
}

async function submitEditCollecte() {
  if (!validateCollecteForm('ecol')) return;

  try {
    await apiFetch(API_COLLECTES, {
      method: 'PUT',
      body: JSON.stringify({
        id:              editingColId,
        titre:           document.getElementById('ecol-titre').value.trim(),
        description:     document.getElementById('ecol-description').value.trim(),
        date_collecte:   document.getElementById('ecol-date').value,
        lieu:            document.getElementById('ecol-lieu').value.trim(),
        quantite_totale: parseFloat(document.getElementById('ecol-quantite').value || '0'),
        unite:           'kg',
        statut:          document.getElementById('ecol-statut').value,
      }),
    });
    closeModal('modal-edit-col');
    showAlert('col-alert', '✅ Collecte modifiée !', 'success');
    await loadCollectes();
  } catch (err) {
    showAlert('col-alert', `❌ ${err.message}`, 'error');
  }
}

function openDeleteColConfirm(id, titre) {
  deleteColTargetId = id;
  document.getElementById('delete-col-name').textContent = titre;
  openModal('modal-delete-col');
}

async function confirmDeleteCollecte() {
  if (!deleteColTargetId) return;
  try {
    await apiFetch(API_COLLECTES, { method: 'DELETE', body: JSON.stringify({ id: deleteColTargetId }) });
    closeModal('modal-delete-col');
    showAlert('col-alert', '🗑️ Collecte supprimée.', 'success');
    await loadCollectes();
  } catch (err) {
    showAlert('col-alert', `❌ ${err.message}`, 'error');
  } finally { deleteColTargetId = null; }
}

// ============================================================
//  MODALS
// ============================================================
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// ============================================================
//  ALERTS
// ============================================================
function showAlert(id, msg, type) {
  const el = document.getElementById(id);
  if (!el) return;
  el.className = `alert alert-${type} show`;
  el.innerHTML = `<span>${msg}</span>`;
  setTimeout(() => el.classList.remove('show'), 3500);
}

// ============================================================
//  UTILITIES
// ============================================================
function esc(s) {
  return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function formatDate(d) {
  if (!d) return '—';
  return new Date(d + 'T00:00:00').toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
}

function typeEmoji(t) {
  const map = { 'Légumes':'🥦','Fruits':'🍎','Pain':'🍞','Viande':'🥩','Produits laitiers':'🥛','Poisson':'🐟' };
  return map[t] || '🍽️';
}

// ============================================================
//  JOINTURE 3 ENTITÉS — Détail collecte avec déchets + catégories
// ============================================================

async function openDetailCol(id) {
  const col = collectes.find(c => Number(c.id) === id);
  if (!col) return;

  const body = document.getElementById('detail-col-body');
  const s = STATUT_LABELS[col.statut] || { label: col.statut, cls: 'badge-gray' };

  // Affiche le header de la collecte immédiatement
  body.innerHTML = `
    <div style="background:var(--bg-secondary);border-radius:10px;padding:16px;margin-top:20px;margin-bottom:20px;">
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
        <div>
          <div style="font-size:17px;font-weight:700;color:var(--text);">🚛 ${esc(col.titre)}</div>
          <div style="font-size:13px;color:var(--text-muted);margin-top:4px;">📍 ${esc(col.lieu)} &nbsp;·&nbsp; 📅 ${formatDate(col.date_collecte)}</div>
        </div>
        <span class="badge ${s.cls}">${s.label}</span>
      </div>
      ${col.description ? `<div style="margin-top:10px;font-size:13px;color:var(--text-muted);line-height:1.5;">${esc(col.description)}</div>` : ''}
    </div>
    <div style="font-weight:600;font-size:14px;color:var(--text);margin-bottom:12px;">
      🔗 Déchets récupérés lors de cette collecte <span style="color:var(--text-muted);font-weight:400;">(jointure collectes → dechets → catégories)</span>
    </div>
    <div id="detail-col-loading" style="text-align:center;padding:30px;color:var(--text-muted);">
      <div style="font-size:28px;margin-bottom:8px;">⏳</div>Chargement des données…
    </div>`;

  openModal('modal-detail-col');

  // Appel API jointure 3 entités
  try {
    const res = await apiFetch(`${API_COLLECTES}?id=${id}&with_dechets`);
    const dechets = res?.data?.dechets || [];

    if (!dechets.length) {
      document.getElementById('detail-col-loading').innerHTML = `
        <div style="text-align:center;padding:24px;color:var(--text-muted);">
          <div style="font-size:32px;margin-bottom:8px;">📭</div>
          <div>Aucun déchet lié à cette collecte.</div>
          <div style="font-size:12px;margin-top:6px;">Les données de jointure apparaissent ici une fois des déchets associés.</div>
        </div>`;
      return;
    }

    // Grouper par catégorie
    const parCat = {};
    dechets.forEach(d => {
      const cat = d.categorie_nom || 'Sans catégorie';
      if (!parCat[cat]) parCat[cat] = { icone: d.categorie_icone || '📦', couleur: d.categorie_couleur || '#888', items: [] };
      parCat[cat].items.push(d);
    });

    const totalKg = dechets.reduce((s, d) => s + parseFloat(d.quantite || 0), 0);

    let html = `
      <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
        <div style="background:var(--primary-light, #e8f5e9);border-radius:8px;padding:10px 16px;flex:1;min-width:120px;">
          <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Déchets récupérés</div>
          <div style="font-size:24px;font-weight:700;color:var(--primary, #2e7d32);">${dechets.length}</div>
        </div>
        <div style="background:#fff8e1;border-radius:8px;padding:10px 16px;flex:1;min-width:120px;">
          <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Total quantité</div>
          <div style="font-size:24px;font-weight:700;color:#f57c00;">${totalKg.toFixed(2)} kg</div>
        </div>
        <div style="background:#e3f2fd;border-radius:8px;padding:10px 16px;flex:1;min-width:120px;">
          <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Catégories</div>
          <div style="font-size:24px;font-weight:700;color:#1565c0;">${Object.keys(parCat).length}</div>
        </div>
      </div>`;

    Object.entries(parCat).forEach(([catNom, cat]) => {
      const catKg = cat.items.reduce((s, d) => s + parseFloat(d.quantite || 0), 0);
      html += `
        <div style="border:1px solid ${cat.couleur}33;border-left:4px solid ${cat.couleur};border-radius:8px;margin-bottom:12px;overflow:hidden;">
          <div style="background:${cat.couleur}15;padding:10px 14px;display:flex;align-items:center;justify-content:space-between;">
            <span style="font-weight:600;font-size:14px;">${cat.icone} ${esc(catNom)}</span>
            <span style="font-size:12px;color:var(--text-muted);">${cat.items.length} déchet(s) · ${catKg.toFixed(2)} kg</span>
          </div>
          <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
              <tr style="background:${cat.couleur}08;">
                <th style="padding:7px 14px;text-align:left;color:var(--text-muted);font-weight:500;">Aliment</th>
                <th style="padding:7px 14px;text-align:right;color:var(--text-muted);font-weight:500;">Quantité</th>
                <th style="padding:7px 14px;text-align:left;color:var(--text-muted);font-weight:500;">Raison</th>
                <th style="padding:7px 14px;text-align:left;color:var(--text-muted);font-weight:500;">Date</th>
              </tr>
            </thead>
            <tbody>
              ${cat.items.map((d, i) => `
                <tr style="border-top:1px solid var(--border);background:${i%2===0?'transparent':'var(--bg-secondary)'};">
                  <td style="padding:8px 14px;font-weight:500;">🍽️ ${esc(d.type_aliment)}</td>
                  <td style="padding:8px 14px;text-align:right;color:${cat.couleur};font-weight:600;">${parseFloat(d.quantite).toFixed(2)} ${esc(d.unite)}</td>
                  <td style="padding:8px 14px;color:var(--text-muted);">${esc(d.raison)}</td>
                  <td style="padding:8px 14px;color:var(--text-muted);">${formatDate(d.date_dechet)}</td>
                </tr>`).join('')}
            </tbody>
          </table>
        </div>`;
    });

    html += `<div style="text-align:center;font-size:11px;color:var(--text-muted);margin-top:8px;padding-top:12px;border-top:1px solid var(--border);">
      Données issues de la jointure SQL : <code>collectes ⟶ collecte_dechets ⟶ dechets ⟶ categories</code>
    </div>`;

    document.getElementById('detail-col-loading').outerHTML = html;

  } catch (err) {
    document.getElementById('detail-col-loading').innerHTML = `
      <div style="text-align:center;padding:24px;color:#E53935;">
        <div style="font-size:28px;margin-bottom:8px;">⚠️</div>
        Impossible de charger les données (API non connectée en mode démo).
        <div style="margin-top:12px;font-size:12px;color:var(--text-muted);">
          La jointure fonctionne en base de données via <code>v_collectes_detail</code>.
        </div>
      </div>`;
  }
}

// ============================================================
//  INIT
// ============================================================
document.addEventListener('DOMContentLoaded', async () => {

  // Recherche déchets
  const searchInput = document.getElementById('search-input');
  if (searchInput) searchInput.addEventListener('input', () => renderTable(searchInput.value));

  // Confirmation jointure quand collecte sélectionnée
  const addCollecteSelect = document.getElementById('add-collecte');
  if (addCollecteSelect) {
    addCollecteSelect.addEventListener('change', () => {
      const confirm = document.getElementById('add-collecte-confirm');
      if (confirm) confirm.style.display = addCollecteSelect.value ? 'block' : 'none';
    });
  }

  // Recherche collectes
  const colSearch = document.getElementById('col-search');
  if (colSearch) colSearch.addEventListener('input', () => renderCollectes(colSearch.value));

  // Color pickers
  const cp1 = document.getElementById('cat-couleur');
  if (cp1) cp1.addEventListener('input', () => {
    document.getElementById('cat-couleur-hex').textContent = cp1.value;
  });
  const cp2 = document.getElementById('ecat-couleur');
  if (cp2) cp2.addEventListener('input', () => {
    document.getElementById('ecat-couleur-hex').textContent = cp2.value;
  });

  // Date max = aujourd'hui
  const today = new Date().toISOString().split('T')[0];
  ['add-date', 'edit-date'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.setAttribute('max', today);
  });
  const addDate = document.getElementById('add-date');
  if (addDate) addDate.value = today;
  const colDate = document.getElementById('col-date');
  if (colDate) colDate.value = today;

  try {
    await loadDechets();
  } catch (err) {
    console.error('Erreur chargement initial :', err);
  }

  navigate('dashboard');
});

// ============================================================
//  EXPORT CSV
// ============================================================
function exportCSV() {
  if (!dechets || dechets.length === 0) {
    alert('Aucune donnée à exporter.');
    return;
  }
  const headers = ['ID', 'Type aliment', 'Quantité (kg)', 'Raison', 'Date', 'Description'];
  const rows = dechets.map(d => [
    d.id,
    '"' + (d.type_aliment || '').replace(/"/g, '""') + '"',
    d.quantite,
    '"' + (d.raison || '').replace(/"/g, '""') + '"',
    d.date_dechet || '',
    '"' + (d.description || '').replace(/"/g, '""') + '"'
  ].join(','));

  const csv = [headers.join(','), ...rows].join('\n');
  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'FoodSave_export_' + new Date().toISOString().split('T')[0] + '.csv';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

// ============================================================
//  EXPORT PDF  (via print window)
// ============================================================
function exportPDF() {
  if (!dechets || dechets.length === 0) {
    alert('Aucune donnée à exporter.');
    return;
  }

  const total = dechets.reduce((s, d) => s + Number(d.quantite), 0);
  const today = new Date().toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric' });

  // Build summary by type
  const byType = {};
  dechets.forEach(d => { byType[d.type_aliment] = (byType[d.type_aliment] || 0) + Number(d.quantite); });
  const typeRows = Object.entries(byType).sort((a, b) => b[1] - a[1])
    .map(([t, v]) => `<tr><td>${esc(t)}</td><td>${v.toFixed(2)} kg</td><td>${((v/total)*100).toFixed(1)}%</td></tr>`).join('');

  const detailRows = dechets.slice(0, 100).map(d =>
    `<tr><td>${d.id}</td><td>${esc(d.type_aliment)}</td><td>${Number(d.quantite).toFixed(2)} kg</td><td>${esc(d.raison)}</td><td>${d.date_dechet || '—'}</td></tr>`
  ).join('');

  const html = `<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"/>
<title>FoodSave – Rapport</title>
<style>
  body { font-family: Arial, sans-serif; margin: 30px; color: #222; }
  h1 { color: #2e7d32; font-size: 1.6rem; margin-bottom: 4px; }
  .subtitle { color: #888; font-size: 0.9rem; margin-bottom: 24px; }
  .stats { display: flex; gap: 24px; margin-bottom: 28px; flex-wrap: wrap; }
  .stat-box { background: #f9f9f9; border-left: 4px solid #4CAF50; padding: 12px 20px; border-radius: 6px; }
  .stat-box h3 { margin: 0; font-size: 1.4rem; color: #2e7d32; }
  .stat-box p { margin: 4px 0 0; font-size: 0.82rem; color: #666; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 28px; font-size: 0.88rem; }
  th { background: #4CAF50; color: white; padding: 9px 12px; text-align: left; }
  td { padding: 8px 12px; border-bottom: 1px solid #eee; }
  tr:nth-child(even) td { background: #f7f7f7; }
  h2 { color: #2e7d32; font-size: 1.1rem; margin-bottom: 10px; }
  .footer { margin-top: 30px; font-size: 0.78rem; color: #aaa; border-top: 1px solid #eee; padding-top: 10px; }
</style></head><body>
<h1>🍃 FoodSave – Rapport de gaspillage alimentaire</h1>
<div class="subtitle">Généré le ${today}</div>

<div class="stats">
  <div class="stat-box"><h3>${total.toFixed(1)} kg</h3><p>Total gaspillé</p></div>
  <div class="stat-box"><h3>${dechets.length}</h3><p>Entrées enregistrées</p></div>
  <div class="stat-box"><h3>${(total * 0.4).toFixed(1)} kg</h3><p>CO₂ économisé (est.)</p></div>
</div>

<h2>📊 Répartition par type d'aliment</h2>
<table>
  <thead><tr><th>Type d'aliment</th><th>Quantité</th><th>Part (%)</th></tr></thead>
  <tbody>${typeRows}</tbody>
</table>

<h2>📋 Détail des entrées (max 100)</h2>
<table>
  <thead><tr><th>#</th><th>Type</th><th>Quantité</th><th>Raison</th><th>Date</th></tr></thead>
  <tbody>${detailRows}</tbody>
</table>

<div class="footer">FoodSave – Zéro gaspillage &bull; Rapport généré automatiquement</div>
</body></html>`;

  const win = window.open('', '_blank');
  win.document.write(html);
  win.document.close();
  win.focus();
  setTimeout(() => win.print(), 600);
}

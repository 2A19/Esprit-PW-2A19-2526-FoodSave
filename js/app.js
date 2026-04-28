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
    dashboard:  '📊 Tableau de bord',
    list:       '🗑️ Liste des Déchets',
    add:        '➕ Ajouter un Déchet',
    historique: '📅 Historique',
    categories: '🏷️ Catégories',
    collectes:  '🚛 Collectes',
  };
  document.getElementById('page-title').textContent = titles[page] || 'FoodSave';

  if (page === 'dashboard')  renderDashboard();
  if (page === 'list')       renderTable();
  if (page === 'historique') renderHistorique();
  if (page === 'categories') loadCategories();
  if (page === 'collectes')  loadCollectes();
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

  try {
    await apiFetch(API_DECHETS, { method: 'POST', body: JSON.stringify(payload) });
    await refresh();
    showAlert('add-alert', '✅ Déchet enregistré avec succès !', 'success');
    document.getElementById('form-add').reset();
    clearErrors('form-add');
    setTimeout(() => navigate('list'), 1200);
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
  const colors = ['#4CAF50','#FFA726','#EF5350','#42A5F5','#AB47BC','#26C6DA'];
  const max = Math.max(...Object.values(byType), 1);
  document.getElementById('chart').innerHTML = Object.entries(byType).map(([type, val], i) => `
    <div class="bar-wrap">
      <div class="bar" data-val="${val.toFixed(1)} kg" style="height:${(val/max)*180}px;background:${colors[i%colors.length]};"></div>
      <span class="bar-label">${type.substring(0, 8)}</span>
    </div>`).join('');
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
function renderHistorique() {
  const sorted = [...dechets].sort((a, b) => b.date_dechet < a.date_dechet ? -1 : 1);
  document.getElementById('historique-list').innerHTML = sorted.map(d => `
    <div style="display:flex;gap:18px;align-items:flex-start;padding:18px 0;border-bottom:1px solid var(--border);">
      <div style="width:46px;height:46px;background:var(--green-light);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;">${typeEmoji(d.type_aliment)}</div>
      <div style="flex:1;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <strong>${esc(d.type_aliment)}</strong>
          <span class="badge badge-orange">${Number(d.quantite).toFixed(3)} ${esc(d.unite)}</span>
        </div>
        <div style="font-size:0.82rem;color:var(--text-muted);margin-top:3px;">${esc(d.raison)} • ${formatDate(d.date_dechet)}</div>
        ${d.notes ? `<div style="font-size:0.8rem;color:var(--text-muted);margin-top:4px;font-style:italic;">${esc(d.notes)}</div>` : ''}
      </div>
    </div>`).join('');
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
  planifiee: { label: '📅 Planifiée',  cls: 'badge-blue'   },
  en_cours:  { label: '🔄 En cours',   cls: 'badge-orange' },
  terminee:  { label: '✅ Terminée',   cls: 'badge-green'  },
  annulee:   { label: '❌ Annulée',    cls: 'badge-gray'   },
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
//  INIT
// ============================================================
document.addEventListener('DOMContentLoaded', async () => {

  // Recherche déchets
  const searchInput = document.getElementById('search-input');
  if (searchInput) searchInput.addEventListener('input', () => renderTable(searchInput.value));

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

/**
 * FoodSave - Gestion des Déchets
 * JavaScript : Validation + CRUD simulé (prêt pour PDO/PHP)
 * Contraintes : AUCUNE validation HTML5 (novalidate sur tous les forms)
 */

// ============================================================
//  DATA STORE (API/MySQL)
// ============================================================
const API_DECHETS_URL = 'api/dechets.php';

let dechets = [];
let users = [];
let editingId = null;
let deleteTargetId = null;
let currentPage = 'dashboard';
let currentScope = 'mine';
let currentUserId = 0;

function mapApiToUi(record) {
  return {
    id: Number(record.id),
    type: String(record.type_aliment || ''),
    quantite: Number(record.quantite || 0),
    unite: String(record.unite || ''),
    date: String(record.date_dechet || ''),
    raison: String(record.raison || ''),
    notes: String(record.notes || ''),
    statut: 'Enregistré',
  };
}

async function loadDechetsFromApi(scope = currentScope) {
  const url = new URL(API_DECHETS_URL, window.location.href);
  if (scope) {
    url.searchParams.set('scope', scope);
  }

  if (scope !== 'all' && currentUserId > 0) {
    url.searchParams.set('user_id', String(currentUserId));
    url.searchParams.set('as_user_id', String(currentUserId));
  }

  const res = await fetch(url, {
    method: 'GET',
    headers: { 'Accept': 'application/json' },
  });

  if (!res.ok) throw new Error('Erreur chargement API');

  const payload = await res.json();
  if (!payload.success || !Array.isArray(payload.data)) {
    throw new Error('Format de reponse API invalide');
  }

  dechets = payload.data.map(mapApiToUi);
}

async function loadUsersFromApi() {
  const url = new URL(API_DECHETS_URL, window.location.href);
  url.searchParams.set('resource', 'users');

  const res = await fetch(url, {
    method: 'GET',
    headers: { 'Accept': 'application/json' },
  });

  if (!res.ok) throw new Error('Erreur chargement utilisateurs');

  const payload = await res.json();
  if (!payload.success || !Array.isArray(payload.data)) {
    throw new Error('Format de reponse utilisateurs invalide');
  }

  users = payload.data.map((u) => ({
    id: Number(u.id),
    nom: String(u.nom || ''),
    prenom: String(u.prenom || ''),
    role: String(u.role || 'user'),
  }));
}

function initUserSelector() {
  const select = document.getElementById('current-user-select');
  if (!select) return;

  select.innerHTML = users
    .map((u) => `<option value="${u.id}">${esc(`${u.prenom} ${u.nom}`.trim())}</option>`)
    .join('');

  if (!users.length) {
    select.innerHTML = '<option value="0">Aucun utilisateur</option>';
    currentUserId = 0;
    return;
  }

  if (!currentUserId || !users.some((u) => u.id === currentUserId)) {
    currentUserId = users[0].id;
  }

  select.value = String(currentUserId);
  updateCurrentUserBadge();

  select.addEventListener('change', async () => {
    currentUserId = Number(select.value || 0);
    updateCurrentUserBadge();
    await refreshDechets();
  });
}

function updateCurrentUserBadge() {
  const user = users.find((u) => u.id === currentUserId);
  const avatar = document.querySelector('.topbar-right .avatar');
  if (!user || !avatar) return;

  const initials = `${(user.prenom[0] || '').toUpperCase()}${(user.nom[0] || '').toUpperCase()}`;
  avatar.textContent = initials || 'U';
  avatar.title = `${user.prenom} ${user.nom}`.trim();
}

async function refreshDechets(scope = currentScope) {
  await loadDechetsFromApi(scope);

  if (currentPage === 'dashboard') renderDashboard();
  if (currentPage === 'list') renderTable();
  if (currentPage === 'historique') renderHistorique();
}

// ============================================================
//  NAVIGATION
// ============================================================
function navigate(page) {
  currentPage = page;
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  const target = document.getElementById('page-' + page);
  if (target) target.classList.add('active');
  const navItem = document.querySelector(`[data-page="${page}"]`);
  if (navItem) navItem.classList.add('active');

  const titles = {
    dashboard: '📊 Tableau de bord',
    list: '🗑️ Liste des Déchets',
    add: '➕ Ajouter un Déchet',
    historique: '📅 Historique',
    profile: '👤 Mon Profil'
  };
  document.getElementById('page-title').textContent = titles[page] || 'FoodSave';

  if (page === 'dashboard') renderDashboard();
  if (page === 'list') renderTable();
  if (page === 'historique') renderHistorique();
}

// ============================================================
//  VALIDATION — AUCUNE HTML5 (conforme contrainte prof)
// ============================================================
function validateField(fieldId, errorId, rules) {
  const field = document.getElementById(fieldId);
  const error = document.getElementById(errorId);
  const val = field.value.trim();
  let msg = '';

  if (rules.required && val === '') {
    msg = 'Ce champ est obligatoire.';
  } else if (rules.minLen && val.length < rules.minLen) {
    msg = `Minimum ${rules.minLen} caractères requis.`;
  } else if (rules.pattern && !rules.pattern.test(val)) {
    msg = rules.patternMsg || 'Format invalide.';
  } else if (rules.min !== undefined && parseFloat(val) < rules.min) {
    msg = `La valeur minimale est ${rules.min}.`;
  } else if (rules.max !== undefined && parseFloat(val) > rules.max) {
    msg = `La valeur maximale est ${rules.max}.`;
  } else if (rules.isDate && val !== '' && isNaN(Date.parse(val))) {
    msg = 'Date invalide.';
  } else if (rules.futureDate && val !== '' && new Date(val) > new Date()) {
    msg = 'La date ne peut pas être dans le futur.';
  }

  if (msg) {
    field.classList.add('error');
    error.textContent = msg;
    error.classList.add('show');
    return false;
  } else {
    field.classList.remove('error');
    error.classList.remove('show');
    return true;
  }
}

function clearFormErrors(formId) {
  const form = document.getElementById(formId);
  form.querySelectorAll('input, select, textarea').forEach(el => el.classList.remove('error'));
  form.querySelectorAll('.error-msg').forEach(el => { el.textContent = ''; el.classList.remove('show'); });
}

function validateDechetForm(formId) {
  const isAdd = formId === 'form-add';
  const prefix = isAdd ? 'add' : 'edit';
  let valid = true;

  const checks = [
    { field: `${prefix}-type`,     error: `${prefix}-type-err`,     rules: { required: true } },
    { field: `${prefix}-quantite`, error: `${prefix}-quantite-err`, rules: { required: true, pattern: /^\d+(\.\d{1,3})?$/, patternMsg: 'Entrez un nombre valide (ex: 1.5)', min: 0.01, max: 9999 } },
    { field: `${prefix}-unite`,    error: `${prefix}-unite-err`,    rules: { required: true } },
    { field: `${prefix}-date`,     error: `${prefix}-date-err`,     rules: { required: true, isDate: true, futureDate: true } },
    { field: `${prefix}-raison`,   error: `${prefix}-raison-err`,   rules: { required: true } },
  ];

  checks.forEach(c => { if (!validateField(c.field, c.error, c.rules)) valid = false; });
  return valid;
}

// ============================================================
//  CRUD OPERATIONS
// ============================================================

/** CREATE */
async function submitAdd() {
  if (!validateDechetForm('form-add')) return;
  if (currentUserId <= 0) {
    showAlert('add-alert', '❌ Aucun utilisateur sélectionné.', 'error');
    return;
  }

  const payload = {
    user_id: currentUserId,
    type_aliment: document.getElementById('add-type').value,
    quantite: parseFloat(document.getElementById('add-quantite').value),
    unite: document.getElementById('add-unite').value,
    date_dechet: document.getElementById('add-date').value,
    raison: document.getElementById('add-raison').value,
    notes: document.getElementById('add-notes').value.trim(),
  };

  try {
    const res = await fetch(API_DECHETS_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(payload),
    });

    const json = await res.json();
    if (!res.ok || !json.success) {
      throw new Error(json.message || 'Erreur API');
    }

    await refreshDechets();
    showAlert('add-alert', '✅ Déchet enregistré avec succès !', 'success');
    document.getElementById('form-add').reset();
    clearFormErrors('form-add');
    setTimeout(() => { navigate('list'); }, 1200);
  } catch (err) {
    showAlert('add-alert', `❌ ${err.message || 'Erreur de connexion au serveur.'}`, 'error');
  }
}

/** READ - render table */
function renderTable(filter = '') {
  const tbody = document.getElementById('table-body');
  let data = [...dechets];
  if (filter) data = data.filter(d =>
    d.type.toLowerCase().includes(filter.toLowerCase()) ||
    d.raison.toLowerCase().includes(filter.toLowerCase())
  );

  if (data.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7">
      <div class="empty-state">
        <div class="icon">🗑️</div>
        <h3>Aucun déchet trouvé</h3>
        <p>Aucun résultat pour cette recherche.</p>
      </div></td></tr>`;
    return;
  }

  tbody.innerHTML = data.map(d => `
    <tr>
      <td><span class="badge badge-gray">#${d.id}</span></td>
      <td><strong>${esc(d.type)}</strong></td>
      <td>${d.quantite} ${esc(d.unite)}</td>
      <td>${formatDate(d.date)}</td>
      <td>${esc(d.raison)}</td>
      <td><span class="badge badge-green">${esc(d.statut)}</span></td>
      <td>
        <div style="display:flex;gap:6px;">
          <button class="btn btn-sm btn-outline" onclick="openEditModal(${d.id})">✏️ Modifier</button>
          <button class="btn btn-sm btn-danger" onclick="openDeleteConfirm(${d.id})">🗑️ Supprimer</button>
        </div>
      </td>
    </tr>`).join('');
}

/** UPDATE - open modal */
function openEditModal(id) {
  const d = dechets.find(x => x.id === id);
  if (!d) return;
  editingId = id;
  clearFormErrors('form-edit');

  document.getElementById('edit-type').value = d.type;
  document.getElementById('edit-quantite').value = d.quantite;
  document.getElementById('edit-unite').value = d.unite;
  document.getElementById('edit-date').value = d.date;
  document.getElementById('edit-raison').value = d.raison;
  document.getElementById('edit-notes').value = d.notes;

  openModal('modal-edit');
}

async function submitEdit() {
  if (!validateDechetForm('form-edit')) return;
  if (!editingId) return;

  try {
    const res = await fetch(API_DECHETS_URL, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        id: editingId,
        as_user_id: currentUserId,
        type_aliment: document.getElementById('edit-type').value,
        quantite: parseFloat(document.getElementById('edit-quantite').value),
        unite: document.getElementById('edit-unite').value,
        date_dechet: document.getElementById('edit-date').value,
        raison: document.getElementById('edit-raison').value,
        notes: document.getElementById('edit-notes').value.trim(),
      }),
    });

    const json = await res.json();
    if (!res.ok || !json.success) {
      throw new Error(json.message || 'Erreur API');
    }

    await refreshDechets();
    closeModal('modal-edit');
    showAlert('list-alert', '✅ Déchet modifié avec succès !', 'success');
  } catch (err) {
    showAlert('list-alert', `❌ ${err.message || 'Erreur lors de la mise à jour.'}`, 'error');
  }
}

/** DELETE */
function openDeleteConfirm(id) {
  deleteTargetId = id;
  const d = dechets.find(x => x.id === id);
  if (!d) return;
  document.getElementById('delete-item-name').textContent = `${d.type} (${d.quantite} ${d.unite})`;
  openModal('modal-delete');
}

async function confirmDelete() {
  if (!deleteTargetId) return;

  try {
    const res = await fetch(API_DECHETS_URL, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        id: deleteTargetId,
        as_user_id: currentUserId,
      }),
    });

    const json = await res.json();
    if (!res.ok || !json.success) {
      throw new Error(json.message || 'Erreur API');
    }

    await refreshDechets();
    closeModal('modal-delete');
    showAlert('list-alert', '🗑️ Déchet supprimé avec succès.', 'success');
  } catch (err) {
    showAlert('list-alert', `❌ ${err.message || 'Erreur lors de la suppression.'}`, 'error');
  } finally {
    deleteTargetId = null;
  }
}

// ============================================================
//  DASHBOARD
// ============================================================
function renderDashboard() {
  // Stats
  const total = dechets.reduce((s, d) => s + d.quantite, 0);
  document.getElementById('stat-total').textContent = total.toFixed(1) + ' kg';
  document.getElementById('stat-count').textContent = dechets.length;
  document.getElementById('stat-today').textContent = getTodayCount();
  document.getElementById('stat-saved').textContent = (total * 0.4).toFixed(1) + ' kg';

  // Chart by type
  renderChart();
  renderTopRaisons();
}

function getTodayCount() {
  const today = new Date().toISOString().split('T')[0];
  return dechets.filter(d => d.date === today).length;
}

function renderChart() {
  const byType = {};
  dechets.forEach(d => { byType[d.type] = (byType[d.type] || 0) + d.quantite; });
  const colors = ['#4CAF50', '#FFA726', '#EF5350', '#42A5F5', '#AB47BC', '#26C6DA'];
  const max = Math.max(...Object.values(byType), 1);
  const container = document.getElementById('chart');
  container.innerHTML = Object.entries(byType).map(([type, val], i) => `
    <div class="bar-wrap">
      <div class="bar" data-val="${val.toFixed(1)} kg" style="height:${(val/max)*180}px;background:${colors[i%colors.length]};"></div>
      <span class="bar-label">${type.substring(0,8)}</span>
    </div>`).join('');
}

function renderTopRaisons() {
  const byRaison = {};
  const total = dechets.reduce((s,d)=>s+d.quantite,0)||1;
  dechets.forEach(d => { byRaison[d.raison] = (byRaison[d.raison]||0) + d.quantite; });
  const sorted = Object.entries(byRaison).sort((a,b)=>b[1]-a[1]).slice(0,4);
  const colors = ['#4CAF50','#FFA726','#EF5350','#42A5F5'];
  document.getElementById('top-raisons').innerHTML = sorted.map(([r,v],i)=>`
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
  const sorted = [...dechets].sort((a,b)=>new Date(b.date)-new Date(a.date));
  const container = document.getElementById('historique-list');
  container.innerHTML = sorted.map(d => `
    <div style="display:flex;gap:18px;align-items:flex-start;padding:18px 0;border-bottom:1px solid var(--border);">
      <div style="width:46px;height:46px;background:var(--green-light);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;">${typeEmoji(d.type)}</div>
      <div style="flex:1;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <strong>${esc(d.type)}</strong>
          <span class="badge badge-orange">${d.quantite} ${esc(d.unite)}</span>
        </div>
        <div style="font-size:0.82rem;color:var(--text-muted);margin-top:3px;">${esc(d.raison)} • ${formatDate(d.date)}</div>
        ${d.notes ? `<div style="font-size:0.8rem;color:var(--text-muted);margin-top:4px;font-style:italic;">${esc(d.notes)}</div>` : ''}
      </div>
    </div>`).join('');
}

// ============================================================
//  MODALS
// ============================================================
function openModal(id) {
  document.getElementById(id).classList.add('open');
}
function closeModal(id) {
  document.getElementById(id).classList.remove('open');
}

// ============================================================
//  ALERTS
// ============================================================
function showAlert(id, msg, type) {
  const el = document.getElementById(id);
  el.className = `alert alert-${type} show`;
  el.innerHTML = `<span>${msg}</span>`;
  setTimeout(() => el.classList.remove('show'), 3500);
}

// ============================================================
//  UTILITIES
// ============================================================
function esc(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function formatDate(d) {
  const dt = new Date(d + 'T00:00:00');
  return dt.toLocaleDateString('fr-FR', { day:'2-digit', month:'short', year:'numeric' });
}
function typeEmoji(t) {
  const map = { 'Légumes':'🥦','Fruits':'🍎','Pain':'🍞','Viande':'🥩','Produits laitiers':'🥛','Poisson':'🐟' };
  return map[t] || '🍽️';
}

// ============================================================
//  SEARCH
// ============================================================
document.addEventListener('DOMContentLoaded', async () => {
  const searchInput = document.getElementById('search-input');
  if (searchInput) {
    searchInput.addEventListener('input', () => renderTable(searchInput.value));
  }

  // Real-time validation on blur
  const addFields = ['add-quantite'];
  addFields.forEach(fid => {
    const el = document.getElementById(fid);
    if (el) el.addEventListener('blur', () => {
      validateField('add-quantite','add-quantite-err',{required:true,pattern:/^\d+(\.\d{1,3})?$/,patternMsg:'Nombre valide requis (ex: 1.5)',min:0.01,max:9999});
    });
  });

  // Set today as max date
  const today = new Date().toISOString().split('T')[0];
  ['add-date','edit-date'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.setAttribute('max', today);
  });

  // Default dates
  const addDate = document.getElementById('add-date');
  if (addDate) addDate.value = today;

  try {
    await loadUsersFromApi();
    initUserSelector();
    await refreshDechets();
  } catch (err) {
    console.error(err);
  }

  navigate('dashboard');
});

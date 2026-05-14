<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>FoodSave – Gestion des Déchets</title>
  <link rel="stylesheet" href="assets/css/style-dark.css" />
  <!-- novalidate sur tous les formulaires : validation JS uniquement -->
</head>
<body>

<!-- ===================== SIDEBAR ===================== -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <img src="assets/images/logo.png" alt="FoodSave" class="sidebar-logo-img" />
    <span class="sidebar-logo-sub" data-i18n="sidebar_logo_sub">Zéro gaspillage</span>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-label" data-i18n="sidebar_nav_label_overview">Tableau de bord</div>

    <a class="nav-item" data-page="dashboard" onclick="navigate('dashboard')">
      <span class="icon">📊</span> <span data-i18n="nav_item_dashboard">Vue d'ensemble</span>
    </a>

    <div class="nav-label" style="margin-top:8px;" data-i18n="sidebar_nav_label_manage">Gestion</div>

    <a class="nav-item" data-page="list" onclick="navigate('list')">
      <span class="icon">🗑️</span> <span data-i18n="nav_item_waste">Déchets</span>
    </a>
    <a class="nav-item" data-page="add" onclick="navigate('add')">
      <span class="icon">➕</span> <span data-i18n="nav_item_new_waste">Nouveau déchet</span>
    </a>
    <a class="nav-item" data-page="categories" onclick="navigate('categories')">
      <span class="icon">🏷️</span> <span data-i18n="nav_item_categories">Catégories</span>
    </a>
    <a class="nav-item" data-page="collectes" onclick="navigate('collectes')">
      <span class="icon">🚛</span> <span data-i18n="nav_item_collects">Collectes</span>
    </a>
    <a class="nav-item" data-page="historique" onclick="navigate('historique')">
      <span class="icon">🔗</span> <span data-i18n="nav_item_history">Jointure</span>
    </a>


  </nav>

  <div class="sidebar-footer">
    <a href="index.php?action=accueil" style="color:rgba(255,255,255,0.5);text-decoration:none;font-size:12px;display:block;margin-bottom:6px;">🏠 Accueil</a>
    <a href="index.php?action=dashboard" style="color:rgba(255,255,255,0.5);text-decoration:none;font-size:12px;display:block;margin-bottom:6px;">👤 Mon espace</a>
    <a href="index.php?action=evenements" style="color:rgba(255,255,255,0.5);text-decoration:none;font-size:12px;display:block;margin-bottom:6px;">📅 Événements</a>
    <a href="index.php?action=blog" style="color:rgba(255,255,255,0.5);text-decoration:none;font-size:12px;display:block;margin-bottom:6px;">📰 Blog</a>
    FoodSave — NextWave Team<br>© 2026
  </div>
</aside>

<!-- ===================== MAIN ===================== -->
<div class="main">

  <!-- TOPBAR -->
  <header class="topbar">
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
      <h1 class="topbar-title" id="page-title">Tableau de bord</h1>
      <select id="language-selector" onchange="setLanguage(this.value)" style="padding:8px 10px;border-radius:10px;border:1px solid #ccc;background:#fff;color:#222;cursor:pointer;">
        <option value="fr">Français</option>
        <option value="en">English</option>
      </select>
    </div>
    <div class="topbar-right">
      <div class="topbar-pill">
        <div class="topbar-pill-dot"></div>
        <span data-i18n="topbar_active">Actif</span>
      </div>
      <div class="avatar" title="FoodSave">FS</div>
    </div>
  </header>

  <!-- ===== PAGE: DASHBOARD ===== -->
  <div id="page-dashboard" class="page">

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon green">🗑️</div>
        <div class="stat-info">
          <h3 id="stat-total">—</h3>
          <p data-i18n="stat_total_label">Total gaspillé</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange">📋</div>
        <div class="stat-info">
          <h3 id="stat-count">—</h3>
          <p data-i18n="stat_count_label">Entrées enregistrées</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon blue">📅</div>
        <div class="stat-info">
          <h3 id="stat-today">—</h3>
          <p data-i18n="stat_today_label">Ajouts aujourd'hui</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red">♻️</div>
        <div class="stat-info">
          <h3 id="stat-saved">—</h3>
          <p data-i18n="stat_saved_label">CO₂ économisé (est.)</p>
        </div>
      </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end;margin-bottom:16px;">
      <button onclick="exportCSV()" class="btn btn-outline" style="display:flex;align-items:center;gap:7px;padding:9px 18px;border-radius:10px;font-size:0.88rem;font-weight:600;cursor:pointer;background:#fff;border:1.5px solid #4CAF50;color:#2e7d32;transition:background 0.15s;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><polyline points="9 15 12 18 15 15"/></svg>
        <span data-i18n="button_export_csv">Exporter CSV</span>
      </button>
      <button onclick="exportPDF()" class="btn btn-outline" style="display:flex;align-items:center;gap:7px;padding:9px 18px;border-radius:10px;font-size:0.88rem;font-weight:600;cursor:pointer;background:#fff;border:1.5px solid #EF5350;color:#c62828;transition:background 0.15s;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="14" x2="12" y2="14"/><line x1="8" y1="18" x2="16" y2="18"/></svg>
        <span data-i18n="button_export_pdf">Exporter PDF</span>
      </button>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">
      <div class="card">
        <div class="card-header">
          <h2 data-i18n="dashboard_chart_title">🥧 Répartition par type d'aliment</h2>
        </div>
        <div class="card-body" style="display:flex;align-items:center;justify-content:center;gap:28px;flex-wrap:wrap;">
          <div id="chart" style="display:flex;align-items:center;justify-content:center;min-height:220px;"></div>
          <div id="chart-legend" style="display:flex;flex-direction:column;gap:10px;min-width:130px;"></div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h2 data-i18n="dashboard_top_causes_title">🔍 Principales causes</h2>
        </div>
        <div class="card-body" id="top-raisons"></div>
      </div>
    </div>

    <div style="margin-top:22px;" class="card">
      <div class="card-header">
        <h2>💡 Conseil du jour</h2>
      </div>
      <div class="card-body">
        <div class="conseil-card">
          <span class="conseil-card-icon">🥦</span>
          <div>
            <strong>Conservez vos légumes correctement !</strong>
            <p>Les légumes à feuilles se conservent enveloppés dans un linge humide au réfrigérateur jusqu'à 5 jours. Évitez de les laver avant stockage.</p>
          </div>
        </div>
      </div>
    </div>

    <div style="margin-top:22px;" class="card">
      <div class="card-header">
        <div>
          <h2 data-i18n="dashboard_advanced_jobs_title">🚀 Métiers avancés</h2>
          <p style="margin:6px 0 0;color:var(--text-muted);font-size:0.95rem;font-weight:500;" data-i18n="dashboard_advanced_jobs_desc">Partagez, planifiez, scannez, écoutez et interagissez avec l'assistant IA depuis le front office.</p>
        </div>
      </div>
      <div class="card-body">
        <div class="tool-grid">
          <div class="tool-card">
            <div>
              <h3 data-i18n="tool_email_title">✉️ Email métier</h3>
              <p data-i18n="tool_email_desc">Envoyez rapidement une proposition de métier avancé par email à un contact.</p>
            </div>
            <button class="btn btn-outline" type="button" onclick="sendMetierEmail()">Envoyer Email</button>
          </div>
          <div class="tool-card">
            <div>
              <h3 data-i18n="tool_facebook_title">📣 Partage Facebook</h3>
              <p data-i18n="tool_facebook_desc">Diffusez le lien FoodSave et incitez vos partenaires à adopter les métiers avancés.</p>
              <small id="public-url-display" style="color:#888;font-size:0.78em;"></small>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
              <button class="btn btn-outline" type="button" onclick="shareOnFacebook()">Partager</button>
              <button class="btn btn-outline" type="button" onclick="configurePublicUrl()" title="Configurer l'URL publique">⚙️ URL</button>
            </div>
          </div>
          <div class="tool-card">
            <div>
              <h3 data-i18n="tool_calendar_title">📅 Calendrier</h3>
              <p data-i18n="tool_calendar_desc">Ajoutez un rendez-vous métier ou une réunion de collecte directement dans votre calendrier.</p>
            </div>
            <button class="btn btn-outline" type="button" onclick="addCalendarEvent()">Ajouter au calendrier</button>
          </div>
          <div class="tool-card">
            <div>
              <h3 data-i18n="tool_voice_title">🔊 Lecture vocale</h3>
              <p data-i18n="tool_voice_desc">Le tableau de bord peut être lu à voix haute pour un suivi vocal des métiers.</p>
            </div>
            <button class="btn btn-outline" type="button" onclick="speakOverview()">Lire à voix haute</button>
          </div>

          <div class="tool-card">
            <div>
              <h3 data-i18n="tool_ai_title">🤖 Assistant IA</h3>
              <p data-i18n="tool_ai_desc">Posez une question et obtenez une réponse intelligente sur le gaspillage alimentaire.</p>
            </div>
            <button class="btn btn-outline" type="button" onclick="focusAssistant()">Ouvrir assistant</button>
          </div>
        </div>

        <div class="tool-extras">
          <!-- qr-panel métier supprimé -->
          <div class="assistant-panel">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
              <div>
                <h3 style="margin:0 0 8px;font-size:1rem;" data-i18n="assistant_panel_title">Assistant IA</h3>
                <p style="margin:0;color:var(--text-muted);font-size:0.95rem;" data-i18n="assistant_panel_desc">Posez une question en lien avec les métiers, la collecte ou la réduction du gaspillage.</p>
              </div>
              <button class="btn btn-primary" type="button" onclick="askAssistant()" data-i18n="assistant_question_button">💬 Question rapide</button>
            </div>
            <textarea id="assistant-prompt" placeholder="Ex : Comment optimiser les collectes alimentaires aujourd'hui ?" data-i18n-placeholder="assistant_prompt_placeholder"></textarea>
            <div class="assistant-actions">
              <button class="btn btn-sm btn-outline" type="button" onclick="setAssistantPrompt('Donne-moi des conseils pour réduire le gaspillage alimentaire dans les métiers de la restauration.')">Conseil restauration</button>
              <button class="btn btn-sm btn-outline" type="button" onclick="setAssistantPrompt('Comment organiser une collecte locale efficace ?')">Collecte locale</button>
              <button class="btn btn-sm btn-outline" type="button" onclick="setAssistantPrompt('Quels outils utiliser pour gérer des métiers avancés ?')">Outils métiers</button>
            </div>
            <div class="assistant-response" id="assistant-response" data-i18n="assistant_response_placeholder">Réponse de l'assistant IA ici.</div>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /dashboard -->

  <!-- ===== PAGE: LIST ===== -->
  <div id="page-list" class="page">

    <div id="list-alert" class="alert"></div>

    <div class="card">
      <div class="card-header">
        <h2 data-i18n="list_title">🗑️ Liste des déchets alimentaires</h2>
        <div style="display:flex;gap:12px;align-items:center;">
          <div class="search-bar">
            <span class="search-icon">🔍</span>
            <input type="text" id="search-input" placeholder="Rechercher..." data-i18n-placeholder="search_placeholder" />
          </div>
          <button class="btn btn-primary" onclick="navigate('add')"><span data-i18n="button_add">➕ Ajouter</span></button>
        </div>
      </div>

      <div class="table-wrap">
        <table id="dechets-table">
          <thead>
            <tr>
              <th class="sortable" data-col="id" onclick="sortTable('id')" title="Trier par ID">
                <span data-i18n="table_th_id">ID</span><span class="sort-icon" id="sort-icon-id">↕</span>
              </th>
              <th class="sortable" data-col="type_aliment" onclick="sortTable('type_aliment')" title="Trier par type">
                <span data-i18n="table_th_type">Type d'aliment</span><span class="sort-icon" id="sort-icon-type_aliment">↕</span>
              </th>
              <th class="sortable" data-col="quantite" onclick="sortTable('quantite')" title="Trier par quantité">
                <span data-i18n="table_th_quantity">Quantité</span><span class="sort-icon" id="sort-icon-quantite">↕</span>
              </th>
              <th class="sortable" data-col="date_dechet" onclick="sortTable('date_dechet')" title="Trier par date">
                <span data-i18n="table_th_date">Date</span><span class="sort-icon" id="sort-icon-date_dechet">↕</span>
              </th>
              <th class="sortable" data-col="raison" onclick="sortTable('raison')" title="Trier par raison">
                <span data-i18n="table_th_reason">Raison</span><span class="sort-icon" id="sort-icon-raison">↕</span>
              </th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="table-body">
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">Chargement...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div><!-- /list -->

  <!-- ===== PAGE: ADD ===== -->
  <div id="page-add" class="page">
    <div class="card" style="max-width:720px;margin:0 auto;">
      <div class="card-header">
        <h2>➕ Enregistrer un nouveau déchet</h2>
      </div>
      <div class="card-body">

        <div id="add-alert" class="alert"></div>

        <!-- novalidate : validation JS uniquement, pas HTML5 -->
        <form id="form-add" novalidate onsubmit="return false;">
          <div class="form-grid">

            <div class="form-group">
              <label for="add-type">Type d'aliment *</label>
              <input type="text" id="add-type" placeholder="Ex : Courgettes, Pain, Yaourts…" />
              <span class="error-msg" id="add-type-err"></span>
            </div>

            <div class="form-group">
              <label for="add-raison">Raison du gaspillage *</label>
              <input type="text" id="add-raison" placeholder="Ex : Date expirée, Surproduction…" />
              <span class="error-msg" id="add-raison-err"></span>
            </div>

            <div class="form-group">
              <label for="add-quantite">Quantité *</label>
              <input type="text" id="add-quantite" placeholder="Ex: 1.5" />
              <span class="error-msg" id="add-quantite-err"></span>
            </div>

            <div class="form-group">
              <label for="add-unite">Unité *</label>
              <select id="add-unite">
                <option value="">— Choisir —</option>
                <option>kg</option>
                <option>g</option>
                <option>L</option>
                <option>ml</option>
                <option>unité(s)</option>
              </select>
              <span class="error-msg" id="add-unite-err"></span>
            </div>

            <div class="form-group">
              <label for="add-date">Date *</label>
              <input type="date" id="add-date" />
              <span class="error-msg" id="add-date-err"></span>
            </div>

            <div class="form-group">
              <label for="add-notes">Notes (optionnel)</label>
              <input type="text" id="add-notes" placeholder="Informations complémentaires..." />
            </div>

            <!-- Rattachement jointure -->
            <div class="form-group span2" style="background:var(--bg-secondary);border:1.5px solid var(--primary-light,#a5d6a7);border-radius:10px;padding:14px 16px;">
              <label for="add-collecte" style="display:flex;align-items:center;gap:6px;font-weight:700;">
                🔗 Rattacher à une collecte
                <span style="font-weight:400;font-size:11px;color:var(--text-muted);">(optionnel — sauvegarde automatique dans la jointure)</span>
              </label>
              <select id="add-collecte" style="margin-top:6px;width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:var(--white);color:var(--text);">
                <option value="">— Aucune collecte —</option>
              </select>
              <div id="add-collecte-confirm" style="display:none;margin-top:8px;font-size:12px;color:#2e7d32;font-weight:600;">
                ✅ Ce déchet sera automatiquement ajouté à la jointure lors de l'enregistrement.
              </div>
            </div>

            <div class="form-actions">
              <button class="btn btn-outline" type="button" onclick="document.getElementById('form-add').reset();clearFormErrors('form-add');">🔄 Réinitialiser</button>
              <button class="btn btn-primary" type="button" onclick="submitAdd()">💾 Enregistrer</button>
            </div>

          </div>
        </form>
      </div>
    </div>
  </div><!-- /add -->

  <!-- ===== PAGE: HISTORIQUE ===== -->
  <div id="page-historique" class="page">

    <!-- Header -->
    <div style="margin-bottom:24px;">
      <h2 style="font-size:1.4rem;font-weight:800;margin:0 0 4px;">🔗 Tableau de Jointure</h2>
      <p style="color:var(--text-muted);font-size:0.875rem;margin:0;">
        <code style="background:var(--bg-secondary);padding:2px 7px;border-radius:5px;font-size:12px;">collectes ⟶ collecte_dechets ⟶ dechets ⟶ categories</code>
      </p>
    </div>

    <!-- KPIs jointure -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px;" id="join-kpis">
      <div style="background:var(--white);border-radius:var(--radius);padding:16px;border:1px solid var(--border);">
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:6px;">Collectes actives</div>
        <div style="font-size:28px;font-weight:800;color:var(--primary);" id="jkpi-col">—</div>
      </div>
      <div style="background:var(--white);border-radius:var(--radius);padding:16px;border:1px solid var(--border);">
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:6px;">Déchets liés</div>
        <div style="font-size:28px;font-weight:800;color:#f57c00;" id="jkpi-dec">—</div>
      </div>
      <div style="background:var(--white);border-radius:var(--radius);padding:16px;border:1px solid var(--border);">
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:6px;">Catégories présentes</div>
        <div style="font-size:28px;font-weight:800;color:#1565c0;" id="jkpi-cat">—</div>
      </div>
      <div style="background:var(--white);border-radius:var(--radius);padding:16px;border:1px solid var(--border);">
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:6px;">Total récupéré</div>
        <div style="font-size:28px;font-weight:800;color:#6a1b9a;" id="jkpi-kg">— kg</div>
      </div>
    </div>

    <!-- Filtres -->
    <div style="background:var(--white);border-radius:var(--radius);padding:16px;border:1px solid var(--border);margin-bottom:18px;display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
      <input type="text" id="join-search" placeholder="🔍 Rechercher collecte, déchet, catégorie…"
        style="flex:1;min-width:200px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:var(--bg-secondary);color:var(--text);"
        oninput="renderJointure()" />
      <select id="join-filter-statut" onchange="renderJointure()"
        style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:var(--bg-secondary);color:var(--text);">
        <option value="">Tous statuts</option>
        <option value="terminee">✅ Terminée</option>
        <option value="en_cours">🔄 En cours</option>
        <option value="planifiee">📅 Planifiée</option>
        <option value="annulee">❌ Annulée</option>
      </select>
      <select id="join-filter-cat" onchange="renderJointure()"
        style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:var(--bg-secondary);color:var(--text);">
        <option value="">Toutes catégories</option>
      </select>
      <span id="join-count" style="font-size:13px;color:var(--text-muted);white-space:nowrap;"></span>
    </div>

    <!-- Tableau jointure -->
    <div style="background:var(--white);border-radius:var(--radius);border:1px solid var(--border);overflow:hidden;">
      <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
          <thead>
            <tr style="background:var(--bg-secondary);border-bottom:2px solid var(--border);">
              <th style="padding:12px 14px;text-align:left;font-weight:600;color:var(--text-muted);white-space:nowrap;">🚛 Collecte</th>
              <th style="padding:12px 14px;text-align:left;font-weight:600;color:var(--text-muted);white-space:nowrap;">📅 Date</th>
              <th style="padding:12px 14px;text-align:left;font-weight:600;color:var(--text-muted);white-space:nowrap;">📍 Lieu</th>
              <th style="padding:12px 14px;text-align:left;font-weight:600;color:var(--text-muted);white-space:nowrap;">🍽️ Déchet récupéré</th>
              <th style="padding:12px 14px;text-align:right;font-weight:600;color:var(--text-muted);white-space:nowrap;">⚖️ Quantité</th>
              <th style="padding:12px 14px;text-align:left;font-weight:600;color:var(--text-muted);white-space:nowrap;">❓ Raison</th>
              <th style="padding:12px 14px;text-align:left;font-weight:600;color:var(--text-muted);white-space:nowrap;">🏷️ Catégorie</th>
              <th style="padding:12px 14px;text-align:center;font-weight:600;color:var(--text-muted);white-space:nowrap;">Statut</th>
            </tr>
          </thead>
          <tbody id="join-tbody">
            <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted);">Chargement…</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </div><!-- /jointure -->

  <!-- ===== PAGE: PROFILE ===== -->
  <div id="page-profile" class="page">
    <div class="card" style="max-width:600px;margin:0 auto;">
      <div class="card-header">
        <h2>👤 Mon profil</h2>
      </div>
      <div class="card-body">
        <div style="display:flex;align-items:center;gap:20px;margin-bottom:28px;padding:20px;background:var(--bg);border-radius:12px;">
          <div style="width:64px;height:64px;background:var(--green);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:900;color:white;">FK</div>
          <div>
            <h3 style="font-size:1.15rem;font-weight:800;">Faten Karoui</h3>
            <p style="color:var(--text-muted);font-size:0.88rem;">Esprit2526-2A19 · NextWave Team</p>
          </div>
        </div>
        <form novalidate onsubmit="return false;">
          <div class="form-grid">
            <div class="form-group">
              <label>Prénom</label>
              <input type="text" value="Faten" />
            </div>
            <div class="form-group">
              <label>Nom</label>
              <input type="text" value="Karoui" />
            </div>
            <div class="form-group span2">
              <label>Email</label>
              <input type="text" value="faten.karoui@esprit.tn" />
            </div>
            <div class="form-actions">
              <button class="btn btn-primary" type="button">💾 Sauvegarder</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div><!-- /profile -->

  <!-- ===== PAGE: CATEGORIES ===== -->
  <div id="page-categories" class="page">

    <div id="cat-alert" class="alert"></div>

    <div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));margin-bottom:28px;">
      <div class="stat-card">
        <div class="stat-icon green">🏷️</div>
        <div class="stat-info"><h3 id="cat-stat-total">—</h3><p>Total catégories</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange">🗑️</div>
        <div class="stat-info"><h3 id="cat-stat-dechets">—</h3><p>Déchets catégorisés</p></div>
      </div>
    </div>

    <!-- Formulaire ajout rapide -->
    <div class="card" style="margin-bottom:28px;">
      <div class="card-header">
        <h2>➕ Nouvelle catégorie</h2>
        <button class="btn btn-outline btn-sm" onclick="toggleCatForm()">▾ Saisie</button>
      </div>
      <div class="card-body" id="cat-form-wrap" style="display:none;">
        <form id="form-cat" novalidate onsubmit="return false;">
          <div class="form-grid">
            <div class="form-group">
              <label for="cat-nom">Nom *</label>
              <input type="text" id="cat-nom" placeholder="Ex: Céréales" />
              <span class="error-msg" id="cat-nom-err"></span>
            </div>
            <div class="form-group">
              <label for="cat-icone">Icône (emoji)</label>
              <input type="text" id="cat-icone" placeholder="Ex: 🌾" maxlength="4" style="font-size:1.4rem;" />
            </div>
            <div class="form-group">
              <label for="cat-couleur">Couleur</label>
              <div style="display:flex;gap:10px;align-items:center;">
                <input type="color" id="cat-couleur" value="#4CAF50" style="width:50px;height:40px;padding:2px;border:1px solid var(--border);border-radius:8px;cursor:pointer;" />
                <span id="cat-couleur-hex" style="font-size:0.85rem;color:var(--text-muted);">#4CAF50</span>
              </div>
            </div>
            <div class="form-group span2">
              <label for="cat-description">Description</label>
              <input type="text" id="cat-description" placeholder="Description optionnelle..." />
            </div>
            <div class="form-actions">
              <button class="btn btn-outline" type="button" onclick="resetCatForm()">🔄 Réinitialiser</button>
              <button class="btn btn-primary" type="button" onclick="submitCategory()">💾 Enregistrer</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Grille des catégories -->
    <div class="card">
      <div class="card-header">
        <h2>🏷️ Liste des catégories</h2>
      </div>
      <div class="card-body">
        <div id="cat-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;">
          <div style="text-align:center;padding:40px;color:var(--text-muted);">Chargement...</div>
        </div>
      </div>
    </div>
  </div><!-- /categories -->

  <!-- ===== PAGE: COLLECTES ===== -->
  <div id="page-collectes" class="page">

    <div id="col-alert" class="alert"></div>

    <div class="stats-grid" style="margin-bottom:28px;">
      <div class="stat-card">
        <div class="stat-icon green">🚛</div>
        <div class="stat-info"><h3 id="col-stat-total">—</h3><p>Total collectes</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange">✅</div>
        <div class="stat-info"><h3 id="col-stat-terminees">—</h3><p>Terminées</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon blue">🔄</div>
        <div class="stat-info"><h3 id="col-stat-encours">—</h3><p>En cours</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red">📅</div>
        <div class="stat-info"><h3 id="col-stat-planifiees">—</h3><p>Planifiées</p></div>
      </div>
    </div>

    <!-- Bouton + formulaire -->
    <div class="card" style="margin-bottom:28px;">
      <div class="card-header">
        <h2>➕ Nouvelle collecte</h2>
        <button class="btn btn-outline btn-sm" onclick="toggleColForm()">▾ Saisie</button>
      </div>
      <div class="card-body" id="col-form-wrap" style="display:none;">
        <form id="form-col" novalidate onsubmit="return false;">
          <div class="form-grid">
            <div class="form-group span2">
              <label for="col-titre">Titre *</label>
              <input type="text" id="col-titre" placeholder="Ex: Collecte Marché Central" />
              <span class="error-msg" id="col-titre-err"></span>
            </div>
            <div class="form-group">
              <label for="col-lieu">Lieu *</label>
              <input type="text" id="col-lieu" placeholder="Ex: Marché Bab El Bhar, Tunis" />
              <span class="error-msg" id="col-lieu-err"></span>
            </div>
            <div class="form-group">
              <label for="col-date">Date *</label>
              <input type="date" id="col-date" />
              <span class="error-msg" id="col-date-err"></span>
            </div>
            <div class="form-group">
              <label for="col-quantite">Quantité estimée (kg)</label>
              <input type="text" id="col-quantite" placeholder="Ex: 12.5" />
            </div>
            <div class="form-group">
              <label for="col-statut">Statut</label>
              <select id="col-statut">
                <option value="planifiee">📅 Planifiée</option>
                <option value="en_cours">🔄 En cours</option>
                <option value="terminee">✅ Terminée</option>
                <option value="annulee">❌ Annulée</option>
              </select>
            </div>
            <div class="form-group span2">
              <label for="col-description">Description</label>
              <input type="text" id="col-description" placeholder="Détails de la collecte..." />
            </div>
            <div class="form-actions">
              <button class="btn btn-outline" type="button" onclick="resetColForm()">🔄 Réinitialiser</button>
              <button class="btn btn-primary" type="button" onclick="submitCollecte()">💾 Enregistrer</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Tableau collectes -->
    <div class="card">
      <div class="card-header">
        <h2>🚛 Liste des collectes</h2>
        <div class="search-bar">
          <span class="search-icon">🔍</span>
          <input type="text" id="col-search" placeholder="Filtrer par lieu ou titre..." />
        </div>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Titre</th>
              <th>Lieu</th>
              <th>Date</th>
              <th>Quantité</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="col-table-body">
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">Chargement...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div><!-- /collectes -->

</div><!-- /main -->

<!-- ===================== MODAL: EDIT ===================== -->
<div class="modal-overlay" id="modal-edit">
  <div class="modal">
    <div class="modal-header">
      <h3>✏️ Modifier le déchet</h3>
      <button class="modal-close" onclick="closeModal('modal-edit')">✕</button>
    </div>
    <form id="form-edit" novalidate onsubmit="return false;">
      <div class="form-grid">
        <div class="form-group">
          <label for="edit-type">Type d'aliment *</label>
          <input type="text" id="edit-type" placeholder="Ex : Courgettes, Pain, Yaourts…" />
          <span class="error-msg" id="edit-type-err"></span>
        </div>
        <div class="form-group">
          <label for="edit-raison">Raison *</label>
          <input type="text" id="edit-raison" placeholder="Ex : Date expirée, Surproduction…" />
          <span class="error-msg" id="edit-raison-err"></span>
        </div>
        <div class="form-group">
          <label for="edit-quantite">Quantité *</label>
          <input type="text" id="edit-quantite" placeholder="Ex: 1.5" />
          <span class="error-msg" id="edit-quantite-err"></span>
        </div>
        <div class="form-group">
          <label for="edit-unite">Unité *</label>
          <select id="edit-unite">
            <option value="">— Choisir —</option>
            <option>kg</option><option>g</option><option>L</option><option>ml</option><option>unité(s)</option>
          </select>
          <span class="error-msg" id="edit-unite-err"></span>
        </div>
        <div class="form-group">
          <label for="edit-date">Date *</label>
          <input type="date" id="edit-date" />
          <span class="error-msg" id="edit-date-err"></span>
        </div>
        <div class="form-group">
          <label for="edit-notes">Notes</label>
          <input type="text" id="edit-notes" />
        </div>
        <div class="form-actions">
          <button class="btn btn-outline" type="button" onclick="closeModal('modal-edit')">Annuler</button>
          <button class="btn btn-primary" type="button" onclick="submitEdit()">💾 Enregistrer</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- ===================== MODAL: DELETE CONFIRM ===================== -->
<div class="modal-overlay confirm-modal" id="modal-delete">
  <div class="modal" style="max-width:420px;">
    <div class="modal-header">
      <h3>⚠️ Confirmation</h3>
      <button class="modal-close" onclick="closeModal('modal-delete')">✕</button>
    </div>
    <div class="modal-body">
      <div class="confirm-icon">🗑️</div>
      <h4>Supprimer ce déchet ?</h4>
      <p>Vous êtes sur le point de supprimer :<br>
      <strong id="delete-item-name" style="color:var(--text);"></strong></p>
      <p style="margin-top:8px;color:#E53935;">Cette action est irréversible.</p>
    </div>
    <div class="modal-footer" style="margin-top:24px;">
      <button class="btn btn-outline" onclick="closeModal('modal-delete')">Annuler</button>
      <button class="btn btn-danger" onclick="confirmDelete()">🗑️ Supprimer</button>
    </div>
  </div>
</div>

<!-- ===================== MODAL: EDIT CATEGORY ===================== -->
<div class="modal-overlay" id="modal-edit-cat">
  <div class="modal">
    <div class="modal-header">
      <h3>✏️ Modifier la catégorie</h3>
      <button class="modal-close" onclick="closeModal('modal-edit-cat')">✕</button>
    </div>
    <form id="form-edit-cat" novalidate onsubmit="return false;">
      <div class="form-grid">
        <div class="form-group">
          <label for="ecat-nom">Nom *</label>
          <input type="text" id="ecat-nom" />
          <span class="error-msg" id="ecat-nom-err"></span>
        </div>
        <div class="form-group">
          <label for="ecat-icone">Icône (emoji)</label>
          <input type="text" id="ecat-icone" maxlength="4" style="font-size:1.4rem;" />
        </div>
        <div class="form-group">
          <label for="ecat-couleur">Couleur</label>
          <div style="display:flex;gap:10px;align-items:center;">
            <input type="color" id="ecat-couleur" style="width:50px;height:40px;padding:2px;border:1px solid var(--border);border-radius:8px;cursor:pointer;" />
            <span id="ecat-couleur-hex" style="font-size:0.85rem;color:var(--text-muted);"></span>
          </div>
        </div>
        <div class="form-group span2">
          <label for="ecat-description">Description</label>
          <input type="text" id="ecat-description" />
        </div>
        <div class="form-actions">
          <button class="btn btn-outline" type="button" onclick="closeModal('modal-edit-cat')">Annuler</button>
          <button class="btn btn-primary" type="button" onclick="submitEditCategory()">💾 Enregistrer</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- ===================== MODAL: EDIT COLLECTE ===================== -->
<div class="modal-overlay" id="modal-edit-col">
  <div class="modal">
    <div class="modal-header">
      <h3>✏️ Modifier la collecte</h3>
      <button class="modal-close" onclick="closeModal('modal-edit-col')">✕</button>
    </div>
    <form id="form-edit-col" novalidate onsubmit="return false;">
      <div class="form-grid">
        <div class="form-group span2">
          <label for="ecol-titre">Titre *</label>
          <input type="text" id="ecol-titre" />
          <span class="error-msg" id="ecol-titre-err"></span>
        </div>
        <div class="form-group">
          <label for="ecol-lieu">Lieu *</label>
          <input type="text" id="ecol-lieu" />
          <span class="error-msg" id="ecol-lieu-err"></span>
        </div>
        <div class="form-group">
          <label for="ecol-date">Date *</label>
          <input type="date" id="ecol-date" />
          <span class="error-msg" id="ecol-date-err"></span>
        </div>
        <div class="form-group">
          <label for="ecol-quantite">Quantité (kg)</label>
          <input type="text" id="ecol-quantite" />
        </div>
        <div class="form-group">
          <label for="ecol-statut">Statut</label>
          <select id="ecol-statut">
            <option value="planifiee">📅 Planifiée</option>
            <option value="en_cours">🔄 En cours</option>
            <option value="terminee">✅ Terminée</option>
            <option value="annulee">❌ Annulée</option>
          </select>
        </div>
        <div class="form-group span2">
          <label for="ecol-description">Description</label>
          <input type="text" id="ecol-description" />
        </div>
        <div class="form-actions">
          <button class="btn btn-outline" type="button" onclick="closeModal('modal-edit-col')">Annuler</button>
          <button class="btn btn-primary" type="button" onclick="submitEditCollecte()">💾 Enregistrer</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- ===================== MODAL: DELETE COLLECTE ===================== -->
<div class="modal-overlay confirm-modal" id="modal-delete-col">
  <div class="modal" style="max-width:420px;">
    <div class="modal-header">
      <h3>⚠️ Confirmation</h3>
      <button class="modal-close" onclick="closeModal('modal-delete-col')">✕</button>
    </div>
    <div class="modal-body">
      <div class="confirm-icon">🚛</div>
      <h4>Supprimer cette collecte ?</h4>
      <p>Vous êtes sur le point de supprimer :<br>
      <strong id="delete-col-name" style="color:var(--text);"></strong></p>
      <p style="margin-top:8px;color:#E53935;">Cette action est irréversible.</p>
    </div>
    <div class="modal-footer" style="margin-top:24px;">
      <button class="btn btn-outline" onclick="closeModal('modal-delete-col')">Annuler</button>
      <button class="btn btn-danger" onclick="confirmDeleteCollecte()">🗑️ Supprimer</button>
    </div>
  </div>
</div>

<!-- ===================== MODAL: DELETE CATEGORY ===================== -->
<div class="modal-overlay confirm-modal" id="modal-delete-cat">
  <div class="modal" style="max-width:420px;">
    <div class="modal-header">
      <h3>⚠️ Confirmation</h3>
      <button class="modal-close" onclick="closeModal('modal-delete-cat')">✕</button>
    </div>
    <div class="modal-body">
      <div class="confirm-icon">🏷️</div>
      <h4>Supprimer cette catégorie ?</h4>
      <p>Vous êtes sur le point de supprimer :<br>
      <strong id="delete-cat-name" style="color:var(--text);"></strong></p>
      <p style="margin-top:8px;color:#E53935;">Les déchets liés ne seront pas supprimés.</p>
    </div>
    <div class="modal-footer" style="margin-top:24px;">
      <button class="btn btn-outline" onclick="closeModal('modal-delete-cat')">Annuler</button>
      <button class="btn btn-danger" onclick="confirmDeleteCategory()">🗑️ Supprimer</button>
    </div>
  </div>
</div>

<!-- ===================== MODAL: DÉTAIL COLLECTE (Jointure 3 entités) ===================== -->
<div class="modal-overlay" id="modal-detail-col">
  <div class="modal" style="max-width:680px;">
    <div class="modal-header">
      <h3>🔗 Détail de la collecte</h3>
      <button class="modal-close" onclick="closeModal('modal-detail-col')">✕</button>
    </div>
    <div class="modal-body" id="detail-col-body" style="padding:0 24px 24px;">
      <!-- rempli dynamiquement -->
    </div>
  </div>
</div>

<script src="assets/js/app.js?v=5"></script>
<!-- ══ MODALE QR PAR DÉCHET ══ -->
<div class="modal-overlay" id="dechet-qr-modal" onclick="if(event.target===this)closeDechetQrModal()">
  <div class="modal" style="max-width:400px;text-align:center;">
    <div class="modal-header">
      <h3 class="modal-title" id="dechet-qr-title">📷 QR Code</h3>
      <button class="modal-close" onclick="closeDechetQrModal()">✕</button>
    </div>
    <div class="modal-body">
      <img id="dechet-qr-img" src="" alt="QR Code déchet"
           style="width:280px;height:280px;border-radius:12px;border:2px solid var(--border);display:block;margin:0 auto 16px;">
      <div id="dechet-qr-info" style="text-align:left;background:var(--surface-alt,#f8f9fa);border-radius:8px;padding:12px;font-size:0.9rem;line-height:1.7;margin-bottom:12px;color:#1a2e1f;"></div>
      <p style="font-size:0.78rem;color:#4a6057;word-break:break-all;margin-bottom:16px;" id="dechet-qr-url"></p>
      <button class="btn btn-primary" onclick="downloadDechetQr()">⬇️ Télécharger</button>
    </div>
  </div>
</div>

<!-- ══ MODALE FICHE DÉCHET (scan QR) ══ -->
<div class="modal-overlay" id="scanned-dechet-modal" onclick="if(event.target===this)closeScannedDechetModal()">
  <div class="modal" style="max-width:480px;">
    <div class="modal-header" style="background:linear-gradient(135deg,#2d6a4f,#40916c);border-radius:12px 12px 0 0;">
      <h3 class="modal-title" style="color:#fff;">📱 Fiche Déchet — Scan QR</h3>
      <button class="modal-close" style="color:#fff;" onclick="closeScannedDechetModal()">✕</button>
    </div>
    <div class="modal-body" id="scanned-dechet-body"></div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeScannedDechetModal()">Fermer</button>
    </div>
  </div>
</div>

</body>
</html>

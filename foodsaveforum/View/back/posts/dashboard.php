<div class="admin-dashboard-content">
    <h2>Bienvenue au Dashboard Admin 🔐</h2>

    <div class="dashboard-intro">
        <p>Vous êtes connecté en tant qu'administrateur du forum FoodSave.</p>
        <p>Utilisez la navigation pour gérer les posts et commentaires.</p>
    </div>

    <div class="dashboard-quick-links">
        <h3>Accès rapide</h3>
        <div class="quick-links-grid">
            <div class="quick-link-card">
                <h4>📋 Gérer les Posts</h4>
                <p>Visualiser, bannir ou supprimer les posts du forum</p>
                <a href="admin.php?action=posts" class="btn btn-primary">Accéder</a>
            </div>

            <div class="quick-link-card">
                <h4>💬 Gérer les Commentaires</h4>
                <p>Visualiser, bannir ou supprimer les commentaires</p>
                <a href="admin.php?action=commentaires" class="btn btn-primary">Accéder</a>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-intro {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}

.dashboard-intro p {
    margin: 10px 0;
    color: #555;
}

.dashboard-quick-links h3 {
    color: var(--color-primary);
    margin-bottom: 20px;
    font-size: 20px;
}

.quick-links-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.quick-link-card {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border-left: 4px solid var(--color-primary);
    transition: all 0.3s ease;
}

.quick-link-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.12);
}

.quick-link-card h4 {
    color: var(--color-primary);
    margin-bottom: 10px;
    font-size: 18px;
}

.quick-link-card p {
    color: #666;
    margin-bottom: 15px;
    font-size: 14px;
}

.quick-link-card .btn {
    width: 100%;
}
</style>

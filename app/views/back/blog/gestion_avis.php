<?php
// Vérifier que les variables existent
if(!isset($avis) || !isset($totalAvis) || !isset($totalPending) || !isset($totalApproved) || !isset($averageNote)) {
    header('Location: index.php?action=adminArticles');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Admin : Gestion des avis</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/FoodSave/public/assets/css/style.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { font-family: 'Poppins', sans-serif; background: #EDE8D0; }
        
        /* ========== SIDEBAR ========== */
        .admin-container { display: flex; min-height: 100vh; }
        
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1a1a1a 0%, #0d0d0d 100%);
            color: #e0e0e0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1rem;
        }
        
        .sidebar-header .logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .sidebar-header .logo-area img { height: 40px; }
        .sidebar-header .logo-area span {
            font-size: 1.2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #4caf50, #ff6b35);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .sidebar-menu { list-style: none; padding: 0 1rem; }
        .sidebar-menu li { margin-bottom: 0.5rem; }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #b0b0b0;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: linear-gradient(135deg, rgba(76,175,80,0.2), rgba(255,107,53,0.1));
            color: #4caf50;
        }
        
        .sidebar-menu a i { width: 24px; font-size: 1.1rem; }
        
        /* ========== MAIN CONTENT ========== */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            background: #EDE8D0;
            min-height: 100vh;
        }
        
        /* ========== NAVBAR ========== */
        .navbar {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 0.8rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .nav-logo img { height: 40px; }
        .nav-logo span { font-weight: 700; font-size: 1.2rem; color: #333; }
        
        .nav-menu { display: flex; gap: 1rem; }
        .nav-link {
            text-decoration: none;
            color: #555;
            padding: 8px 16px;
            border-radius: 30px;
            transition: all 0.3s ease;
        }
        .nav-link:hover, .nav-link.active {
            background: #4caf50;
            color: white;
        }
        
        .login-btn {
            padding: 8px 18px;
            border-radius: 30px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .login-outline { background: transparent; border: 1px solid #4caf50; color: #4caf50; }
        .login-outline:hover { background: #4caf50; color: white; }
        .login-primary { background: #ff6b35; color: white; }
        .login-primary:hover { background: #e65100; transform: translateY(-2px); }
        
        /* ========== CONTENT HEADER ========== */
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .content-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .content-header h1 i { color: #ffc107; }
        
        /* ========== STATS CARDS ========== */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #4caf50, #ff6b35);
        }
        
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .stat-card .number { font-size: 2.2rem; font-weight: 800; color: #4caf50; }
        .stat-card h4 { font-size: 0.85rem; color: #888; margin-top: 0.5rem; text-transform: uppercase; letter-spacing: 1px; }
        
        /* ========== TABLE CARD ========== */
        .avis-table {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .avis-table h3 {
            margin-bottom: 1rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .avis-table h3 i { color: #4caf50; }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        
        th {
            background: #f8f6ee;
            font-weight: 600;
            color: #444;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        tr:hover td { background: rgba(76,175,80,0.03); }
        
        /* ========== BADGES & BUTTONS ========== */
        .badge-status {
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 600;
            color: white;
        }
        .badge-status { background: #4caf50; }
        .badge-status.warning { background: #ffc107; color: #333; }
        
        .stars { color: #ffc107; font-size: 0.85rem; letter-spacing: 2px; }
        
        .btn-edit, .btn-validate, .btn-delete {
            padding: 6px 14px;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 0 3px;
        }
        .btn-edit { background: #ffc107; color: #333; }
        .btn-edit:hover { background: #e0a800; transform: translateY(-1px); }
        .btn-validate { background: #4caf50; color: white; }
        .btn-validate:hover { background: #2e7d32; transform: translateY(-1px); }
        .btn-delete { background: #dc3545; color: white; }
        .btn-delete:hover { background: #c82333; transform: translateY(-1px); }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .table-responsive { overflow-x: auto; }
        
        @media (max-width: 768px) {
            .sidebar { width: 80px; }
            .sidebar-header .logo-area span, .sidebar-menu a span { display: none; }
            .main-content { margin-left: 80px; }
            .stats-cards { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="admin-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo-area">
                <img src="/FoodSave/public/assets/images/logo_foodsave.png" alt="Logo">
                <span>FoodSave Admin</span>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php?action=adminArticles"><i class="fas fa-newspaper"></i> <span>Articles</span></a></li>
            <li><a href="index.php?action=adminAvis" class="active"><i class="fas fa-star"></i> <span>Avis</span></a></li>
            <li><a href="#"><i class="fas fa-users"></i> <span>Utilisateurs</span></a></li>
            <li><a href="#"><i class="fas fa-chart-line"></i> <span>Statistiques</span></a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        
        <!-- Navbar -->
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <img src="/FoodSave/public/assets/images/logo_foodsave.png" alt="Logo">
                    <span>FoodSave Admin</span>
                </div>
                <div class="nav-menu">
                    <a href="index.php?action=adminArticles" class="nav-link">Articles</a>
                    <a href="index.php?action=adminAvis" class="nav-link active">Avis</a>
                </div>
                <div class="user-actions">
                    <button class="login-btn login-outline"><i class="fas fa-user"></i> Profil</button>
                    <button class="login-btn login-primary"><i class="fas fa-sign-out-alt"></i> Déconnexion</button>
                </div>
            </div>
        </nav>

        <div class="content-header">
            <h1><i class="fas fa-star" style="color: #ffc107;"></i> Gestion des avis</h1>
        </div>

        <?php if(isset($_GET['success'])): ?>
            <?php if($_GET['success'] == 'approved'): ?>
                <div class="success"><i class="fas fa-check-circle"></i> Avis approuvé !</div>
            <?php elseif($_GET['success'] == 'updated'): ?>
                <div class="success"><i class="fas fa-check-circle"></i> Avis modifié !</div>
            <?php elseif($_GET['success'] == 'deleted'): ?>
                <div class="success"><i class="fas fa-check-circle"></i> Avis supprimé !</div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="number"><?php echo $totalAvis; ?></div>
                <h4>Total avis</h4>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $totalApproved; ?></div>
                <h4>Approuvés</h4>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $totalPending; ?></div>
                <h4>En attente</h4>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $averageNote; ?></div>
                <h4>Note moyenne</h4>
            </div>
        </div>
        
        <!-- Tableau des avis -->
        <div class="avis-table">
            <h3><i class="fas fa-list-ul"></i> Liste des avis</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <th>ID</th><th>Article</th><th>Utilisateur</th><th>Note</th><th>Avis</th><th>Statut</th><th>Actions</th>
                    </thead>
                    <tbody>
                        <?php foreach($avis as $a): ?>
                        <tr>
                            <td><?php echo $a['id']; ?></td>
                            <td><?php echo htmlspecialchars($a['article_titre']); ?></td>
                            <td><?php echo htmlspecialchars($a['user_name']); ?></td>
                            <td class="stars">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <?php if($i <= $a['note']): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </td>
                            <td><?php echo htmlspecialchars(substr($a['contenu'], 0, 50)) . '...'; ?></td>
                            <td>
                                <?php if($a['statut'] == 'approuvé'): ?>
                                    <span class="badge-status">Approuvé</span>
                                <?php elseif($a['statut'] == 'en attente'): ?>
                                    <span class="badge-status warning">En attente</span>
                                <?php else: ?>
                                    <span class="badge-status warning">Rejeté</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="index.php?action=editAvisForm&id=<?php echo $a['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i> Modifier</a>
                                <?php if($a['statut'] == 'en attente'): ?>
                                    <a href="index.php?action=approveAvis&id=<?php echo $a['id']; ?>" class="btn-validate" onclick="return confirm('Approuver cet avis ?')"><i class="fas fa-check"></i> Approuver</a>
                                <?php endif; ?>
                                <a href="index.php?action=deleteAvis&id=<?php echo $a['id']; ?>" class="btn-delete" onclick="return confirm('Supprimer cet avis ?')"><i class="fas fa-trash-alt"></i> Supprimer</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>
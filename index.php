<?php
// index.php - Page d'accueil
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Angels House - Accueil</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">✨ Angels House</a>
            <ul class="nav-links">
                <?php if (isset($_SESSION['user_type'])): ?>
                    <li><a href="dashboard.php">Tableau de bord</a></li>
                    <li><a href="logout.php">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="login.php">Connexion</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <section class="hero">
        <div class="container">
            <h1>Bienvenue à Angels House</h1>
            <p>Gestion intelligente de votre communauté</p>
            <?php if (!isset($_SESSION['user_type'])): ?>
                <a href="login.php" class="btn btn-primary">Se connecter</a>
            <?php else: ?>
                <a href="dashboard.php" class="btn btn-primary">Accéder au tableau de bord</a>
            <?php endif; ?>
        </div>
    </section>

    <section class="container" style="margin-top: 50px; margin-bottom: 50px;">
        <div class="dashboard-grid">
            <div class="stat-card">
                <h3>👥</h3>
                <p>Gestion des membres</p>
            </div>
            <div class="stat-card">
                <h3>⏰</h3>
                <p>Système de pointage</p>
            </div>
            <div class="stat-card">
                <h3>📋</h3>
                <p>Gestion des tâches</p>
            </div>
            <div class="stat-card">
                <h3>🔔</h3>
                <p>Notifications</p>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Angels House. Tous droits réservés.</p>
    </footer>
</body>
</html>


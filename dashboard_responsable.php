<?php
session_start();
require_once 'config.php'; 

// 1. Protection d'accès
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'responsable' && $_SESSION['role'] !== 'root')) {
    header("Location: connexion.php");
    exit;
}

$pseudo_responsable = $_SESSION['pseudo'];
$role = $_SESSION['role'];

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord - Responsable</title>
    
    <style>
        /* CSS HARMONISÉ */
        :root {
            --color-primary: #8A2BE2; /* Bleu-Violet Profond (Améthyste) */
            --color-secondary: #DAA520; /* Jaune Doré (Goldenrod) */
            --color-background: #cab3f8ff; /* Violet très clair, presque blanc */
            --color-surface: #FFFFFF; /* Cartes blanches */
            --color-text: #333333;
            --color-light-text: #555555;
            --font-display: 'Georgia', serif; 
            --font-body: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            --shadow-subtle: 0 4px 10px rgba(0, 0, 0, 0.08);
        }
        body { font-family: var(--font-body); background-color: var(--color-background); margin: 0; padding: 0; color: var(--color-text); }
        
        /* NAVIGATION ET EN-TÊTE UNIFORME */
        .navbar {
            background-color: var(--color-surface);
            padding: 10px 50px; 
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--color-primary);
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .logo-group {
            display: flex;
            align-items: center;
        }
        .logo-group .logo-img {
            height: 65px; 
            width: auto;
            border-radius: 50%; 
            object-fit: cover;
            margin-right: 10px; 
            
        }
        .logo-group .logo-text {
            font-family: var(--font-display);
            font-size: 1.5em;
            color: var(--color-primary);
            font-weight: bold;
        }
        .navbar nav {
            display: flex;
            align-items: center; 
        }
        .navbar nav a {
            color: var(--color-primary);
            text-decoration: none;
            padding: 8px 15px;
            margin-left: 15px;
            border-radius: 4px;
            transition: background-color 0.3s, color 0.3s;
        }
        .navbar nav a:hover {
            background-color: var(--color-primary);
            color: white;
        }

        main { padding: 40px 50px; max-width: 1000px; margin: 0 auto; }
        h2 { font-family: var(--font-display); color: var(--color-primary); border-bottom: 3px solid var(--color-secondary); padding-bottom: 10px; margin-top: 40px; }
        
        .info-box { 
            background-color: var(--color-surface); 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: var(--shadow-subtle); 
            margin-bottom: 20px; 
            border-left: 5px solid var(--color-primary); 
        }
        .info-box p { margin: 5px 0; }
        
        footer {
            background-color: var(--color-primary);
            color: white;
            text-align: center;
            padding: 15px 50px;
            margin-top: 50px;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="logo-group">
            <img src="AH.jpg" alt="Logo Mon Royaume" class="logo-img">
            <div class="logo-text">PANNEAU RESPONSABLE</div>
        </div>
        <nav>
            <span style="color: var(--color-primary); font-weight: bold;">Bienvenue, <?php echo htmlspecialchars($pseudo_responsable); ?> (<?php echo ucfirst($role); ?>)</span>
            <a href="deconnexion.php" style="background-color: #FF6347; color: white; font-weight: bold;">🔒 Déconnexion</a>
        </nav>
    </div>

    <main>
        
        <h2>Aperçu Général</h2>
        
        <div class="info-box">
            <p><strong>Rôle Actuel :</strong> Vous êtes désigné **<?php echo ucfirst($role); ?>** et êtes le pont entre la supervision (Root) et les opérations (Ouvriers).</p>
            <p><strong>Objectifs du Jour :</strong> Veuillez consulter les missions en cours et assurer la coordination de votre équipe.</p>
        </div>
        
        <h2>Gestion des Utilisateurs (Rôle Responsable)</h2>
        <div class="info-box">
            <p style="font-weight: bold; color: var(--color-primary);">Accéder aux fonctionnalités avancées de gestion.</p>
            <p>Ici, vous pourrez bientôt visualiser les performances de vos ouvriers et assigner des tâches spécifiques.</p>
        </div>

        <h2>Missions Actuelles</h2>
        <div class="info-box" style="border-left-color: var(--color-secondary);">
            <p><strong>Mission 1 :</strong> Optimisation de l'entrepôt (Priorité Élevée)</p>
            <p><strong>Mission 2 :</strong> Formation des nouveaux ouvriers (En cours)</p>
        </div>
        
    </main>

    <footer>
        <p>
            © <?php echo date("Y"); ?> Mon Royaume - Panneau Responsable
        </p>
    </footer>

</body>
</html>
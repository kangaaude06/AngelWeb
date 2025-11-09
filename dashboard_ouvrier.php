<?php
session_start();
require_once 'config.php'; 

// 1. Protection d'accès
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'ouvrier' && $_SESSION['role'] !== 'root')) {
    header("Location: connexion.php");
    exit;
}

$pseudo_ouvrier = $_SESSION['pseudo'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord - Ouvrier</title>
    
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

        main { padding: 40px 50px; max-width: 800px; margin: 0 auto; }
        h2 { font-family: var(--font-display); color: var(--color-primary); border-bottom: 3px solid var(--color-secondary); padding-bottom: 10px; margin-top: 40px; }
        
        .mission-card { 
            background-color: var(--color-surface); 
            padding: 25px; 
            border-radius: 8px; 
            box-shadow: var(--shadow-subtle); 
            margin-bottom: 20px; 
            border-left: 5px solid #4CAF50; /* Vert pour Mission */
        }
        .mission-card h3 { color: var(--color-primary); margin-top: 0; }

        .calendrier-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .calendrier-table th, .calendrier-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .calendrier-table th { background-color: var(--color-primary); color: white; }
        .calendrier-table tr:nth-child(even) { background-color: #fcfcfc; }
        
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
            <div class="logo-text">PANNEAU OUVRIER</div>
        </div>
        <nav>
            <span style="color: var(--color-primary); font-weight: bold;">Bienvenue, <?php echo htmlspecialchars($pseudo_ouvrier); ?> (<?php echo ucfirst($role); ?>)</span>
            <a href="deconnexion.php" style="background-color: #FF6347; color: white; font-weight: bold;">🔒 Déconnexion</a>
        </nav>
    </div>

    <main>
        
        <h2> Mes Missions Actuelles</h2>
        
        <div class="mission-card">
            <h3>Mission : Optimisation de l'entrepôt</h3>
            <p><strong>Statut :</strong> En Cours</p>
            <p><strong>Description :</strong> Réorganiser les stocks selon la nouvelle méthode A-B-C. Rapport à remettre au Responsable avant Vendredi.</p>
        </div>
        
        <div class="mission-card" style="border-left-color: #2196F3;">
            <h3>Mission : Maintenance Préventive</h3>
            <p><strong>Statut :</strong> À Faire</p>
            <p><strong>Description :</strong> Vérification des machines 1 à 5. Planifier cette tâche en début de semaine prochaine.</p>
        </div>

        <h2> Calendrier des Réunions</h2>
        
        <table class="calendrier-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Sujet</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>2025-11-10</td>
                    <td>09:00</td>
                    <td>Point hebdomadaire sur les objectifs.</td>
                </tr>
                <tr>
                    <td>2025-11-15</td>
                    <td>14:30</td>
                    <td>Formation sur les nouvelles procédures.</td>
                </tr>
            </tbody>
        </table>
        
    </main>

    <footer>
        <p>
            © <?php echo date("Y"); ?> Mon Royaume - Espace Ouvrier
        </p>
    </footer>

</body>
</html>
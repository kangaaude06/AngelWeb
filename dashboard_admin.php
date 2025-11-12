<?php
// dashboard_admin.php
require_once 'User.class.php';
require_once 'Pointage.class.php';
require_once 'config.php'; // Pour s'assurer que la session est démarrée via config.php

// Vérification des droits d'accès
if (!isset($_SESSION['user_id']) || $_SESSION['grade'] !== 'Administrateur') { header("Location: login.php"); exit; }

$userHandler = new User();
$pointageHandler = new Pointage();

$searchTerm = trim(isset($_GET['search']) ? $_GET['search'] : '');
$tout_le_personnel = $userHandler->getAllUsers($searchTerm);
$stats_ouvriers = $pointageHandler->getWorkerStats();

$message = $message_class = "";

// --- GESTION DE L'AJOUT DE NOUVEL UTILISATEUR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $grade_id = (int)$_POST['grade_id'];
    if ($grade_id === 2 || $grade_id === 3) { // Coordination ou Responsable
        $data = array(
            'nom' => trim($_POST['nom']), 'prenom' => trim($_POST['prenom']), 
            'numero_telephone' => trim($_POST['numero_telephone']), 'departement' => trim($_POST['departement']),
            'grade_id' => $grade_id, 'password' => 'angel123' 
        );
        if ($userHandler->registerUser($data)) {
            $message = "Utilisateur ajouté avec succès ! (Grade: " . ($grade_id === 2 ? 'Coordination' : 'Responsable') . "). Mot de passe par défaut: angel123";
            $message_class = 'success';
        } else {
            $message = "Échec de l'ajout de l'utilisateur. Le numéro de téléphone pourrait déjà exister.";
            $message_class = 'error';
        }
    } else {
        $message = "Erreur : Tentative d'ajouter un grade non autorisé.";
        $message_class = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>TdB Administrateur - Gestion Globale</title>
    <style>
        :root { --violet: #8A2BE2; --or: #FFD700; --blanc: #FFFFFF; --texte-clair: #F0F0F0;}
        body { font-family: Arial, sans-serif; background-color: var(--blanc); color: #333; margin: 0; padding: 0; }
        header { background-color: var(--violet); color: var(--blanc); padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { margin: 0; border-bottom: 2px solid var(--or); padding-bottom: 5px; }
        .header-links a { color: var(--or); text-decoration: none; font-weight: bold; margin-left: 20px; }
        .container { padding: 20px; } 
        .section-box { border: 1px solid var(--violet); padding: 20px; margin-bottom: 30px; border-radius: 8px; background-color: var(--texte-clair); box-shadow: 2px 2px 10px rgba(138, 43, 226, 0.1); }
        h3 { color: var(--violet); border-bottom: 1px solid var(--or); padding-bottom: 5px; margin-top: 30px; }
        .search-form input[type="text"] { padding: 10px; border: 1px solid var(--violet); border-radius: 5px; width: 300px; }
        .search-form button { padding: 10px 15px; background-color: var(--violet); color: var(--blanc); border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; } 
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: var(--or); color: #333; font-weight: bold; } 
        tr:nth-child(even) { background-color: #fff; }
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; text-align: center; margin-top: 20px; }
        .stat-card { background-color: var(--or); color: #333; padding: 15px; border-radius: 5px; font-size: 1.1em; border: 1px solid var(--violet); }
        .stat-card h4 { margin: 0; font-weight: normal; } 
        .stat-card span { font-size: 2em; font-weight: bold; display: block; }
        .export-btn { background-color: var(--or); color: #333; padding: 10px 15px; border-radius: 5px; font-weight: bold; display: inline-block; margin-bottom: 20px; text-decoration: none; border: 2px solid var(--violet); transition: background-color 0.3s; }
        .export-btn:hover { background-color: #e5c100; }
        .form-user form { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; align-items: end; }
        .form-user { border: 1px solid var(--violet); padding: 15px; border-radius: 8px; background-color: var(--texte-clair); margin-bottom: 30px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        input[type="text"], input[type="tel"], select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button[type="submit"] { background-color: var(--violet); color: var(--blanc); border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; transition: background-color 0.3s; }
        button[type="submit"]:hover { background-color: #6a1aae; }
        .message { padding: 10px; margin: 10px 20px; border-radius: 5px; font-weight: bold; text-align: center; }
        .success { background-color: #e6ffe6; color: green; border: 1px solid green; }
        .error { background-color: #ffe6e6; color: red; border: 1px solid red; }
    </style>
</head>
<body>
    <header>
        <h1>Tableau de Bord Administrateur 👑</h1>
        <div class="header-links">
            <a href="admin_gestion_personnel.php">Gérer Personnel</a> 
            <a href="logout.php">Déconnexion</a>
        </div>
    </header>
    
    <?php if (!empty($message)): ?>
        <div class="message <?php echo $message_class; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="container">
        
        
        <div class="section-box">
            <h2>Personnel Angel House (<?php echo count($tout_le_personnel); ?> Membres)</h2>
            <form method="GET" class="search-form">
                <input type="text" name="search" placeholder="Rechercher par Nom, Prénom ou Département..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button type="submit">Rechercher</button>
            </form>
            <table>
                <thead><tr><th>Nom Complet</th><th>Grade</th><th>Département</th><th>Tél.</th></tr></thead>
                <tbody>
                    <?php if (!empty($tout_le_personnel)): ?>
                        <?php foreach ($tout_le_personnel as $personne): ?>
                            <tr><td><?php echo htmlspecialchars($personne['prenom'] . ' ' . $personne['nom']); ?></td><td><?php echo htmlspecialchars($personne['nom_grade']); ?></td>
                                <td><?php echo htmlspecialchars($personne['departement']); ?></td><td><?php echo htmlspecialchars($personne['numero_telephone']); ?></td></tr>
                        <?php endforeach; ?>
                    <?php else: ?> <tr><td colspan="4">Aucun personnel trouvé.</td></tr> <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="section-box">
            <h2>Statistiques de Pointage des Ouvriers</h2>
             <a href="export_stats.php" class="export-btn">
                ⬇️ Exporter les Statistiques en Excel (XLSX)
            </a>
            
            <table>
                <thead><tr><th>Ouvrier</th><th>Département</th><th>Présences (P)</th><th>Retards (R)</th><th>Absences (A)</th><th>Total Pointé</th></tr></thead>
                <tbody>
                    <?php $total_global_present = $total_global_retard = 0; ?>
                    <?php if (!empty($stats_ouvriers)): ?>
                        <?php foreach ($stats_ouvriers as $stats): 
                            $total_global_present += $stats['total_present']; $total_global_retard += $stats['total_retard'];
                        ?>
                            <tr><td><?php echo htmlspecialchars($stats['prenom'] . ' ' . $stats['nom']); ?></td><td><?php echo htmlspecialchars($stats['departement']); ?></td>
                                <td style="color: green;"><?php echo htmlspecialchars($stats['total_present']); ?></td>
                                <td style="color: orange;"><?php echo htmlspecialchars($stats['total_retard']); ?></td>
                                <td style="color: red;"><?php echo htmlspecialchars($stats['total_absent']); ?></td>
                                <td><?php echo htmlspecialchars($stats['total_pointage']); ?></td></tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                         <tr><td colspan="6">Aucun historique de pointage trouvé pour les ouvriers.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <h3 style="margin-top: 30px; color: var(--violet);">Synthèse Globale</h3>
            <?php 
            $total_absent = !empty($stats_ouvriers) ? array_sum(array_column($stats_ouvriers, 'total_absent')) : 0;
            ?>
            <div class="stats-grid">
                 <div class="stat-card"><h4>Total Présences</h4><span style="color: green;"><?php echo $total_global_present; ?></span></div>
                <div class="stat-card"><h4>Total Retards</h4><span style="color: orange;"><?php echo $total_global_retard; ?></span></div>
                <div class="stat-card"><h4>Total Absences</h4><span style="color: red;"><?php echo $total_absent; ?></span></div>
            </div>
        </div>
    </div>
</body>
</html>
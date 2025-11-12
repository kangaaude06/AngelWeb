<?php
// dashboard_admin.php
require_once 'User.class.php';

// CORRECTION PHP 5.6 - Démarrage de session unique
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérification des droits d'accès
if (!isset($_SESSION['user_id']) || $_SESSION['grade'] !== 'Administrateur') {
    header("Location: login.php");
    exit;
}

$userHandler = new User();
$message = $message_class = "";

// --- GESTION DE L'AJOUT DE NOUVEL UTILISATEUR (Coordination ou Responsable) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    
    $grade_id = (int)$_POST['grade_id'];
    
    // Seuls les Coordinations (2) et les Responsables (3) sont ajoutés par l'Admin.
    if ($grade_id === 2 || $grade_id === 3) {
        $data = array(
            'nom' => trim($_POST['nom']),
            'prenom' => trim($_POST['prenom']),
            'numero_telephone' => trim($_POST['numero_telephone']),
            'departement' => trim($_POST['departement']),
            'grade_id' => $grade_id,
            'password' => 'angel123' // Mot de passe par défaut que l'utilisateur devra changer
        );

        if ($userHandler->registerUser($data)) {
            $message = "Utilisateur ajouté avec succès ! (Grade: " . ($grade_id === 2 ? 'Coordination' : 'Responsable') . "). Mot de passe par défaut: angel123";
            $message_class = 'success';
        } else {
            $message = "Échec de l'ajout de l'utilisateur. Le numéro de téléphone pourrait déjà exister.";
            $message_class = 'error';
        }
    } else {
        $message = "Erreur : Tentative d'ajouter un grade non autorisé (Admin ou Ouvrier).";
        $message_class = 'error';
    }
}


// --- RÉCUPÉRATION DES DONNÉES ---
$allUsers = $userHandler->getAllUsers();

// Pour les compteurs rapides
$stats = array(
    'Administrateur' => 0,
    'Coordination' => 0,
    'Responsable' => 0,
    'Ouvrier' => 0,
    'Total' => count($allUsers)
);

foreach ($allUsers as $user) {
    if (isset($stats[$user['nom_grade']])) {
        $stats[$user['nom_grade']]++;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>TdB Administrateur - Gestion Utilisateurs</title>
    <style>
        :root { --violet: #8A2BE2; --or: #FFD700; --blanc: #FFFFFF; --texte-clair: #F0F0F0; }
        body { font-family: Arial, sans-serif; background-color: var(--blanc); color: #333; margin: 0; padding: 0; }
        header { background-color: var(--violet); color: var(--blanc); padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { margin: 0; border-bottom: 2px solid var(--or); padding-bottom: 5px; }
        .main-content { padding: 20px; }
        h3 { color: var(--violet); border-bottom: 1px solid var(--or); padding-bottom: 5px; margin-top: 30px; }
        
        /* Styles pour les compteurs */
        .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-box { background-color: var(--texte-clair); padding: 15px; border-radius: 8px; text-align: center; border-left: 5px solid var(--violet); box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .stat-box h4 { margin: 0; color: #555; font-size: 0.9em; }
        .stat-box p { font-size: 1.8em; font-weight: bold; color: var(--violet); margin: 5px 0 0; }
        
        /* Styles pour les formulaires et tableaux */
        .form-user, .user-table { border: 1px solid var(--violet); padding: 15px; border-radius: 8px; background-color: var(--texte-clair); margin-bottom: 30px; }
        .form-user form { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; align-items: end; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        input[type="text"], input[type="tel"], select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: var(--violet); color: var(--blanc); border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; transition: background-color 0.3s; }
        button:hover { background-color: #6a1aae; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: var(--or); color: #333; font-weight: bold; }
        
        .message { padding: 10px; margin: 10px 20px; border-radius: 5px; font-weight: bold; text-align: center; }
        .success { background-color: #e6ffe6; color: green; border: 1px solid green; }
        .error { background-color: #ffe6e6; color: red; border: 1px solid red; }
    </style>
</head>
<body>
    <header>
        <h1>Tableau de Bord Administrateur ⚙️</h1>
        <a href="logout.php" style="color: var(--or); text-decoration: none; font-weight: bold;">Déconnexion</a>
    </header>
    
    <?php if (!empty($message)): ?>
        <div class="message <?php echo $message_class; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="main-content">
        
        <h3>Statistiques Rapides des Utilisateurs</h3>
        <div class="stats-grid">
            <div class="stat-box"><h4>Total Utilisateurs</h4><p><?php echo $stats['Total']; ?></p></div>
            <div class="stat-box"><h4>Administrateurs</h4><p><?php echo $stats['Administrateur']; ?></p></div>
            <div class="stat-box"><h4>Coordinations</h4><p><?php echo $stats['Coordination']; ?></p></div>
            <div class="stat-box"><h4>Responsables</h4><p><?php echo $stats['Responsable']; ?></p></div>
            <div class="stat-box"><h4>Ouvriers</h4><p><?php echo $stats['Ouvrier']; ?></p></div>
        </div>
        
        <h3>Ajouter un Coordination ou un Responsable</h3>
        <div class="form-user">
            <form method="POST">
                <input type="hidden" name="action" value="add_user">
                
                <div class="form-group">
                    <label for="grade_id">Rôle :</label>
                    <select id="grade_id" name="grade_id" required>
                        <option value="">-- Choisir --</option>
                        <option value="2">Coordination</option>
                        <option value="3">Responsable</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="nom">Nom :</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                
                <div class="form-group">
                    <label for="prenom">Prénom :</label>
                    <input type="text" id="prenom" name="prenom" required>
                </div>
                
                <div class="form-group">
                    <label for="numero_telephone">Numéro de Téléphone (Identifiant) :</label>
                    <input type="tel" id="numero_telephone" name="numero_telephone" required>
                </div>
                
                <div class="form-group">
                    <label for="departement">Département/Poste :</label>
                    <input type="text" id="departement" name="departement" required>
                </div>
                
                <button type="submit">Ajouter l'Utilisateur</button>
                <small style="grid-column: 1 / 4; color: #666;">Note : Le mot de passe par défaut sera **angel123**.</small>
            </form>
        </div>
        
        <h3>Liste Complète des Utilisateurs</h3>
        <div class="user-table">
            <table>
                <thead>
                    <tr><th>ID</th><th>Prénom Nom</th><th>Rôle</th><th>Département</th><th>Téléphone</th></tr>
                </thead>
                <tbody>
                    <?php if (!empty($allUsers)): ?>
                        <?php foreach ($allUsers as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></td>
                                <td><?php echo htmlspecialchars($user['nom_grade']); ?></td>
                                <td><?php echo htmlspecialchars($user['departement']); ?></td>
                                <td><?php echo htmlspecialchars($user['numero_telephone']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5">Aucun utilisateur trouvé dans la base de données.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <p style="text-align: center; margin-top: 40px; color: var(--violet); font-weight: bold;">
            
        </p>
    </div>
</body>
</html>
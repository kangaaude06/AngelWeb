<?php
// gestion_taches.php - Gestion des tâches pour la coordination
require_once 'config.php';
require_once 'Auth.class.php';
require_once 'Database.class.php';
require_once 'TacheManager.class.php';

Auth::requireRole('coordination');

$tacheManager = new TacheManager();
$db = Database::getInstance()->getPdo();
$message = '';
$messageType = '';

// Création d'une tâche
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['creer_tache'])) {
    $libelle = trim($_POST['libelle']);
    $description = trim($_POST['description']);
    $dateDebut = $_POST['date_debut'];
    $dateFin = $_POST['date_fin'] ?: null;
    $idOuvrier = $_POST['id_ouvrier'];
    $idCoordination = $_SESSION['user_id'];
    
    $result = $tacheManager->creerTache($libelle, $description, $dateDebut, $dateFin, $idCoordination, $idOuvrier);
    
    if ($result['success']) {
        $message = $result['message'];
        $messageType = 'success';
    } else {
        $message = $result['message'];
        $messageType = 'error';
    }
}

// Mise à jour du statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_statut'])) {
    $idTache = $_POST['id_tache'];
    $statut = $_POST['statut'];
    
    if ($tacheManager->updateStatutTache($idTache, $statut)) {
        $message = 'Statut mis à jour avec succès';
        $messageType = 'success';
    } else {
        $message = 'Erreur lors de la mise à jour';
        $messageType = 'error';
    }
}

// Récupération des ouvriers du département
$departement = $_SESSION['departement'];
$sql = "SELECT * FROM ouvrier WHERE departement = :departement ORDER BY nom, prenom";
$stmt = $db->prepare($sql);
$stmt->execute(['departement' => $departement]);
$ouvriers = $stmt->fetchAll();

// Récupération des tâches
$taches = $tacheManager->getTachesDepartement($departement);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des tâches - Angels House</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo"> Angels House</a>
            <ul class="nav-links">
                <li><a href="dashboard.php">Tableau de bord</a></li>
                <li><a href="gestion_taches.php">Tâches</a></li>
                <li><a href="gestion_notifications.php">Notifications</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <div class="container" style="margin-top: 30px;">
        <h1 style="color: white; margin-bottom: 20px;">Gestion des tâches</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire de création -->
        <div class="card">
            <div class="card-header">Créer une nouvelle tâche</div>
            <form method="POST">
                <div class="form-group">
                    <label for="libelle">Libellé</label>
                    <input type="text" id="libelle" name="libelle" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="id_ouvrier">Ouvrier</label>
                    <select name="id_ouvrier" id="id_ouvrier" class="form-control" required>
                        <option value="">Sélectionnez un ouvrier</option>
                        <?php foreach ($ouvriers as $ouvrier): ?>
                            <option value="<?php echo $ouvrier['id_ouvrier']; ?>">
                                <?php echo htmlspecialchars($ouvrier['nom'] . ' ' . $ouvrier['prenom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date_debut">Date de début</label>
                    <input type="datetime-local" id="date_debut" name="date_debut" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="date_fin">Date de fin (optionnel)</label>
                    <input type="datetime-local" id="date_fin" name="date_fin" class="form-control">
                </div>
                
                <button type="submit" name="creer_tache" class="btn btn-primary">Créer la tâche</button>
            </form>
        </div>

        <!-- Liste des tâches -->
        <div class="card">
            <div class="card-header">Liste des tâches</div>
            <?php if (empty($taches)): ?>
                <p>Aucune tâche assignée.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Libellé</th>
                            <th>Ouvrier</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($taches as $tache): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($tache['libelle']); ?></td>
                            <td><?php echo htmlspecialchars($tache['ouv_nom'] . ' ' . $tache['ouv_prenom']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($tache['date_debut'])); ?></td>
                            <td><?php echo $tache['date_fin'] ? date('d/m/Y H:i', strtotime($tache['date_fin'])) : '-'; ?></td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo $tache['statut'] === 'terminée' ? 'success' : 
                                        ($tache['statut'] === 'en cours' ? 'warning' : 'info'); 
                                ?>">
                                    <?php echo ucfirst($tache['statut']); ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="id_tache" value="<?php echo $tache['id_tache']; ?>">
                                    <select name="statut" onchange="this.form.submit()" style="padding: 5px;">
                                        <option value="à faire" <?php echo $tache['statut'] === 'à faire' ? 'selected' : ''; ?>>À faire</option>
                                        <option value="en cours" <?php echo $tache['statut'] === 'en cours' ? 'selected' : ''; ?>>En cours</option>
                                        <option value="terminée" <?php echo $tache['statut'] === 'terminée' ? 'selected' : ''; ?>>Terminée</option>
                                    </select>
                                    <input type="hidden" name="update_statut" value="1">
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Angels House. Tous droits réservés.</p>
    </footer>
</body>
</html>


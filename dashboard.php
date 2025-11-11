<?php
// dashboard.php - Tableau de bord selon le type d'utilisateur
require_once 'config.php';
require_once 'Auth.class.php';
require_once 'Database.class.php';
require_once 'PointageManager.class.php';
require_once 'TacheManager.class.php';
require_once 'NotificationManager.class.php';

// Vérification de la connexion
if (!Auth::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Pour les responsables, vérifier qu'au moins un département est défini
if ($_SESSION['user_type'] === 'responsable') {
    require_once 'DepartementManager.class.php';
    $departementManager = new DepartementManager();
    $departements = $departementManager->getDepartementsResponsable($_SESSION['user_id']);
    
    if (empty($departements)) {
        header('Location: select_departement.php');
        exit;
    }
    
    // Si aucun département actif n'est défini, utiliser le premier
    if (!isset($_SESSION['departement'])) {
        $_SESSION['departement'] = $departements[0];
    }
    
    // Vérifier que le département actif est toujours valide
    if (!in_array($_SESSION['departement'], $departements)) {
        $_SESSION['departement'] = $departements[0];
    }
}

$userType = $_SESSION['user_type'];
$userId = $_SESSION['user_id'];
$db = Database::getInstance()->getPdo();

// Récupération des données selon le type d'utilisateur
$pointageManager = new PointageManager();
$tacheManager = new TacheManager();
$notificationManager = new NotificationManager();

$stats = [];
$pointages = [];
$taches = [];
$notifications = [];

switch ($userType) {
    case 'admin':
        // Statistiques pour l'admin
        $stmt = $db->query("SELECT COUNT(*) FROM ouvrier");
        $stats['ouvriers'] = $stmt->fetchColumn();
        
        $stmt = $db->query("SELECT COUNT(*) FROM responsable");
        $stats['responsables'] = $stmt->fetchColumn();
        
        $stmt = $db->query("SELECT COUNT(*) FROM coordination");
        $stats['coordination'] = $stmt->fetchColumn();
        
        $stmt = $db->query("SELECT COUNT(*) FROM pointage_ouvrier WHERE DATE(date_heure_pointage) = CURDATE()");
        $stats['pointages_aujourdhui'] = $stmt->fetchColumn();
        break;
        
    case 'coordination':
        $departement = $_SESSION['departement'];
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM ouvrier WHERE departement = :departement");
        $stmt->execute(['departement' => $departement]);
        $stats['ouvriers'] = $stmt->fetchColumn();
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM responsable WHERE departement = :departement");
        $stmt->execute(['departement' => $departement]);
        $stats['responsables'] = $stmt->fetchColumn();
        
        $taches = $tacheManager->getTachesDepartement($departement);
        $notifications = $notificationManager->getNotificationsResponsable($userId);
        break;
        
    case 'responsable':
        $departement = $_SESSION['departement'];
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM ouvrier WHERE departement = :departement AND id_responsable = :id_responsable");
        $stmt->execute(['departement' => $departement, 'id_responsable' => $userId]);
        $stats['ouvriers'] = $stmt->fetchColumn();
        
        $pointages = $pointageManager->getPointagesDepartement($departement);
        $notifications = $notificationManager->getNotificationsResponsable($userId);
        break;
        
    case 'ouvrier':
        $pointages = $pointageManager->getPointagesOuvrier($userId, 10);
        $taches = $tacheManager->getTachesOuvrier($userId);
        $notifications = $notificationManager->getNotificationsOuvrier($userId);
        break;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Angels House</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">✨ Angels House</a>
            <ul class="nav-links">
                <li><a href="dashboard.php">Tableau de bord</a></li>
                <?php if ($userType === 'responsable'): ?>
                    <li><a href="pointage.php">Pointage</a></li>
                    <li><a href="select_departement.php">Changer de département</a></li>
                <?php endif; ?>
                <?php if ($userType === 'coordination'): ?>
                    <li><a href="gestion_taches.php">Tâches</a></li>
                    <li><a href="gestion_notifications.php">Notifications</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <div class="container" style="margin-top: 30px;">
        <h1 style="color: white; margin-bottom: 20px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
            Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
        </h1>
        <?php if ($userType === 'responsable'): ?>
            <?php
            require_once 'DepartementManager.class.php';
            $departementManager = new DepartementManager();
            $departements = $departementManager->getDepartementsResponsable($_SESSION['user_id']);
            ?>
            <div style="background: rgba(255, 255, 255, 0.1); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <p style="color: white; margin: 0; font-size: 1.1rem;">
                    Département actif: <strong><?php echo htmlspecialchars($_SESSION['departement']); ?></strong>
                    <?php if (count($departements) > 1): ?>
                        <br><small style="font-size: 0.9rem; opacity: 0.9;">
                            Autres départements: <?php echo implode(', ', array_map(function($d) { return ucwords($d); }, array_filter($departements, function($d) { return $d !== $_SESSION['departement']; }))); ?>
                        </small>
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
        
        <!-- Statistiques -->
        <?php if (!empty($stats)): ?>
        <div class="dashboard-grid">
            <?php foreach ($stats as $key => $value): ?>
            <div class="stat-card">
                <h3><?php echo $value; ?></h3>
                <p><?php echo ucfirst(str_replace('_', ' ', $key)); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Pointages récents -->
        <?php if (!empty($pointages)): ?>
        <div class="card">
            <div class="card-header">Pointages récents</div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Type</th>
                        <th>Statut</th>
                        <?php if ($userType === 'ouvrier'): ?>
                        <th>Responsable</th>
                        <?php else: ?>
                        <th>Ouvrier</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pointages as $pointage): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($pointage['date_heure_pointage'])); ?></td>
                        <td><?php echo date('H:i', strtotime($pointage['date_heure_pointage'])); ?></td>
                        <td><?php echo ucfirst($pointage['type_pointage']); ?></td>
                        <td>
                            <span class="badge badge-<?php 
                                echo $pointage['statut'] === 'présent' ? 'success' : 
                                    ($pointage['statut'] === 'retard' ? 'warning' : 'danger'); 
                            ?>">
                                <?php echo ucfirst($pointage['statut']); ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            if ($userType === 'ouvrier') {
                                echo htmlspecialchars($pointage['resp_nom'] . ' ' . $pointage['resp_prenom']);
                            } else {
                                echo htmlspecialchars($pointage['ouv_nom'] . ' ' . $pointage['ouv_prenom']);
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Tâches -->
        <?php if (!empty($taches)): ?>
        <div class="card">
            <div class="card-header">Mes tâches</div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Libellé</th>
                        <th>Date début</th>
                        <th>Date fin</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($taches as $tache): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($tache['libelle']); ?></td>
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
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Notifications -->
        <?php if (!empty($notifications)): ?>
        <div class="card">
            <div class="card-header">Notifications</div>
            <?php foreach ($notifications as $notif): ?>
            <div class="alert alert-info" style="margin-bottom: 10px;">
                <strong><?php echo htmlspecialchars($notif['contenu']); ?></strong><br>
                <small><?php echo date('d/m/Y H:i', strtotime($notif['date_envoi'])); ?></small>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Angels House. Tous droits réservés.</p>
    </footer>
</body>
</html>


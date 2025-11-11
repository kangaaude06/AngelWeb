<?php
// gestion_notifications.php - Gestion des notifications pour la coordination
require_once 'config.php';
require_once 'Auth.class.php';
require_once 'Database.class.php';
require_once 'NotificationManager.class.php';

Auth::requireRole('coordination');

$notificationManager = new NotificationManager();
$db = Database::getInstance()->getPdo();
$message = '';
$messageType = '';

// Création d'une notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['creer_notification'])) {
    $contenu = trim($_POST['contenu']);
    $typeNotification = $_POST['type_notification'];
    $idCoordination = $_SESSION['user_id'];
    
    $idOuvriers = isset($_POST['id_ouvriers']) ? $_POST['id_ouvriers'] : [];
    $idResponsables = isset($_POST['id_responsables']) ? $_POST['id_responsables'] : [];
    
    $result = $notificationManager->creerNotification($contenu, $idCoordination, $typeNotification, $idOuvriers, $idResponsables);
    
    if ($result['success']) {
        $message = $result['message'];
        $messageType = 'success';
    } else {
        $message = $result['message'];
        $messageType = 'error';
    }
}

// Récupération des ouvriers et responsables du département
$departement = $_SESSION['departement'];

$sql = "SELECT * FROM ouvrier WHERE departement = :departement ORDER BY nom, prenom";
$stmt = $db->prepare($sql);
$stmt->execute(['departement' => $departement]);
$ouvriers = $stmt->fetchAll();

$sql = "SELECT * FROM responsable WHERE departement = :departement ORDER BY nom, prenom";
$stmt = $db->prepare($sql);
$stmt->execute(['departement' => $departement]);
$responsables = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des notifications - Angels House</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">✨ Angels House</a>
            <ul class="nav-links">
                <li><a href="dashboard.php">Tableau de bord</a></li>
                <li><a href="gestion_taches.php">Tâches</a></li>
                <li><a href="gestion_notifications.php">Notifications</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <div class="container" style="margin-top: 30px;">
        <h1 style="color: white; margin-bottom: 20px;">Gestion des notifications</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire de création -->
        <div class="card">
            <div class="card-header">Créer une notification</div>
            <form method="POST">
                <div class="form-group">
                    <label for="contenu">Contenu</label>
                    <textarea id="contenu" name="contenu" class="form-control" rows="4" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="type_notification">Type de notification</label>
                    <select name="type_notification" id="type_notification" class="form-control" required>
                        <option value="message">Message</option>
                        <option value="alerte">Alerte</option>
                        <option value="rappel">Rappel</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Destinataires - Ouvriers</label>
                    <div style="max-height: 150px; overflow-y: auto; border: 1px solid var(--border-color); padding: 10px; border-radius: 8px;">
                        <?php foreach ($ouvriers as $ouvrier): ?>
                            <label style="display: block; margin-bottom: 8px;">
                                <input type="checkbox" name="id_ouvriers[]" value="<?php echo $ouvrier['id_ouvrier']; ?>">
                                <?php echo htmlspecialchars($ouvrier['nom'] . ' ' . $ouvrier['prenom']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Destinataires - Responsables</label>
                    <div style="max-height: 150px; overflow-y: auto; border: 1px solid var(--border-color); padding: 10px; border-radius: 8px;">
                        <?php foreach ($responsables as $responsable): ?>
                            <label style="display: block; margin-bottom: 8px;">
                                <input type="checkbox" name="id_responsables[]" value="<?php echo $responsable['id_responsable']; ?>">
                                <?php echo htmlspecialchars($responsable['nom'] . ' ' . $responsable['prenom']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <button type="submit" name="creer_notification" class="btn btn-primary">Envoyer la notification</button>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Angels House. Tous droits réservés.</p>
    </footer>
</body>
</html>


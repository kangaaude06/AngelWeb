<?php
// pointage.php - Page de pointage pour les responsables
require_once 'config.php';
require_once 'Auth.class.php';
require_once 'Database.class.php';
require_once 'PointageManager.class.php';

Auth::requireRole('responsable');

$pointageManager = new PointageManager();
$db = Database::getInstance()->getPdo();
$message = '';
$messageType = '';

// S'assurer que le département est défini
require_once 'DepartementManager.class.php';
$departementManager = new DepartementManager();
$departements = $departementManager->getDepartementsResponsable($_SESSION['user_id']);

if (empty($departements)) {
    header('Location: select_departement.php');
    exit;
}

// Traitement du changement de département actif (doit être fait en premier)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changer_departement_actif'])) {
    $nouveauDepartement = $_POST['departement'];
    if (in_array($nouveauDepartement, $departements)) {
        $_SESSION['departement'] = $nouveauDepartement;
    }
}

// Définir le département actif
if (!isset($_SESSION['departement']) || !in_array($_SESSION['departement'], $departements)) {
    $_SESSION['departement'] = $departements[0];
}

// Département actif
$departement = $_SESSION['departement'];

// Traitement du pointage (présent/absent)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marquer_statut'])) {
    $idOuvrier = $_POST['id_ouvrier'];
    $statut = $_POST['statut']; // présent, absent, retard
    $idResponsable = $_SESSION['user_id'];
    
    $result = $pointageManager->pointerOuvrier($idOuvrier, $idResponsable, $statut);
    
    if ($result['success']) {
        $message = $result['message'];
        $messageType = 'success';
    } else {
        $message = $result['message'];
        $messageType = 'error';
    }
}

// Récupération des ouvriers du département avec leur statut
$ouvriers = $pointageManager->getOuvriersAvecStatut($departement);

// Récupération des pointages du jour pour affichage
$pointages = $pointageManager->getPointagesDepartement($departement);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pointage - Angels House</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">✨ Angels House</a>
            <ul class="nav-links">
                <li><a href="dashboard.php">Tableau de bord</a></li>
                <li><a href="pointage.php">Pointage</a></li>
                <li><a href="select_departement.php">Mes départements</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <div class="container" style="margin-top: 30px;">
        <h1 style="color: white; margin-bottom: 20px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
            Pointage des ouvriers
        </h1>
        
        <!-- Sélecteur de département -->
        <?php if (count($departements) > 1): ?>
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">Changer de département</div>
            <form method="POST" style="display: flex; gap: 10px; align-items: center;">
                <select name="departement" class="form-control" style="flex: 1;" onchange="this.form.submit()">
                    <?php foreach ($departements as $dep): ?>
                        <option value="<?php echo htmlspecialchars($dep); ?>" 
                                <?php echo ($dep === $_SESSION['departement']) ? 'selected' : ''; ?>>
                            <?php echo ucwords($dep); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="changer_departement_actif" value="1">
            </form>
        </div>
        <?php endif; ?>
        
        <div style="background: rgba(255, 255, 255, 0.1); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <p style="color: white; margin: 0; font-size: 1.1rem;">
                <strong>Département actif:</strong> <?php echo ucwords($departement); ?> | 
                <strong>Date:</strong> <?php echo date('d/m/Y'); ?>
            </p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Liste des ouvriers avec boutons de pointage -->
        <div class="card">
            <div class="card-header">Marquer la présence / absence</div>
            <?php if (empty($ouvriers)): ?>
                <p>Aucun ouvrier dans ce département.</p>
            <?php else: ?>
                <div style="display: grid; gap: 15px; margin-top: 20px;">
                    <?php foreach ($ouvriers as $ouvrier): ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 15px; background: var(--light-color); border-radius: 8px; border: 2px solid var(--border-color);">
                            <div style="flex: 1;">
                                <h3 style="margin: 0; color: var(--primary-color);">
                                    <?php echo htmlspecialchars($ouvrier['nom'] . ' ' . $ouvrier['prenom']); ?>
                                </h3>
                                <?php if ($ouvrier['statut_pointage']): ?>
                                    <p style="margin: 5px 0 0 0; font-size: 0.9rem;">
                                        Statut actuel: 
                                        <span class="badge badge-<?php 
                                            echo $ouvrier['statut_pointage'] === 'présent' ? 'success' : 
                                                ($ouvrier['statut_pointage'] === 'retard' ? 'warning' : 'danger'); 
                                        ?>">
                                            <?php echo ucfirst($ouvrier['statut_pointage']); ?>
                                        </span>
                                        <?php if ($ouvrier['heure_pointage'] && $ouvrier['statut_pointage'] !== 'absent'): ?>
                                            à <?php echo date('H:i', strtotime($ouvrier['heure_pointage'])); ?>
                                        <?php endif; ?>
                                    </p>
                                <?php else: ?>
                                    <p style="margin: 5px 0 0 0; font-size: 0.9rem; color: #666;">
                                        Non pointé aujourd'hui
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="id_ouvrier" value="<?php echo $ouvrier['id_ouvrier']; ?>">
                                    <input type="hidden" name="statut" value="présent">
                                    <button type="submit" name="marquer_statut" class="btn btn-success" 
                                            style="padding: 10px 20px;">
                                        ✓ Présent
                                    </button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="id_ouvrier" value="<?php echo $ouvrier['id_ouvrier']; ?>">
                                    <input type="hidden" name="statut" value="retard">
                                    <button type="submit" name="marquer_statut" class="btn btn-warning" 
                                            style="padding: 10px 20px;">
                                        ⏰ Retard
                                    </button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="id_ouvrier" value="<?php echo $ouvrier['id_ouvrier']; ?>">
                                    <input type="hidden" name="statut" value="absent">
                                    <button type="submit" name="marquer_statut" class="btn btn-danger" 
                                            style="padding: 10px 20px;">
                                        ✗ Absent
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Résumé des pointages du jour -->
        <div class="card">
            <div class="card-header">Résumé des pointages du jour</div>
            <?php if (empty($pointages)): ?>
                <p>Aucun pointage enregistré aujourd'hui.</p>
            <?php else: ?>
                <?php
                $stats = ['présent' => 0, 'retard' => 0, 'absent' => 0];
                foreach ($pointages as $pointage) {
                    if (isset($stats[$pointage['statut']])) {
                        $stats[$pointage['statut']]++;
                    }
                }
                ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    <div class="stat-card" style="border-color: var(--success-color);">
                        <h3 style="color: var(--success-color);"><?php echo $stats['présent']; ?></h3>
                        <p>Présents</p>
                    </div>
                    <div class="stat-card" style="border-color: var(--warning-color);">
                        <h3 style="color: var(--warning-color);"><?php echo $stats['retard']; ?></h3>
                        <p>Retards</p>
                    </div>
                    <div class="stat-card" style="border-color: var(--danger-color);">
                        <h3 style="color: var(--danger-color);"><?php echo $stats['absent']; ?></h3>
                        <p>Absents</p>
                    </div>
                </div>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ouvrier</th>
                            <th>Heure</th>
                            <th>Type</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pointages as $pointage): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pointage['ouv_nom'] . ' ' . $pointage['ouv_prenom']); ?></td>
                            <td><?php echo $pointage['statut'] === 'absent' ? '-' : date('H:i', strtotime($pointage['date_heure_pointage'])); ?></td>
                            <td><?php echo ucfirst($pointage['type_pointage']); ?></td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo $pointage['statut'] === 'présent' ? 'success' : 
                                        ($pointage['statut'] === 'retard' ? 'warning' : 'danger'); 
                                ?>">
                                    <?php echo ucfirst($pointage['statut']); ?>
                                </span>
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

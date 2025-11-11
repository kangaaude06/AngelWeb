<?php
// select_departement.php - Sélection des départements pour les responsables (max 2)
require_once 'config.php';
require_once 'Auth.class.php';
require_once 'Database.class.php';
require_once 'departements.php';
require_once 'DepartementManager.class.php';

// Vérifier que l'utilisateur est un responsable
if (!Auth::isLoggedIn() || $_SESSION['user_type'] !== 'responsable') {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance()->getPdo();
$departementManager = new DepartementManager();
$message = '';
$messageType = '';

// Récupérer les départements actuels du responsable
$departementsActuels = $departementManager->getDepartementsResponsable($_SESSION['user_id']);

// Traitement de la sélection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouter_departement'])) {
        $departement = trim($_POST['departement']);
        $result = $departementManager->ajouterDepartementResponsable($_SESSION['user_id'], $departement);
        
        if ($result['success']) {
            $message = $result['message'];
            $messageType = 'success';
            $departementsActuels = $departementManager->getDepartementsResponsable($_SESSION['user_id']);
            // Définir le premier département comme département actif si aucun n'est sélectionné
            if (!isset($_SESSION['departement']) && !empty($departementsActuels)) {
                $_SESSION['departement'] = $departementsActuels[0];
            }
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    } elseif (isset($_POST['supprimer_departement'])) {
        $departement = $_POST['departement'];
        if ($departementManager->supprimerDepartementResponsable($_SESSION['user_id'], $departement)) {
            $message = 'Département supprimé avec succès.';
            $messageType = 'success';
            $departementsActuels = $departementManager->getDepartementsResponsable($_SESSION['user_id']);
            // Si le département supprimé était le département actif, changer pour le premier disponible
            if (isset($_SESSION['departement']) && $_SESSION['departement'] === $departement) {
                $_SESSION['departement'] = !empty($departementsActuels) ? $departementsActuels[0] : null;
            }
        } else {
            $message = 'Erreur lors de la suppression.';
            $messageType = 'error';
        }
    } elseif (isset($_POST['changer_departement_actif'])) {
        $departement = $_POST['departement'];
        if (in_array($departement, $departementsActuels)) {
            $_SESSION['departement'] = $departement;
            $message = 'Département actif changé avec succès.';
            $messageType = 'success';
        }
    }
}

// Si aucun département n'est défini mais qu'il y a des départements, utiliser le premier
if (!isset($_SESSION['departement']) && !empty($departementsActuels)) {
    $_SESSION['departement'] = $departementsActuels[0];
}

// Département actif pour le pointage
$departementActif = isset($_SESSION['departement']) ? $_SESSION['departement'] : null;

// Départements disponibles pour l'ajout (exclure ceux déjà assignés)
$departementsDisponibles = array_filter(getDepartements(), function($dep) use ($departementsActuels) {
    return !in_array($dep, $departementsActuels);
});
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des départements - Angels House</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">✨ Angels House</a>
            <ul class="nav-links">
                <li><a href="dashboard.php">Tableau de bord</a></li>
                <li><a href="select_departement.php">Mes départements</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <div class="container" style="margin-top: 30px;">
        <h1 style="color: white; margin-bottom: 20px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
            Gestion de mes départements
        </h1>
        <p style="color: white; margin-bottom: 30px;">
            Vous pouvez avoir <strong>maximum 2 départements</strong>. Le département actif est celui utilisé pour le pointage.
        </p>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Départements actuels -->
        <div class="card">
            <div class="card-header">Mes départements (<?php echo count($departementsActuels); ?>/2)</div>
            
            <?php if (empty($departementsActuels)): ?>
                <p style="color: #666; text-align: center; padding: 20px;">
                    Aucun département assigné. Veuillez ajouter au moins un département pour continuer.
                </p>
            <?php else: ?>
                <div style="display: grid; gap: 15px; margin-top: 20px;">
                    <?php foreach ($departementsActuels as $dep): ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 15px; background: var(--light-color); border-radius: 8px; border: 2px solid <?php echo ($dep === $departementActif) ? 'var(--gold-color)' : 'var(--border-color)'; ?>;">
                            <div style="flex: 1;">
                                <h3 style="margin: 0; color: var(--primary-color);">
                                    <?php echo ucwords($dep); ?>
                                    <?php if ($dep === $departementActif): ?>
                                        <span class="badge badge-gold" style="margin-left: 10px;">Actif</span>
                                    <?php endif; ?>
                                </h3>
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <?php if ($dep !== $departementActif): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="departement" value="<?php echo htmlspecialchars($dep); ?>">
                                        <button type="submit" name="changer_departement_actif" class="btn btn-gold" style="padding: 8px 16px;">
                                            Définir comme actif
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce département ?');">
                                    <input type="hidden" name="departement" value="<?php echo htmlspecialchars($dep); ?>">
                                    <button type="submit" name="supprimer_departement" class="btn btn-danger" style="padding: 8px 16px;">
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Ajouter un département -->
        <?php if (count($departementsActuels) < 2): ?>
        <div class="card">
            <div class="card-header">Ajouter un département</div>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="departement">Sélectionnez un département</label>
                    <select name="departement" id="departement" class="form-control" required>
                        <option value="">-- Choisissez un département --</option>
                        <?php foreach ($departementsDisponibles as $dep): ?>
                            <option value="<?php echo htmlspecialchars($dep); ?>">
                                <?php echo ucwords($dep); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: #666; margin-top: 5px; display: block;">
                        Vous pouvez ajouter jusqu'à 2 départements maximum.
                    </small>
                </div>
                
                <button type="submit" name="ajouter_departement" class="btn btn-primary">
                    Ajouter ce département
                </button>
            </form>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="alert alert-info">
                <strong>Maximum atteint :</strong> Vous avez déjà 2 départements assignés. Supprimez-en un pour en ajouter un autre.
            </div>
        </div>
        <?php endif; ?>

        <!-- Lien vers le dashboard si au moins un département est défini -->
        <?php if (!empty($departementsActuels)): ?>
        <div style="text-align: center; margin-top: 30px;">
            <a href="dashboard.php" class="btn btn-primary" style="padding: 12px 30px;">
                Continuer vers le tableau de bord
            </a>
        </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Angels House. Tous droits réservés.</p>
    </footer>
</body>
</html>

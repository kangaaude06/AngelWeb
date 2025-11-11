<?php
// dashboard_responsable.php
require_once 'User.class.php';
require_once 'Pointage.class.php';

if (!isset($_SESSION['user_id']) || $_SESSION['grade'] !== 'Responsable') { header("Location: login.php"); exit; }

$responsable_id = $_SESSION['user_id'];
$responsable_departement = $_SESSION['departement'];
$userHandler = new User();
$pointageHandler = new Pointage();
$message = $message_class = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pointer') {
    $ouvrier_a_pointer_id = filter_var($_POST['ouvrier_id'], FILTER_VALIDATE_INT);
    $heure_arrivee_full = trim($_POST['heure_arrivee']) . ':00'; 

    if ($ouvrier_a_pointer_id) {
        $resultat = $pointageHandler->enregistrerPointage($ouvrier_a_pointer_id, $responsable_id, $heure_arrivee_full);
        $message = $resultat['message'];
        $message_class = $resultat['success'] ? 'success' : 'error';
    } else { $message = "Erreur: ID ouvrier manquant."; $message_class = 'error'; }
}

$ouvriers = $userHandler->getUsersByDepartment($responsable_departement, 4); // Grade 4 = Ouvrier
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>TdB Responsable - <?php echo $responsable_departement; ?></title>
<style>
    .error { color: red; } .success { color: green; }
    table { border-collapse: collapse; width: 100%; margin-top: 15px; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
    th { background-color: #FFD700; color: #333; }
</style>
</head>
<body>
    <h1>Tableau de Bord Responsable 👷‍♂️</h1>
    <h2>Département : <?php echo $responsable_departement; ?></h2>
    
    <div class="<?php echo $message_class; ?>"><?php echo $message; ?></div>

    <h3>Pointage des Ouvriers (Limite: <?php echo substr(HEURE_LIMITE_POINTAGE, 0, 5); ?>)</h3>

    <table>
        <thead><tr><th>Nom Complet</th><th>Action (Pointage)</th></tr></thead>
        <tbody>
            <?php if (!empty($ouvriers)): ?>
                <?php foreach ($ouvriers as $ouvrier): ?>
                    <tr>
                        <td><?php echo $ouvrier['prenom'] . ' ' . $ouvrier['nom']; ?></td>
                        <td>
                            <form method="POST" style="display: flex; gap: 10px; align-items: center;">
                                <input type="hidden" name="action" value="pointer">
                                <input type="hidden" name="ouvrier_id" value="<?php echo $ouvrier['id']; ?>">
                                
                                <label for="heure_<?php echo $ouvrier['id']; ?>">Heure d'arrivée :</label>
                                <input type="time" id="heure_<?php echo $ouvrier['id']; ?>" name="heure_arrivee" value="<?php echo date('H:i'); ?>" required style="width: 100px;">
                                       
                                <button type="submit" style="background-color: #8A2BE2; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer;">Pointer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="2">Aucun ouvrier trouvé dans votre département.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <p style="margin-top: 20px;"><a href="logout.php">Déconnexion</a></p>
</body>
</html>
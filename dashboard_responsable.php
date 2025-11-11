<?php
// dashboard_responsable.php
require_once 'User.class.php';
require_once 'Pointage.class.php';

if (!isset($_SESSION['user_id']) || $_SESSION['grade'] !== 'Responsable') { header("Location: login.php"); exit; }

$responsableId = $_SESSION['user_id'];
$responsableDepartement = $_SESSION['departement'];
$responsablePrenom = $_SESSION['prenom'];

$userHandler = new User();
$pointageHandler = new Pointage();

$message = "";
$message_class = "";

// Traitement du Pointage
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ouvrier_id']) && isset($_POST['heure_arrivee'])) {
    $ouvrierId = (int)$_POST['ouvrier_id'];
    $heureArrivee = $_POST['heure_arrivee'];
    
    $resultat = $pointageHandler->enregistrerPointage($ouvrierId, $responsableId, $heureArrivee);
    
    $message = $resultat['message'];
    $message_class = $resultat['success'] ? 'success' : 'error';
}

// Récupérer les ouvriers du département du responsable
// La méthode getUserByDepartment est compatible avec PHP 5.6
$ouvriers_a_pointer = $userHandler->getUsersByDepartment($responsableDepartement, 4); // 4 = Grade Ouvrier
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>TdB Responsable - Pointage</title>
    <style>
        :root { --violet: #8A2BE2; --or: #FFD700; --blanc: #FFFFFF; --texte-clair: #F0F0F0; }
        body { font-family: Arial, sans-serif; background-color: var(--blanc); color: #333; margin: 0; padding: 0; }
        header { background-color: var(--violet); color: var(--blanc); padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { margin: 0; border-bottom: 2px solid var(--or); padding-bottom: 5px; }
        .container { padding: 20px; max-width: 900px; margin: 20px auto; }
        .section-box { border: 1px solid var(--violet); padding: 20px; margin-bottom: 30px; border-radius: 8px; background-color: var(--texte-clair); }
        h2 { color: var(--violet); border-bottom: 1px solid var(--or); padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: var(--or); color: #333; font-weight: bold; }
        tr:nth-child(even) { background-color: #fff; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; text-align: center; }
        .success { background-color: #e6ffe6; color: green; border: 1px solid green; }
        .error { background-color: #ffe6e6; color: red; border: 1px solid red; }
        input[type="time"], button { padding: 8px; border-radius: 5px; }
        button { background-color: var(--violet); color: var(--blanc); border: none; cursor: pointer; font-weight: bold; }
        button:hover { background-color: #6a1aae; }
    </style>
</head>
<body>
    <header>
        <h1>TdB Responsable - Pointage ⏰</h1>
        <div style="text-align: right;">
            <span style="color: var(--or); margin-right: 15px;">Département: <?php echo htmlspecialchars($responsableDepartement); ?></span>
            <a href="logout.php" style="color: var(--or); text-decoration: none; font-weight: bold;">Déconnexion</a>
        </div>
    </header>

    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_class; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="section-box">
            <h2>Ouvriers de votre Département (<?php echo htmlspecialchars($responsableDepartement); ?>)</h2>
            <p style="font-style: italic;">Heure limite de pointage : **<?php echo HEURE_LIMITE_POINTAGE; ?>**</p>
            
            <table>
                <thead>
                    <tr>
                        <th>Ouvrier</th>
                        <th>Numéro Tél.</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ouvriers_a_pointer)): ?>
                        <?php foreach ($ouvriers_a_pointer as $ouvrier): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ouvrier['prenom'] . ' ' . $ouvrier['nom']); ?></td>
                                <td><?php echo htmlspecialchars($ouvrier['numero_telephone']); ?></td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="ouvrier_id" value="<?php echo $ouvrier['id']; ?>">
                                        <input type="time" name="heure_arrivee" value="<?php echo date('H:i'); ?>" required>
                                        <button type="submit">Enregistrer Présence</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3">Aucun ouvrier trouvé dans votre département.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<?php
// dashboard_ouvrier.php
require_once 'User.class.php';
require_once 'Pointage.class.php';
require_once 'Tache.class.php';

if (!isset($_SESSION['user_id']) || $_SESSION['grade'] !== 'Ouvrier') { header("Location: login.php"); exit; }

$ouvrierId = $_SESSION['user_id'];
$ouvrierPrenom = $_SESSION['prenom'];

$pointageHandler = new Pointage();
$tacheHandler = new Tache();

// Traitement de la mise à jour du statut de tâche
$message = $message_class = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tache_id']) && isset($_POST['new_statut'])) {
    $tacheId = (int)$_POST['tache_id'];
    $newStatut = $_POST['new_statut'];
    
    if ($tacheHandler->updateOuvrierTacheStatut($tacheId, $ouvrierId, $newStatut)) {
        $message = "Statut de la tâche mis à jour avec succès.";
        $message_class = 'success';
    } else {
        $message = "Erreur lors de la mise à jour du statut de la tâche.";
        $message_class = 'error';
    }
}

$historique_pointage = $pointageHandler->getPointageHistoryByOuvrier($ouvrierId);
$taches_assignees = $tacheHandler->getTachesByOuvrier($ouvrierId);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>TdB Ouvrier - Angel House</title>
    <style>
        :root { --violet: #8A2BE2; --or: #FFD700; --blanc: #FFFFFF; --texte-clair: #F0F0F0; }
        body { font-family: Arial, sans-serif; background-color: var(--blanc); color: #333; margin: 0; padding: 0; }
        header { background-color: var(--violet); color: var(--blanc); padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { margin: 0; border-bottom: 2px solid var(--or); padding-bottom: 5px; }
        .container { padding: 20px; }
        .section-box { border: 1px solid var(--violet); padding: 20px; margin-bottom: 30px; border-radius: 8px; background-color: var(--texte-clair); }
        h2 { color: var(--violet); border-bottom: 1px solid var(--or); padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: var(--or); color: #333; font-weight: bold; }
        tr:nth-child(even) { background-color: #fff; }
        .statut-present { color: green; font-weight: bold; }
        .statut-retard { color: orange; font-weight: bold; }
        .statut-absent { color: red; font-weight: bold; }
        .statut-tache-en_cours { color: blue; }
        .statut-tache-terminee { color: green; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; }
        .success { background-color: #e6ffe6; color: green; border: 1px solid green; }
        .error { background-color: #ffe6e6; color: red; border: 1px solid red; }
        select { padding: 5px; border-color: var(--violet); }
        .update-form button { background-color: var(--violet); color: var(--blanc); border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; }
    </style>
</head>
<body>
    <header>
        <h1>Tableau de Bord Ouvrier - Bienvenue <?php echo htmlspecialchars($ouvrierPrenom); ?> 👋</h1>
        <a href="logout.php" style="color: var(--or); text-decoration: none; font-weight: bold;">Déconnexion</a>
    </header>

    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_class; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="section-box">
            <h2>Mes Tâches Assignées 📋</h2>
            <table>
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Description</th>
                        <th>Échéance</th>
                        <th>Coordinateur</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($taches_assignees)): ?>
                        <?php foreach ($taches_assignees as $tache): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tache['titre']); ?></td>
                                <td><?php echo htmlspecialchars($tache['description']); ?></td>
                                <td><?php echo htmlspecialchars($tache['date_echeance']); ?></td>
                                <td><?php echo htmlspecialchars($tache['coord_prenom'] . ' ' . $tache['coord_nom']); ?></td>
                                <td><span class="statut-tache-<?php echo strtolower(str_replace(' ', '_', $tache['ouvrier_statut'])); ?>"><?php echo htmlspecialchars($tache['ouvrier_statut']); ?></span></td>
                                <td>
                                    <form method="POST" class="update-form">
                                        <input type="hidden" name="tache_id" value="<?php echo $tache['tache_id']; ?>">
                                        <select name="new_statut">
                                            <option value="En Cours" <?php if ($tache['ouvrier_statut'] === 'En Cours') echo 'selected'; ?>>En Cours</option>
                                            <option value="Terminée" <?php if ($tache['ouvrier_statut'] === 'Terminée') echo 'selected'; ?>>Terminée</option>
                                        </select>
                                        <button type="submit">Mettre à jour</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6">Vous n'avez aucune tâche assignée pour le moment.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="section-box">
            <h2>Mon Historique de Pointage ⏰</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Heure d'Arrivée</th>
                        <th>Statut</th>
                        <th>Enregistré par</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($historique_pointage)): ?>
                        <?php foreach ($historique_pointage as $pointage): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pointage['date_pointage']); ?></td>
                                <td><?php echo htmlspecialchars($pointage['heure_arrivee']); ?></td>
                                <td>
                                    <span class="statut-<?php echo strtolower($pointage['statut']); ?>">
                                        <?php echo htmlspecialchars($pointage['statut']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    if (!empty($pointage['responsable_id'])) {
                                        echo htmlspecialchars($pointage['resp_prenom'] . ' ' . $pointage['resp_nom']);
                                    } else {
                                        echo 'Système (Absence auto)';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">Aucun historique de pointage trouvé.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
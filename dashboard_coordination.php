<?php
// dashboard_coordination.php
require_once 'User.class.php';
require_once 'Tache.class.php';

if (!isset($_SESSION['user_id']) || $_SESSION['grade'] !== 'Coordination') { header("Location: login.php"); exit; }

$coordination_id = $_SESSION['user_id'];
$userHandler = new User();
$tacheHandler = new Tache();
$message = $message_class = "";

// GESTION DES ACTIONS (AJOUT TÂCHE / INITIALISATION OUVRIER)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_tache') {
        $titre = trim($_POST['titre']); $description = trim($_POST['description']); $date_echeance = trim($_POST['date_echeance']);
        $ouvrier_ids = $_POST['ouvriers'] ?? [];
        $resultat = $tacheHandler->creerTache($coordination_id, $titre, $description, $date_echeance, $ouvrier_ids);
        $message = $resultat['message']; $message_class = $resultat['success'] ? 'success' : 'error';
    } elseif (isset($_POST['action']) && $_POST['action'] === 'init_ouvrier') {
        $data = ['nom' => trim($_POST['nom']), 'prenom' => trim($_POST['prenom']), 'departement' => trim($_POST['departement_ouvrier']), 'grade_id' => 4, 'password' => NULL];
        if ($userHandler->registerUser($data)) {
            $message = "Ouvrier " . $data['prenom'] . " initialisé. Il peut maintenant s'inscrire via son numéro."; $message_class = 'success';
        } else { $message = "Échec de l'initialisation de l'ouvrier."; $message_class = 'error'; }
    }
}

$ouvriers_tous = $userHandler->getUsersByDepartment('%', 4); // Récupère TOUS les ouvriers
$taches_actives = $tacheHandler->getAllTaches();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>TdB Coordination - Gestion Globale</title>
<style>
    .success { color: green; font-weight: bold; } .error { color: red; font-weight: bold; }
    .container { display: flex; gap: 20px; } .box { border: 1px solid #8A2BE2; padding: 15px; flex: 1; border-radius: 5px; }
    h1 { color: #8A2BE2; border-bottom: 2px solid #FFD700; padding-bottom: 5px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background-color: #FFD700; }
</style>
</head>
<body>
    <h1>Tableau de Bord Coordination 📈</h1>
    <div class="<?php echo $message_class; ?>"><?php echo $message; ?></div>

    <div class="container">
        <div class="box">
            <h3>1. Initialiser un Nouvel Ouvrier</h3>
            <form method="POST"><input type="hidden" name="action" value="init_ouvrier">
                Nom: <input type="text" name="nom" required><br><br> Prénom: <input type="text" name="prenom" required><br><br>
                Département: <input type="text" name="departement_ouvrier" required><br><br>
                <button type="submit" style="background-color: #8A2BE2; color: white; border: none; padding: 8px 12px; border-radius: 4px;">Initialiser l'Ouvrier</button>
            </form>
        </div>

        <div class="box">
            <h3>2. Créer et Assigner une Tâche</h3>
            <form method="POST"><input type="hidden" name="action" value="add_tache">
                Titre: <input type="text" name="titre" required><br><br>
                Description: <textarea name="description" rows="3" required></textarea><br><br>
                Date Échéance: <input type="date" name="date_echeance" required><br><br>
                <h4>Assigner à (Ouvriers) :</h4>
                <div style="max-height: 150px; overflow-y: scroll; border: 1px solid #eee; padding: 5px;">
                    <?php if (!empty($ouvriers_tous)): ?>
                        <?php foreach ($ouvriers_tous as $ouvrier): ?>
                            <input type="checkbox" name="ouvriers[]" value="<?php echo $ouvrier['id']; ?>">
                            <?php echo $ouvrier['prenom'] . ' ' . $ouvrier['nom'] . ' (' . $ouvrier['departement'] . ')'; ?><br>
                        <?php endforeach; ?>
                    <?php else: ?> <p>Aucun ouvrier à assigner.</p> <?php endif; ?>
                </div><br>
                <button type="submit" style="background-color: #8A2BE2; color: white; border: none; padding: 8px 12px; border-radius: 4px;">Créer et Assigner la Tâche</button>
            </form>
        </div>
    </div>
    <hr>
    
    <h3>Liste des Tâches Actuellement Assignées</h3>
    <table>
        <thead><tr><th>Titre</th><th>Échéance</th><th>Statut</th><th>Coord.</th><th>Assignés</th></tr></thead>
        <tbody>
            <?php foreach ($taches_actives as $tache): ?>
            <tr><td><?php echo $tache['titre']; ?></td><td><?php echo $tache['date_echeance']; ?></td><td><?php echo $tache['statut']; ?></td>
                <td><?php echo $tache['coord_prenom']; ?></td><td><?php echo $tache['ouvriers_assignes']; ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p style="margin-top: 20px;"><a href="logout.php">Déconnexion</a></p>
</body>
</html>
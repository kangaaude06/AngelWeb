<?php
// dashboard_coordination.php
require_once 'User.class.php';
require_once 'Tache.class.php';

if (!isset($_SESSION['user_id']) || $_SESSION['grade'] !== 'Coordination') { header("Location: login.php"); exit; }

$coordination_id = $_SESSION['user_id'];
$userHandler = new User();
$tacheHandler = new Tache();
$message = $message_class = "";

// GESTION DES ACTIONS (AJOUT TÂCHE / INITIALISATION OUVRIER / ENVOI MESSAGE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_tache') {
        $titre = trim($_POST['titre']); $description = trim($_POST['description']); $date_echeance = trim($_POST['date_echeance']);
        $ouvrier_ids = isset($_POST['ouvriers']) ? $_POST['ouvriers'] : array();
        $resultat = $tacheHandler->creerTache($coordination_id, $titre, $description, $date_echeance, $ouvrier_ids);
        $message = $resultat['message']; $message_class = $resultat['success'] ? 'success' : 'error';
    } elseif (isset($_POST['action']) && $_POST['action'] === 'init_ouvrier') {
        $data = array('nom' => trim($_POST['nom']), 'prenom' => trim($_POST['prenom']), 'departement' => trim($_POST['departement_ouvrier']), 'grade_id' => 4, 'password' => NULL);
        if ($userHandler->registerUser($data)) {
            $message = "Ouvrier " . $data['prenom'] . " initialisé. Il peut maintenant s'inscrire via son numéro."; $message_class = 'success';
        } else { $message = "Échec de l'initialisation de l'ouvrier."; $message_class = 'error'; }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'send_message') {
        $ouvrier_id = (int)$_POST['ouvrier_cible'];
        $message_text = trim($_POST['message_text']);
        
        if ($ouvrier_id && !empty($message_text)) {
            $resultat = $userHandler->sendCommunication($ouvrier_id, $message_text);
            if ($resultat['success']) {
                 $message = "Message simulé envoyé avec succès au numéro : " . $resultat['numero'] . " (Pour un envoi réel, intégrez une API SMS comme Twilio)."; 
                 $message_class = 'success';
            } else {
                 $message = "Échec de l'envoi du message simulé. " . $resultat['message'];
                 $message_class = 'error';
            }
        } else {
            $message = "Veuillez sélectionner un ouvrier et entrer un message.";
            $message_class = 'error';
        }
    }
}

$ouvriers_tous = $userHandler->getUsersByDepartment('%', 4); // 4 = Grade Ouvrier
$taches_actives = $tacheHandler->getAllTaches();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>TdB Coordination - Gestion Globale</title>
    <style>
        :root { --violet: #8A2BE2; --or: #FFD700; --blanc: #FFFFFF; --texte-clair: #F0F0F0; }
        body { font-family: Arial, sans-serif; background-color: var(--blanc); color: #333; margin: 0; padding: 0; }
        header { background-color: var(--violet); color: var(--blanc); padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { margin: 0; border-bottom: 2px solid var(--or); padding-bottom: 5px; }
        .container { padding: 20px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .box { border: 1px solid var(--violet); padding: 15px; border-radius: 8px; background-color: var(--texte-clair); }
        h3 { color: var(--violet); border-bottom: 1px solid var(--or); padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: var(--or); color: #333; font-weight: bold; }
        textarea, select, input[type="text"], input[type="date"] { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; margin-bottom: 10px; }
        button { background-color: var(--violet); color: var(--blanc); border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background-color: #6a1aae; }
        .message { padding: 10px; margin: 10px 20px; border-radius: 5px; font-weight: bold; text-align: center; }
        .success { background-color: #e6ffe6; color: green; border: 1px solid green; }
        .error { background-color: #ffe6e6; color: red; border: 1px solid red; }
    </style>
</head>
<body>
    <header>
        <h1>Tableau de Bord Coordination 📈</h1>
        <a href="logout.php" style="color: var(--or); text-decoration: none; font-weight: bold;">Déconnexion</a>
    </header>
    
    <?php if (!empty($message)): ?>
        <div class="message <?php echo $message_class; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="container">
        <div class="box">
            <h3>1. Initialiser un Nouvel Ouvrier</h3>
            <form method="POST"><input type="hidden" name="action" value="init_ouvrier">
                Nom: <input type="text" name="nom" required>
                Prénom: <input type="text" name="prenom" required>
                Département: <input type="text" name="departement_ouvrier" required>
                <button type="submit">Initialiser l'Ouvrier</button>
            </form>
        </div>

        <div class="box">
            <h3>2. Créer et Assigner une Tâche</h3>
            <form method="POST"><input type="hidden" name="action" value="add_tache">
                Titre: <input type="text" name="titre" required>
                Description: <textarea name="description" rows="3" required></textarea>
                Date Échéance: <input type="date" name="date_echeance" required>
                <h4>Assigner à (Ouvriers) :</h4>
                <div style="max-height: 150px; overflow-y: scroll; border: 1px solid #eee; padding: 5px;">
                    <?php if (!empty($ouvriers_tous)): ?>
                        <?php foreach ($ouvriers_tous as $ouvrier): ?>
                            <input type="checkbox" name="ouvriers[]" value="<?php echo $ouvrier['id']; ?>">
                            <?php echo htmlspecialchars($ouvrier['prenom'] . ' ' . $ouvrier['nom'] . ' (' . $ouvrier['departement'] . ')'); ?><br>
                        <?php endforeach; ?>
                    <?php else: ?> <p>Aucun ouvrier à assigner.</p> <?php endif; ?>
                </div>
                <button type="submit" style="margin-top: 10px;">Créer et Assigner la Tâche</button>
            </form>
        </div>
        
        <div class="box">
            <h3>3. Envoyer un Message (Simulé) 💬</h3>
            <form method="POST">
                <input type="hidden" name="action" value="send_message">
                <label for="ouvrier_cible">Ouvrier Destinataire :</label>
                <select id="ouvrier_cible" name="ouvrier_cible" required>
                    <option value="">-- Choisir --</option>
                    <?php if (!empty($ouvriers_tous)): ?>
                        <?php foreach ($ouvriers_tous as $ouvrier): ?>
                            <option value="<?php echo $ouvrier['id']; ?>">
                                <?php echo htmlspecialchars($ouvrier['prenom'] . ' ' . $ouvrier['nom'] . ' (' . $ouvrier['numero_telephone'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                
                <label for="message_text">Message :</label>
                <textarea id="message_text" name="message_text" rows="4" required></textarea>
                
                <button type="submit">Envoyer le Message (Simulé)</button>
            </form>
        </div>

    </div>
    
    <div style="padding: 20px;">
        <h3>Liste des Tâches Actuellement Assignées</h3>
        <table>
            <thead><tr><th>Titre</th><th>Échéance</th><th>Statut</th><th>Coord.</th><th>Assignés</th></tr></thead>
            <tbody>
                <?php foreach ($taches_actives as $tache): ?>
                <tr><td><?php echo htmlspecialchars($tache['titre']); ?></td><td><?php echo htmlspecialchars($tache['date_echeance']); ?></td><td><?php echo htmlspecialchars($tache['statut']); ?></td>
                    <td><?php echo htmlspecialchars($tache['coord_prenom']); ?></td><td><?php echo htmlspecialchars($tache['ouvriers_assignes']); ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
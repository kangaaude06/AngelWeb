<?php
// dashboard_ouvrier.php
require_once 'User.class.php';
require_once 'Tache.class.php';
require_once 'Pointage.class.php';

if (!isset($_SESSION['user_id']) || $_SESSION['grade'] !== 'Ouvrier') { header("Location: login.php"); exit; }

$ouvrier_id = $_SESSION['user_id'];
$prenom = explode(' ', $_SESSION['user_name'])[0];
$departement = $_SESSION['departement'];

$tacheHandler = new Tache();
$pointageHandler = new Pointage();

$taches_assignees = $tacheHandler->getTachesByOuvrier($ouvrier_id);
$historique_pointage = $pointageHandler->getPointageHistoryByOuvrier($ouvrier_id);
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>TdB Ouvrier - Mon Espace</title>
<style>
    .container { display: flex; gap: 30px; margin-top: 20px; } .box { border: 1px solid #e0e0e0; padding: 20px; flex: 1; border-radius: 5px; }
    h1 { color: #8A2BE2; border-bottom: 2px solid #FFD700; padding-bottom: 5px; }
    .statut-present { color: green; font-weight: bold; } .statut-retard { color: orange; font-weight: bold; } .statut-absent { color: red; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; } th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background-color: #f2f2f2; }
</style>
</head>
<body>
    <h1>Tableau de Bord Ouvrier 🧑‍🔧</h1>
    <h2>Bienvenue, <?php echo $prenom; ?> (<?php echo $departement; ?>)</h2>
    
    <div class="container">
        
        <div class="box">
            <h3>📋 Mes Tâches Actuelles</h3>
            <?php if (!empty($taches_assignees)): ?>
                <?php foreach ($taches_assignees as $tache): ?>
                    <div style="border-bottom: 1px dashed #ccc; padding-bottom: 10px; margin-bottom: 10px;">
                        <strong><?php echo $tache['titre']; ?></strong> 
                        <span style="float: right;">Statut: <?php echo $tache['statut']; ?></span><br>
                        <small>Échéance: <?php echo date('d/m/Y', strtotime($tache['date_echeance'])); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?> <p>Aucune tâche ne vous a été assignée pour le moment.</p> <?php endif; ?>
        </div>

        <div class="box">
            <h3>⌚ Mon Historique de Pointage</h3>
            <table>
                <thead><tr><th>Date</th><th>Heure d'Arrivée</th><th>Statut</th><th>Pointé par</th></tr></thead>
                <tbody>
                    <?php if (!empty($historique_pointage)): ?>
                        <?php foreach ($historique_pointage as $pointage): $statut_class = 'statut-' . strtolower(str_replace(' ', '', $pointage['statut'])); ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($pointage['date_pointage'])); ?></td>
                            <td><?php echo substr($pointage['heure_arrivee'], 0, 5); ?></td>
                            <td class="<?php echo $statut_class; ?>"><?php echo $pointage['statut']; ?></td>
                            <td><?php echo $pointage['resp_prenom']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?> <tr><td colspan="4">Aucun enregistrement de pointage trouvé.</td></tr> <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <p style="margin-top: 30px;"><a href="logout.php">Déconnexion</a></p>
</body>
</html>
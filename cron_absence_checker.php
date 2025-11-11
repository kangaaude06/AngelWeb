<?php
// cron_absence_checker.php
// Ce script est destiné à être exécuté par une tâche planifiée (CRON)
require_once 'Pointage.class.php';

// Créer une instance et appeler la méthode
$pointageHandler = new Pointage();
$result = $pointageHandler->markDailyAbsences();

// Optionnel: Écrire le résultat dans un fichier log
file_put_contents('absence_log.txt', date('Y-m-d H:i:s') . " - " . $result['message'] . "\n", FILE_APPEND);

echo $result['message']; // Utile si exécuté manuellement
?>
<?php
// create_tables_departements.php - Script pour créer les tables de liaison pour les départements multiples
require_once 'config.php';
require_once 'Database.class.php';

$db = Database::getInstance()->getPdo();

try {
    // Table de liaison pour les départements des ouvriers
    $sql = "CREATE TABLE IF NOT EXISTS ouvrier_departement (
        id_ouvrier INT(11) NOT NULL,
        departement VARCHAR(100) NOT NULL,
        PRIMARY KEY (id_ouvrier, departement),
        KEY idx_ouvrier (id_ouvrier),
        KEY idx_departement (departement)
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
    $db->exec($sql);
    echo "Table ouvrier_departement créée avec succès.<br>";

    // Table de liaison pour les départements des responsables
    $sql = "CREATE TABLE IF NOT EXISTS responsable_departement (
        id_responsable INT(11) NOT NULL,
        departement VARCHAR(100) NOT NULL,
        PRIMARY KEY (id_responsable, departement),
        KEY idx_responsable (id_responsable),
        KEY idx_departement (departement)
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
    $db->exec($sql);
    echo "Table responsable_departement créée avec succès.<br>";

    // Migrer les données existantes depuis les tables ouvrier et responsable
    // Pour les ouvriers
    $sql = "SELECT id_ouvrier, departement FROM ouvrier WHERE departement IS NOT NULL AND departement != ''";
    $stmt = $db->query($sql);
    $ouvriers = $stmt->fetchAll();
    
    foreach ($ouvriers as $ouvrier) {
        $sql = "INSERT IGNORE INTO ouvrier_departement (id_ouvrier, departement) VALUES (:id_ouvrier, :departement)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'id_ouvrier' => $ouvrier['id_ouvrier'],
            'departement' => $ouvrier['departement']
        ]);
    }
    echo "Données des ouvriers migrées.<br>";

    // Pour les responsables
    $sql = "SELECT id_responsable, departement FROM responsable WHERE departement IS NOT NULL AND departement != ''";
    $stmt = $db->query($sql);
    $responsables = $stmt->fetchAll();
    
    foreach ($responsables as $responsable) {
        $sql = "INSERT IGNORE INTO responsable_departement (id_responsable, departement) VALUES (:id_responsable, :departement)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'id_responsable' => $responsable['id_responsable'],
            'departement' => $responsable['departement']
        ]);
    }
    echo "Données des responsables migrées.<br>";

    echo "<br><strong>Migration terminée avec succès!</strong>";
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage();
}
?>


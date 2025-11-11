<?php
// Tache.class.php
require_once 'User.class.php'; 

class Tache {
    private $db;
    public function __construct() { $this->db = (new DB())->getPdo(); }

    // Types de paramètres et de retour retirés
    public function creerTache($coordId, $titre, $description, $dateEcheance, $ouvrierIds) {
        if (empty($ouvrierIds)) {
            return array('success' => false, 'message' => "Erreur: Veuillez assigner la tâche à au moins un ouvrier.");
        }

        // 1. Insérer la tâche principale
        $sqlTache = "INSERT INTO taches (coordination_id, titre, description, date_echeance) 
                     VALUES (:coordId, :titre, :description, :dateEcheance)";
        try {
            $stmt = $this->db->prepare($sqlTache);
            $stmt->execute(array(
                'coordId' => $coordId, 'titre' => $titre, 'description' => $description, 'dateEcheance' => $dateEcheance
            ));
            $tacheId = $this->db->lastInsertId();
        } catch (PDOException $e) {
            return array('success' => false, 'message' => "Erreur BD lors de la création de la tâche: " . $e->getMessage());
        }

        // 2. Assigner la tâche aux ouvriers
        $sqlAssignation = "INSERT INTO tache_ouvrier (tache_id, ouvrier_id) VALUES (:tacheId, :ouvrierId)";
        $stmtAssign = $this->db->prepare($sqlAssignation);
        $count = 0;
        foreach ($ouvrierIds as $ouvrierId) {
            $stmtAssign->execute(array('tacheId' => $tacheId, 'ouvrierId' => $ouvrierId));
            $count++;
        }

        return array('success' => true, 'message' => "Tâche créée et assignée à $count ouvrier(s) avec succès.");
    }

    // Types de paramètres retirés
    public function getTachesByOuvrier($ouvrierId) {
        $sql = "SELECT t.*, c.prenom AS coord_prenom, c.nom AS coord_nom, to_ouv.statut AS ouvrier_statut
                FROM tache_ouvrier to_ouv
                JOIN taches t ON to_ouv.tache_id = t.id
                JOIN users c ON t.coordination_id = c.id
                WHERE to_ouv.ouvrier_id = :ouvrierId
                ORDER BY t.date_creation DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array('ouvrierId' => $ouvrierId));
        return $stmt->fetchAll();
    }
    
    // Types de paramètres retirés
    public function updateTacheStatus($tacheId, $ouvrierId, $newStatus) {
        $sql = "UPDATE tache_ouvrier SET statut = :newStatus, date_mise_a_jour = NOW() 
                WHERE tache_id = :tacheId AND ouvrier_id = :ouvrierId";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(array(
                'newStatus' => $newStatus, 'tacheId' => $tacheId, 'ouvrierId' => $ouvrierId
            ));
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // Type de retour retiré
    public function getAllTaches() {
        $sql = "SELECT t.titre, t.date_echeance, t.date_creation, t.statut, c.prenom AS coord_prenom,
                GROUP_CONCAT(u.prenom SEPARATOR ', ') AS ouvriers_assignes
                FROM taches t
                JOIN users c ON t.coordination_id = c.id
                JOIN tache_ouvrier to_ouv ON t.id = to_ouv.tache_id
                JOIN users u ON to_ouv.ouvrier_id = u.id
                GROUP BY t.id, t.titre, t.date_echeance, t.date_creation, t.statut, c.prenom
                ORDER BY t.date_creation DESC";
        return $this->db->query($sql)->fetchAll();
    }
}
?>
<?php
// Tache.class.php
require_once 'User.class.php'; 

class Tache {
    private $db;
    public function __construct() { $this->db = (new DB())->getPdo(); }

    public function creerTache(int $coordinationId, string $titre, string $description, string $dateEcheance, array $ouvrierIds) {
        if (empty($ouvrierIds)) {
            return ['success' => false, 'message' => "Erreur : Vous devez assigner la tâche à au moins un ouvrier."];
        }
        
        $sqlTache = "INSERT INTO taches (titre, description, date_echeance, coordination_id, statut) 
                     VALUES (:titre, :description, :date_echeance, :coordination_id, 'À Faire')";
        try {
            $stmtTache = $this->db->prepare($sqlTache);
            $stmtTache->execute(['titre' => $titre, 'description' => $description, 'date_echeance' => $dateEcheance, 'coordination_id' => $coordinationId]);
            $tacheId = $this->db->lastInsertId();

        } catch (PDOException $e) { return ['success' => false, 'message' => "Erreur lors de la création de la tâche: " . $e->getMessage()]; }
        
        $sqlAssignation = "INSERT INTO tache_ouvrier (tache_id, ouvrier_id) VALUES (:tache_id, :ouvrier_id)";
        $stmtAssignation = $this->db->prepare($sqlAssignation);
        $assignationCount = 0;
        foreach ($ouvrierIds as $ouvrierId) {
            if (filter_var($ouvrierId, FILTER_VALIDATE_INT)) {
                $stmtAssignation->execute(['tache_id' => $tacheId, 'ouvrier_id' => $ouvrierId]);
                $assignationCount++;
            }
        }
        return ['success' => true, 'message' => "Tâche '$titre' créée et assignée à $assignationCount ouvrier(s)."];
    }
    
    public function getTachesByOuvrier(int $ouvrierId) {
        $sql = "SELECT t.*, u.nom AS coord_nom, u.prenom AS coord_prenom
                FROM taches t JOIN tache_ouvrier to ON t.id = to.tache_id JOIN users u ON t.coordination_id = u.id
                WHERE to.ouvrier_id = :ouvrierId ORDER BY t.date_echeance ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ouvrierId' => $ouvrierId]);
        return $stmt->fetchAll();
    }
    
    public function getAllTaches() {
        $sql = "SELECT t.*, u.nom AS coord_nom, u.prenom AS coord_prenom, 
                       (SELECT COUNT(*) FROM tache_ouvrier WHERE tache_id = t.id) AS ouvriers_assignes
                FROM taches t JOIN users u ON t.coordination_id = u.id
                ORDER BY t.date_echeance ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
?>
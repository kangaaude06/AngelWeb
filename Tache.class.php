<?php
// Tache.class.php
require_once 'User.class.php';

class Tache {
    private $db;
    public function __construct() { $this->db = (new DB())->getPdo(); }

    public function creerTache($coordinationId, $titre, $description, $dateEcheance, $ouvrierIds) {
        if (empty($ouvrierIds)) {
            return array('success' => false, 'message' => "Veuillez assigner au moins un ouvrier à cette tâche.");
        }

        try {
            // 1. Créer la Tâche principale
            $sqlTache = "INSERT INTO taches (coordination_id, titre, description, date_echeance, date_creation) 
                         VALUES (:coordination_id, :titre, :description, :date_echeance, NOW())";
            $stmtTache = $this->db->prepare($sqlTache);
            $stmtTache->execute(array(
                'coordination_id' => $coordinationId,
                'titre' => $titre,
                'description' => $description,
                'date_echeance' => $dateEcheance
            ));
            
            $tacheId = $this->db->lastInsertId();

            // 2. Assigner les Ouvriers à la tâche
            $sqlAssign = "INSERT INTO tache_ouvrier (tache_id, ouvrier_id, statut) VALUES (:tache_id, :ouvrier_id, 'En Cours')";
            $stmtAssign = $this->db->prepare($sqlAssign);

            foreach ($ouvrierIds as $ouvrierId) {
                $stmtAssign->execute(array(
                    'tache_id' => $tacheId,
                    'ouvrier_id' => (int)$ouvrierId 
                ));
            }

            return array('success' => true, 'message' => "Tâche créée et assignée avec succès à " . count($ouvrierIds) . " ouvrier(s).");
            
        } catch (PDOException $e) {
            // Gérer les erreurs de clé étrangère ou de base de données
            return array('success' => false, 'message' => "Erreur de base de données lors de la création de la tâche : " . $e->getMessage());
        }
    }
    
    public function getAllTaches() {
        // Tâche principale avec la liste des ouvriers assignés
        $sql = "SELECT t.id, t.titre, t.date_echeance, t.statut, u.prenom AS coord_prenom,
                GROUP_CONCAT(uo.prenom SEPARATOR ', ') AS ouvriers_assignes
                FROM taches t
                JOIN users u ON t.coordination_id = u.id
                LEFT JOIN tache_ouvrier to_ouv ON t.id = to_ouv.tache_id
                LEFT JOIN users uo ON to_ouv.ouvrier_id = uo.id
                GROUP BY t.id
                ORDER BY t.date_echeance ASC, t.statut DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Fonction utilisée par dashboard_ouvrier.php
    public function getTachesByOuvrier($ouvrierId) {
        $sql = "SELECT t.id, t.titre, t.description, t.date_echeance, t.date_creation, 
                to_ouv.statut AS mon_statut_tache, u.prenom AS coord_prenom
                FROM taches t
                JOIN users u ON t.coordination_id = u.id
                JOIN tache_ouvrier to_ouv ON t.id = to_ouv.tache_id
                WHERE to_ouv.ouvrier_id = :ouvrier_id
                ORDER BY t.date_echeance ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array('ouvrier_id' => $ouvrierId));
        return $stmt->fetchAll();
    }

    public function updateOuvrierTacheStatut($tacheId, $ouvrierId, $nouveauStatut) {
        $sql = "UPDATE tache_ouvrier SET statut = :statut, date_mise_a_jour = NOW() 
                WHERE tache_id = :tache_id AND ouvrier_id = :ouvrier_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(array(
            'statut' => $nouveauStatut,
            'tache_id' => $tacheId,
            'ouvrier_id' => $ouvrierId
        ));
    }
}
?>
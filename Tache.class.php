<?php
// Tache.class.php
require_once 'User.class.php';

// La classe Tache a besoin de la classe DB définie dans User.class.php
class Tache {
    private $db;
    public function __construct() { $this->db = (new DB())->getPdo(); } // Récupère la connexion $pdo

    /**
     * Crée une nouvelle tâche et l'assigne aux ouvriers.
     */
    public function creerTache($coordinationId, $titre, $description, $dateEcheance, $ouvrierIds) {
        if (empty($ouvrierIds)) {
            return array('success' => false, 'message' => "Veuillez assigner au moins un ouvrier à cette tâche.");
        }

        try {
            // 1. Créer la Tâche principale dans la table 'taches'
            // Ajout de statut_general = 'En Attente' lors de la création
            $sqlTache = "INSERT INTO taches (coordination_id, titre, description, date_echeance, date_creation, statut_general) 
                         VALUES (:coordination_id, :titre, :description, :date_echeance, NOW(), 'En Attente')";
            $stmtTache = $this->db->prepare($sqlTache);
            $stmtTache->execute(array(
                'coordination_id' => $coordinationId,
                'titre' => $titre,
                'description' => $description,
                'date_echeance' => $dateEcheance
            ));
            
            $tacheId = $this->db->lastInsertId();

            // 2. Assigner les Ouvriers à la tâche dans la table 'tache_ouvrier'
            $sqlAssign = "INSERT INTO tache_ouvrier (tache_id, ouvrier_id, statut) VALUES (:tache_id, :ouvrier_id, :statut)";
            $stmtAssign = $this->db->prepare($sqlAssign);

            foreach ($ouvrierIds as $ouvrierId) {
                $stmtAssign->execute(array(
                    'tache_id' => $tacheId,
                    'ouvrier_id' => $ouvrierId,
                    'statut' => 'Assignée' // Statut initial pour l'ouvrier
                ));
            }
            
            return array('success' => true, 'message' => "La tâche a été créée et assignée aux ouvriers.");

        } catch (PDOException $e) {
            // Pour le débogage, vous pouvez décommenter :
            // die("Erreur PDO: " . $e->getMessage()); 
            return array('success' => false, 'message' => "Erreur de base de données lors de la création de la tâche.");
        }
    }

    /**
     * Mise à jour du statut d'une tâche par un ouvrier.
     * @param int $tacheId
     * @param int $ouvrierId
     * @param string $nouveauStatut ('En Cours', 'Terminée', etc.)
     * @return bool
     */
    public function updateOuvrierTacheStatut($tacheId, $ouvrierId, $nouveauStatut) {
        try {
            // 1. Mise à jour du statut pour cet ouvrier spécifique
            $sql = "UPDATE tache_ouvrier SET statut = :nouveau_statut, date_modification = NOW() 
                    WHERE tache_id = :tache_id AND ouvrier_id = :ouvrier_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array(
                'nouveau_statut' => $nouveauStatut,
                'tache_id' => $tacheId,
                'ouvrier_id' => $ouvrierId
            ));

            // 2. Vérification et mise à jour du statut général de la tâche
            // CORRECTION: Appel de la méthode interne pour mettre à jour le statut_general
            $this->updateGeneralTacheStatus($tacheId); 

            return true;
        } catch (PDOException $e) {
            // Loggez ou affichez l'erreur pour le débogage
            // die("Erreur lors de la mise à jour du statut de tâche : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Méthode interne pour mettre à jour le statut_general de la table 'taches'.
     * C'EST LA METHODE MANQUANTE (ou mal nommée) qui est désormais ajoutée.
     * @param int $tacheId
     */
    private function updateGeneralTacheStatus($tacheId) {
        // Compter les statuts des ouvriers pour cette tâche
        $sqlCount = "SELECT COUNT(*) AS total, 
                     SUM(CASE WHEN statut = 'Terminée' THEN 1 ELSE 0 END) AS terminees,
                     SUM(CASE WHEN statut = 'Assignée' THEN 1 ELSE 0 END) AS assignees
                     FROM tache_ouvrier WHERE tache_id = :tache_id";
        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->execute(array('tache_id' => $tacheId));
        $counts = $stmtCount->fetch();

        $nouveauStatutGeneral = 'En Cours'; // Si au moins un ouvrier a commencé (pas 'Assignée')

        if ($counts['total'] > 0) {
            if ($counts['terminees'] == $counts['total']) {
                $nouveauStatutGeneral = 'Terminée'; // Tous sont finis
            } elseif ($counts['assignees'] == $counts['total']) {
                $nouveauStatutGeneral = 'En Attente'; // Aucun n'a encore commencé
            } else {
                $nouveauStatutGeneral = 'En Cours'; // Au moins un a commencé, mais pas tous terminés
            }
        }
        
        // Mise à jour de la tâche principale
        $sqlUpdate = "UPDATE taches SET statut_general = :nouveau_statut WHERE id = :tache_id";
        $stmtUpdate = $this->db->prepare($sqlUpdate);
        $stmtUpdate->execute(array('nouveau_statut' => $nouveauStatutGeneral, 'tache_id' => $tacheId));
    }

    /**
     * Récupère toutes les tâches pour la Coordination.
     * @return array
     */
    public function getAllTaches() {
        // ... (votre méthode existante)
        $sql = "SELECT t.id, t.titre, t.date_echeance, t.date_creation, 
                t.statut_general, 
                GROUP_CONCAT(uo.prenom, ' ', uo.nom, ' (', to_ouv.statut, ')') AS ouvriers_assignes
                FROM taches t
                LEFT JOIN tache_ouvrier to_ouv ON t.id = to_ouv.tache_id
                LEFT JOIN users uo ON to_ouv.ouvrier_id = uo.id
                GROUP BY t.id
                ORDER BY t.date_echeance ASC, t.statut_general DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère les tâches assignées à un ouvrier donné.
     * @param int $ouvrierId
     * @return array
     */
    public function getTachesByOuvrier($ouvrierId) {
        // ... (votre méthode existante)
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
}
?>
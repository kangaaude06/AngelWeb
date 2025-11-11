<?php
// TacheManager.class.php
require_once 'Database.class.php';

class TacheManager {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getPdo();
    }

    /**
     * Crée une nouvelle tâche
     */
    public function creerTache($libelle, $description, $dateDebut, $dateFin, $idCoordination, $idOuvrier) {
        $sql = "INSERT INTO tache (libelle, description, date_debut, date_fin, statut, id_coordination, id_ouvrier) 
                VALUES (:libelle, :description, :date_debut, :date_fin, 'à faire', :id_coordination, :id_ouvrier)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'libelle' => $libelle,
                'description' => $description,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'id_coordination' => $idCoordination,
                'id_ouvrier' => $idOuvrier
            ]);
            return ['success' => true, 'message' => 'Tâche créée avec succès'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }

    /**
     * Récupère les tâches d'un ouvrier
     */
    public function getTachesOuvrier($idOuvrier) {
        $sql = "SELECT t.*, c.nom as coord_nom, c.prenom as coord_prenom 
                FROM tache t
                JOIN coordination c ON t.id_coordination = c.id_coordination
                WHERE t.id_ouvrier = :id_ouvrier
                ORDER BY t.date_debut DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_ouvrier' => $idOuvrier]);
        return $stmt->fetchAll();
    }

    /**
     * Met à jour le statut d'une tâche
     */
    public function updateStatutTache($idTache, $statut) {
        $sql = "UPDATE tache SET statut = :statut WHERE id_tache = :id_tache";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['statut' => $statut, 'id_tache' => $idTache]);
    }

    /**
     * Récupère toutes les tâches d'un département
     */
    public function getTachesDepartement($departement) {
        $sql = "SELECT t.*, o.nom as ouv_nom, o.prenom as ouv_prenom, c.nom as coord_nom
                FROM tache t
                JOIN ouvrier o ON t.id_ouvrier = o.id_ouvrier
                JOIN coordination c ON t.id_coordination = c.id_coordination
                WHERE o.departement = :departement
                ORDER BY t.date_debut DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['departement' => $departement]);
        return $stmt->fetchAll();
    }
}
?>


<?php
// DepartementManager.class.php - Gestion des départements multiples
require_once 'Database.class.php';
require_once 'departements.php';

class DepartementManager {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getPdo();
    }

    /**
     * Récupère les départements d'un ouvrier
     */
    public function getDepartementsOuvrier($idOuvrier) {
        $sql = "SELECT departement FROM ouvrier_departement WHERE id_ouvrier = :id_ouvrier ORDER BY departement";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_ouvrier' => $idOuvrier]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Récupère les départements d'un responsable
     */
    public function getDepartementsResponsable($idResponsable) {
        $sql = "SELECT departement FROM responsable_departement WHERE id_responsable = :id_responsable ORDER BY departement";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_responsable' => $idResponsable]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Ajoute un département à un ouvrier
     */
    public function ajouterDepartementOuvrier($idOuvrier, $departement) {
        // Vérifier le nombre maximum de départements
        $departements = $this->getDepartementsOuvrier($idOuvrier);
        if (count($departements) >= 2) {
            return ['success' => false, 'message' => 'Un ouvrier ne peut avoir que 2 départements maximum.'];
        }

        // Vérifier que le département est valide
        if (!isDepartementValide($departement)) {
            return ['success' => false, 'message' => 'Département invalide.'];
        }

        // Normaliser le département
        $departement = normaliserDepartement($departement);

        // Vérifier que le département n'est pas déjà assigné
        if (in_array($departement, $departements)) {
            return ['success' => false, 'message' => 'Cet ouvrier a déjà ce département.'];
        }

        try {
            $sql = "INSERT INTO ouvrier_departement (id_ouvrier, departement) VALUES (:id_ouvrier, :departement)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id_ouvrier' => $idOuvrier,
                'departement' => $departement
            ]);
            return ['success' => true, 'message' => 'Département ajouté avec succès.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }

    /**
     * Ajoute un département à un responsable
     */
    public function ajouterDepartementResponsable($idResponsable, $departement) {
        // Vérifier le nombre maximum de départements
        $departements = $this->getDepartementsResponsable($idResponsable);
        if (count($departements) >= 2) {
            return ['success' => false, 'message' => 'Un responsable ne peut avoir que 2 départements maximum.'];
        }

        // Vérifier que le département est valide
        if (!isDepartementValide($departement)) {
            return ['success' => false, 'message' => 'Département invalide.'];
        }

        // Normaliser le département
        $departement = normaliserDepartement($departement);

        // Vérifier que le département n'est pas déjà assigné
        if (in_array($departement, $departements)) {
            return ['success' => false, 'message' => 'Vous avez déjà ce département.'];
        }

        try {
            $sql = "INSERT INTO responsable_departement (id_responsable, departement) VALUES (:id_responsable, :departement)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id_responsable' => $idResponsable,
                'departement' => $departement
            ]);
            return ['success' => true, 'message' => 'Département ajouté avec succès.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }

    /**
     * Supprime un département d'un ouvrier
     */
    public function supprimerDepartementOuvrier($idOuvrier, $departement) {
        $sql = "DELETE FROM ouvrier_departement WHERE id_ouvrier = :id_ouvrier AND departement = :departement";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id_ouvrier' => $idOuvrier,
            'departement' => $departement
        ]);
    }

    /**
     * Supprime un département d'un responsable
     */
    public function supprimerDepartementResponsable($idResponsable, $departement) {
        $sql = "DELETE FROM responsable_departement WHERE id_responsable = :id_responsable AND departement = :departement";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id_responsable' => $idResponsable,
            'departement' => $departement
        ]);
    }

    /**
     * Définit les départements d'un responsable (remplace tous les départements existants)
     */
    public function setDepartementsResponsable($idResponsable, $departements) {
        // Vérifier le nombre maximum
        if (count($departements) > 2) {
            return ['success' => false, 'message' => 'Un responsable ne peut avoir que 2 départements maximum.'];
        }

        // Vérifier et normaliser les départements
        $departementsNormalises = [];
        foreach ($departements as $dep) {
            if (!isDepartementValide($dep)) {
                return ['success' => false, 'message' => 'Département invalide: ' . $dep];
            }
            $depNormalise = normaliserDepartement($dep);
            if (!in_array($depNormalise, $departementsNormalises)) {
                $departementsNormalises[] = $depNormalise;
            }
        }

        try {
            $this->db->beginTransaction();

            // Supprimer les anciens départements
            $sql = "DELETE FROM responsable_departement WHERE id_responsable = :id_responsable";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_responsable' => $idResponsable]);

            // Ajouter les nouveaux départements
            $sql = "INSERT INTO responsable_departement (id_responsable, departement) VALUES (:id_responsable, :departement)";
            $stmt = $this->db->prepare($sql);
            foreach ($departementsNormalises as $dep) {
                $stmt->execute([
                    'id_responsable' => $idResponsable,
                    'departement' => $dep
                ]);
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'Départements mis à jour avec succès.'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }
}
?>


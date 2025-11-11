<?php
// PointageManager.class.php
require_once 'Database.class.php';

class PointageManager {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getPdo();
    }

    /**
     * Enregistre le pointage d'un ouvrier (présent, absent, retard)
     */
    public function pointerOuvrier($idOuvrier, $idResponsable, $statut = 'présent', $typePointage = 'entrée') {
        $dateHeure = date('Y-m-d H:i:s');
        $dateAujourdhui = date('Y-m-d');
        
        // Vérification si déjà pointé aujourd'hui - on remplace le pointage existant
        $this->supprimerPointageJour($idOuvrier, $dateAujourdhui);

        // Si absent, on enregistre directement
        if ($statut === 'absent') {
            $sql = "INSERT INTO pointage_ouvrier (id_ouvrier, id_responsable, date_heure_pointage, type_pointage, statut) 
                    VALUES (:id_ouvrier, :id_responsable, :date_heure_pointage, :type_pointage, :statut)";
            
            try {
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'id_ouvrier' => $idOuvrier,
                    'id_responsable' => $idResponsable,
                    'date_heure_pointage' => $dateAujourdhui . ' 00:00:00',
                    'type_pointage' => 'entrée',
                    'statut' => 'absent'
                ]);
                return ['success' => true, 'message' => 'Absence enregistrée avec succès.'];
            } catch (PDOException $e) {
                return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
            }
        }

        // Pour présent ou retard, on utilise l'heure actuelle
        $heureActuelle = date('H:i:s');
        if ($statut === 'présent' && $heureActuelle > HEURE_DEBUT_TRAVAIL) {
            $statut = 'retard';
        }

        $sql = "INSERT INTO pointage_ouvrier (id_ouvrier, id_responsable, date_heure_pointage, type_pointage, statut) 
                VALUES (:id_ouvrier, :id_responsable, :date_heure_pointage, :type_pointage, :statut)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id_ouvrier' => $idOuvrier,
                'id_responsable' => $idResponsable,
                'date_heure_pointage' => $dateHeure,
                'type_pointage' => $typePointage,
                'statut' => $statut
            ]);
            return ['success' => true, 'message' => 'Pointage enregistré avec succès. Statut: ' . $statut];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }

    /**
     * Supprime le pointage d'un ouvrier pour un jour donné
     */
    private function supprimerPointageJour($idOuvrier, $date) {
        $sql = "DELETE FROM pointage_ouvrier 
                WHERE id_ouvrier = :id_ouvrier 
                AND DATE(date_heure_pointage) = :date";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_ouvrier' => $idOuvrier, 'date' => $date]);
    }

    /**
     * Vérifie si l'ouvrier a déjà été pointé aujourd'hui
     */
    private function dejaPointeAujourdhui($idOuvrier, $date) {
        $sql = "SELECT COUNT(*) FROM pointage_ouvrier 
                WHERE id_ouvrier = :id_ouvrier 
                AND DATE(date_heure_pointage) = :date";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_ouvrier' => $idOuvrier, 'date' => $date]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Récupère les pointages d'un ouvrier
     */
    public function getPointagesOuvrier($idOuvrier, $limit = 30) {
        $sql = "SELECT po.*, r.nom as resp_nom, r.prenom as resp_prenom 
                FROM pointage_ouvrier po
                JOIN responsable r ON po.id_responsable = r.id_responsable
                WHERE po.id_ouvrier = :id_ouvrier
                ORDER BY po.date_heure_pointage DESC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_ouvrier', $idOuvrier, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Récupère les pointages d'un département
     */
    public function getPointagesDepartement($departement, $date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }
        
        $sql = "SELECT po.*, o.nom as ouv_nom, o.prenom as ouv_prenom, r.nom as resp_nom
                FROM pointage_ouvrier po
                JOIN ouvrier o ON po.id_ouvrier = o.id_ouvrier
                JOIN responsable r ON po.id_responsable = r.id_responsable
                WHERE o.departement = :departement
                AND DATE(po.date_heure_pointage) = :date
                ORDER BY po.date_heure_pointage DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['departement' => $departement, 'date' => $date]);
        return $stmt->fetchAll();
    }

    /**
     * Récupère le statut de pointage d'un ouvrier pour aujourd'hui
     */
    public function getStatutPointageAujourdhui($idOuvrier) {
        $date = date('Y-m-d');
        $sql = "SELECT statut FROM pointage_ouvrier 
                WHERE id_ouvrier = :id_ouvrier 
                AND DATE(date_heure_pointage) = :date 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_ouvrier' => $idOuvrier, 'date' => $date]);
        $result = $stmt->fetch();
        return $result ? $result['statut'] : null;
    }

    /**
     * Récupère tous les ouvriers d'un département avec leur statut de pointage
     */
    public function getOuvriersAvecStatut($departement, $date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }
        
        // Utiliser la table de liaison si elle existe, sinon utiliser le champ departement de la table ouvrier
        $sql = "SELECT o.*, 
                (SELECT statut FROM pointage_ouvrier po 
                 WHERE po.id_ouvrier = o.id_ouvrier 
                 AND DATE(po.date_heure_pointage) = :date 
                 LIMIT 1) as statut_pointage,
                (SELECT date_heure_pointage FROM pointage_ouvrier po 
                 WHERE po.id_ouvrier = o.id_ouvrier 
                 AND DATE(po.date_heure_pointage) = :date 
                 LIMIT 1) as heure_pointage
                FROM ouvrier o
                LEFT JOIN ouvrier_departement od ON o.id_ouvrier = od.id_ouvrier
                WHERE (od.departement = :departement OR o.departement = :departement)
                GROUP BY o.id_ouvrier
                ORDER BY o.nom, o.prenom";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['departement' => $departement, 'date' => $date]);
        return $stmt->fetchAll();
    }

    /**
     * Récupère les pointages d'un département (utilise la table de liaison si disponible)
     */
    public function getPointagesDepartement($departement, $date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }
        
        $sql = "SELECT po.*, o.nom as ouv_nom, o.prenom as ouv_prenom, r.nom as resp_nom
                FROM pointage_ouvrier po
                JOIN ouvrier o ON po.id_ouvrier = o.id_ouvrier
                LEFT JOIN ouvrier_departement od ON o.id_ouvrier = od.id_ouvrier
                JOIN responsable r ON po.id_responsable = r.id_responsable
                WHERE (od.departement = :departement OR o.departement = :departement)
                AND DATE(po.date_heure_pointage) = :date
                GROUP BY po.id_pointage
                ORDER BY po.date_heure_pointage DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['departement' => $departement, 'date' => $date]);
        return $stmt->fetchAll();
    }
}
?>


<?php
// Pointage.class.php
require_once 'User.class.php'; // Pour accéder à la connexion DB et aux constantes

class Pointage {
    private $db;

    public function __construct() {
        $this->db = (new DB())->getPdo();
    }

    /**
     * Enregistre le pointage d'un ouvrier par un responsable.
     */
    public function enregistrerPointage($ouvrierId, $responsableId, $heureArrivee) {
        $datePointage = date('Y-m-d');
        $heureActuelle = date('H:i:s');
        
        // 1. VÉRIFICATION DE LA CONTRAINTE TEMPORELLE (jusqu’à 18h30)
        if ($heureActuelle > HEURE_LIMITE_POINTAGE) {
            return ['success' => false, 'message' => "Erreur : Le pointage est impossible après 18h30."];
        }

        // 2. VÉRIFICATION DU DOUBLE POINTAGE
        if ($this->hasBeenPointedToday($ouvrierId, $datePointage)) {
             return ['success' => false, 'message' => "Erreur : Cet ouvrier a déjà été pointé aujourd'hui."];
        }
        
        // 3. VÉRIFICATION DU DÉPARTEMENT (Le responsable n'enregistre que son département)
        if (!$this->checkOuvrierInResponsableDepartment($ouvrierId, $responsableId)) {
            return ['success' => false, 'message' => "Erreur : Cet ouvrier n'appartient pas à votre département."];
        }


        // 4. CALCUL DU STATUT (Présent ou Retard)
        if (strtotime($heureArrivee) > strtotime(HEURE_DEBUT_TRAVAIL)) {
            $statut = 'Retard';
        } else {
            $statut = 'Présent';
        }
        
        // 5. INSERTION
        $sql = "INSERT INTO pointage (ouvrier_id, responsable_id, heure_arrivee, date_pointage, statut) 
                VALUES (:ouvrier_id, :responsable_id, :heure_arrivee, :date_pointage, :statut)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'ouvrier_id' => $ouvrierId,
                'responsable_id' => $responsableId,
                'heure_arrivee' => $heureArrivee,
                'date_pointage' => $datePointage,
                'statut' => $statut
            ]);
            return ['success' => true, 'message' => "Pointage enregistré. Statut: " . $statut];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => "Erreur BD: " . $e->getMessage()];
        }
    }
    
    /** Vérifie si l'ouvrier est dans le département du responsable */
    private function checkOuvrierInResponsableDepartment($ouvrierId, $responsableId) {
        $sql = "SELECT t1.id FROM users t1
                JOIN users t2 ON t1.departement = t2.departement
                WHERE t1.id = :ouvrierId AND t2.id = :responsableId AND t1.grade_id = 4";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ouvrierId' => $ouvrierId, 'responsableId' => $responsableId]);
        return $stmt->rowCount() > 0;
    }
    
    /** Vérifie si l'ouvrier a déjà été pointé aujourd'hui. */
    private function hasBeenPointedToday($ouvrierId, $datePointage) {
        $sql = "SELECT id FROM pointage WHERE ouvrier_id = :ouvrierId AND date_pointage = :datePointage";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ouvrierId' => $ouvrierId, 'datePointage' => $datePointage]);
        return $stmt->rowCount() > 0;
    }

    /** Fonction de gestion automatique des absences (à lancer par CRON) */
    public function gestionAbsencesAutomatique() {
        if (date('H:i:s') > HEURE_LIMITE_POINTAGE) {
            // ... Logique d'insertion des statuts 'Absent' pour les non-pointés ...
        }
    }
}
?>
<?php
// Pointage.class.php
require_once 'User.class.php'; 

class Pointage {
    private $db;
    public function __construct() { 
        $this->db = (new DB())->getPdo(); 
    }

    /**
     * Enregistre le pointage d'un ouvrier par un responsable.
     * (Logique déjà en place: limite 18h30, double pointage, vérif. département)
     */
    public function enregistrerPointage($ouvrierId, $responsableId, $heureArrivee) {
        $datePointage = date('Y-m-d');
        $heureActuelle = date('H:i:s');
        
        // 1. VÉRIFICATION DE LA CONTRAINTE TEMPORELLE (jusqu’à 18h30)
        if ($heureActuelle > HEURE_LIMITE_POINTAGE) {
            return ['success' => false, 'message' => "Erreur : Le pointage est impossible après 18h30."];
        }
        // ... (autres vérifications : double pointage, département) ...

        if ($this->hasBeenPointedToday($ouvrierId, $datePointage)) { /* ... */ }
        if (!$this->checkOuvrierInResponsableDepartment($ouvrierId, $responsableId)) { /* ... */ }

        // 4. CALCUL DU STATUT
        $statut = (strtotime($heureArrivee) > strtotime(HEURE_DEBUT_TRAVAIL)) ? 'Retard' : 'Présent';
        
        // 5. INSERTION DANS LA BASE DE DONNÉES
        $sql = "INSERT INTO pointage (ouvrier_id, responsable_id, heure_arrivee, date_pointage, statut) 
                VALUES (:ouvrier_id, :responsable_id, :heure_arrivee, :date_pointage, :statut)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'ouvrier_id' => $ouvrierId, 'responsable_id' => $responsableId, 'heure_arrivee' => $heureArrivee,
                'date_pointage' => $datePointage, 'statut' => $statut
            ]);
            return ['success' => true, 'message' => "Pointage enregistré. Statut: " . $statut];
        } catch (PDOException $e) { return ['success' => false, 'message' => "Erreur BD: " . $e->getMessage()]; }
    }
    
    // =============================================================
    // NOUVELLE MÉTHODE : GESTION AUTOMATIQUE DES ABSENCES
    // =============================================================

    /**
     * Marque automatiquement comme 'Absent' tous les ouvriers non pointés pour la date du jour.
     * DOIT ÊTRE EXÉCUTÉE PAR UNE TÂCHE CRON/SCHEDULER APRES 18H30.
     */
    public function markDailyAbsences() {
        $datePointage = date('Y-m-d');
        
        // 1. Trouver tous les IDs des ouvriers actifs (grade_id = 4)
        $sqlOuvriers = "SELECT id FROM users WHERE grade_id = 4 AND est_actif = TRUE";
        $ouvriers = $this->db->query($sqlOuvriers)->fetchAll(PDO::FETCH_COLUMN);

        if (empty($ouvriers)) {
            return ['success' => true, 'message' => "Aucun ouvrier actif trouvé."];
        }

        $absenceCount = 0;
        
        // 2. Parcourir chaque ouvrier
        foreach ($ouvriers as $ouvrierId) {
            // Vérifier si l'ouvrier a déjà été pointé aujourd'hui
            if (!$this->hasBeenPointedToday($ouvrierId, $datePointage)) {
                
                // 3. S'il n'est pas pointé, l'enregistrer comme Absent
                // responsable_id est NULL (absence système)
                $sqlInsertAbsent = "INSERT INTO pointage (ouvrier_id, responsable_id, heure_arrivee, date_pointage, statut) 
                                    VALUES (:ouvrier_id, NULL, '00:00:00', :date_pointage, 'Absent')";
                
                $stmt = $this->db->prepare($sqlInsertAbsent);
                $stmt->execute(['ouvrier_id' => $ouvrierId, 'date_pointage' => $datePointage]);
                $absenceCount++;
            }
        }
        
        return [
            'success' => true, 
            'message' => "$absenceCount absence(s) enregistrée(s) automatiquement pour le $datePointage."
        ];
    }
    
    // ... (méthodes privées et getPointageHistoryByOuvrier restent inchangées)

    private function checkOuvrierInResponsableDepartment($ouvrierId, $responsableId) { /* ... */ }
    private function hasBeenPointedToday($ouvrierId, $datePointage) { /* ... */ }
    public function getPointageHistoryByOuvrier(int $ouvrierId) { /* ... */ }
    public function getWorkerStats() { /* ... */ }
}
?>
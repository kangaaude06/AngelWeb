<?php
// Pointage.class.php
require_once 'User.class.php'; 

class Pointage {
    private $db;
    public function __construct() { 
        $this->db = (new DB())->getPdo(); 
    }

    public function enregistrerPointage($ouvrierId, $responsableId, $heureArrivee) {
        $datePointage = date('Y-m-d');
        $heureActuelle = date('H:i:s');
        
        // 1. VÉRIFICATION DE LA CONTRAINTE TEMPORELLE (jusqu’à 18h30)
        if ($heureActuelle > HEURE_LIMITE_POINTAGE) {
            return array('success' => false, 'message' => "Erreur : Le pointage est impossible après 18h30.");
        }
        
        // 2. VÉRIFICATION DOUBLE POINTAGE
        if ($this->hasBeenPointedToday($ouvrierId, $datePointage)) {
            return array('success' => false, 'message' => "Erreur : Cet ouvrier a déjà été pointé aujourd'hui.");
        }
        
        // 3. VÉRIFICATION DEPARTEMENT
        if (!$this->checkOuvrierInResponsableDepartment($ouvrierId, $responsableId)) {
             return array('success' => false, 'message' => "Erreur : Vous ne pouvez pointer que les ouvriers de votre département.");
        }

        // 4. CALCUL DU STATUT
        $statut = (strtotime($heureArrivee) > strtotime(HEURE_DEBUT_TRAVAIL)) ? 'Retard' : 'Présent';
        
        // 5. INSERTION DANS LA BASE DE DONNÉES
        $sql = "INSERT INTO pointage (ouvrier_id, responsable_id, heure_arrivee, date_pointage, statut) 
                VALUES (:ouvrier_id, :responsable_id, :heure_arrivee, :date_pointage, :statut)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array(
                'ouvrier_id' => $ouvrierId, 'responsable_id' => $responsableId, 'heure_arrivee' => $heureArrivee,
                'date_pointage' => $datePointage, 'statut' => $statut
            ));
            return array('success' => true, 'message' => "Pointage enregistré. Statut: " . $statut);
        } catch (PDOException $e) { return array('success' => false, 'message' => "Erreur BD: " . $e->getMessage()); }
    }
    
    /**
     * Marque automatiquement comme 'Absent' tous les ouvriers non pointés pour la date du jour.
     */
    public function markDailyAbsences() {
        $datePointage = date('Y-m-d');
        
        // 1. Trouver tous les IDs des ouvriers actifs (grade_id = 4)
        $sqlOuvriers = "SELECT id FROM users WHERE grade_id = 4 AND est_actif = TRUE";
        $ouvriers = $this->db->query($sqlOuvriers)->fetchAll(PDO::FETCH_COLUMN);

        if (empty($ouvriers)) {
            return array('success' => true, 'message' => "Aucun ouvrier actif trouvé.");
        }

        $absenceCount = 0;
        
        // 2. Parcourir chaque ouvrier
        foreach ($ouvriers as $ouvrierId) {
            // Vérifier si l'ouvrier a déjà été pointé aujourd'hui
            if (!$this->hasBeenPointedToday($ouvrierId, $datePointage)) {
                
                // 3. S'il n'est pas pointé, l'enregistrer comme Absent
                $sqlInsertAbsent = "INSERT INTO pointage (ouvrier_id, responsable_id, heure_arrivee, date_pointage, statut) 
                                    VALUES (:ouvrier_id, NULL, '00:00:00', :date_pointage, 'Absent')";
                
                $stmt = $this->db->prepare($sqlInsertAbsent);
                $stmt->execute(array('ouvrier_id' => $ouvrierId, 'date_pointage' => $datePointage));
                $absenceCount++;
            }
        }
        
        return array(
            'success' => true, 
            'message' => "$absenceCount absence(s) enregistrée(s) automatiquement pour le $datePointage."
        );
    }
    
    private function checkOuvrierInResponsableDepartment($ouvrierId, $responsableId) {
        $sql = "SELECT u_ouv.departement FROM users u_ouv
                JOIN users u_resp ON u_resp.id = :responsableId
                WHERE u_ouv.id = :ouvrierId AND u_ouv.departement = u_resp.departement";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array('ouvrierId' => $ouvrierId, 'responsableId' => $responsableId));
        return $stmt->rowCount() > 0;
    }
    
    private function hasBeenPointedToday($ouvrierId, $datePointage) {
        $sql = "SELECT id FROM pointage WHERE ouvrier_id = :ouvrierId AND date_pointage = :datePointage";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array('ouvrierId' => $ouvrierId, 'datePointage' => $datePointage));
        return $stmt->rowCount() > 0;
    }
    
    // Type hint 'int' retiré
    public function getPointageHistoryByOuvrier($ouvrierId) {
        $sql = "SELECT p.*, r.prenom AS resp_prenom, r.nom AS resp_nom
                FROM pointage p LEFT JOIN users r ON p.responsable_id = r.id
                WHERE p.ouvrier_id = :ouvrierId ORDER BY p.date_pointage DESC, p.heure_arrivee DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array('ouvrierId' => $ouvrierId));
        return $stmt->fetchAll();
    }
    
    // Type de retour retiré
    public function getWorkerStats() {
        $sql = "SELECT 
            u.id, u.nom, u.prenom, u.departement,
            SUM(CASE WHEN p.statut = 'Présent' THEN 1 ELSE 0 END) AS total_present,
            SUM(CASE WHEN p.statut = 'Retard' THEN 1 ELSE 0 END) AS total_retard,
            SUM(CASE WHEN p.statut = 'Absent' THEN 1 ELSE 0 END) AS total_absent,
            COUNT(p.id) AS total_pointage
        FROM users u 
        LEFT JOIN pointage p ON u.id = p.ouvrier_id
        WHERE u.grade_id = 4 AND u.est_actif = TRUE
        GROUP BY u.id, u.nom, u.prenom, u.departement
        ORDER BY total_present DESC, u.nom";
        return $this->db->query($sql)->fetchAll();
    }
}
?>
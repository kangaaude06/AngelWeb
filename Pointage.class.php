<?php
// Pointage.class.php
require_once 'User.class.php'; 

class Pointage {
    private $db;
    public function __construct() { 
        // Initialisation de la connexion BD
        $this->db = (new DB())->getPdo(); 
    }
    
    /**
     * Vérifie si un ouvrier a déjà été pointé aujourd'hui.
     */
    public function hasBeenPointedToday($ouvrierId, $datePointage) {
        $sql = "SELECT id FROM pointage WHERE ouvrier_id = :ouvrierId AND date_pointage = :datePointage";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array('ouvrierId' => $ouvrierId, 'datePointage' => $datePointage));
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Vérifie si l'ouvrier appartient au même département que le responsable.
     */
    public function checkOuvrierInResponsableDepartment($ouvrierId, $responsableId) {
        $sql = "SELECT COUNT(*) FROM users u1
                JOIN users u2 ON u1.departement = u2.departement
                WHERE u1.id = :ouvrierId AND u2.id = :responsableId";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array('ouvrierId' => $ouvrierId, 'responsableId' => $responsableId));
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Enregistre le pointage d'un ouvrier pour la journée (utilisé par le Responsable).
     */
    public function enregistrerPointage($ouvrierId, $responsableId, $heureArrivee) {
        $datePointage = date('Y-m-d');
        $heureActuelle = date('H:i:s');
        
        // 1. VÉRIFICATION DE LA CONTRAINTE TEMPORELLE
        if ($heureActuelle > HEURE_LIMITE_POINTAGE) {
            return array('success' => false, 'message' => "Erreur : Le pointage est impossible après " . HEURE_LIMITE_POINTAGE . ".");
        }
        
        // 2. VÉRIFICATION DOUBLE POINTAGE
        if ($this->hasBeenPointedToday($ouvrierId, $datePointage)) {
            return array('success' => false, 'message' => "Erreur : Cet ouvrier a déjà été pointé aujourd'hui.");
        }
        
        // 3. VÉRIFICATION DEPARTEMENT
        if (!$this->checkOuvrierInResponsableDepartment($ouvrierId, $responsableId)) {
             return array('success' => false, 'message' => "Erreur : Vous ne pouvez pointer que les ouvriers de votre département.");
        }

        // 4. CALCUL DU STATUT (Présent ou Retard)
        $statut = ($heureArrivee > HEURE_DEBUT_TRAVAIL) ? 'Retard' : 'Présent';

        // 5. Enregistrer le nouveau pointage
        $sqlInsert = "INSERT INTO pointage (ouvrier_id, responsable_id, date_pointage, heure_arrivee, statut) 
                      VALUES (:ouvrier_id, :responsable_id, :date_pointage, :heure_arrivee, :statut)";
        
        try {
            $stmtInsert = $this->db->prepare($sqlInsert);
            $stmtInsert->execute(array(
                'ouvrier_id' => $ouvrierId,
                'responsable_id' => $responsableId,
                'date_pointage' => $datePointage,
                'heure_arrivee' => $heureArrivee, // Utilisation de l'heure saisie par le Responsable
                'statut' => $statut
            ));
            return array('success' => true, 'message' => "Pointage enregistré (Statut: $statut).");
        } catch (PDOException $e) { 
            return array('success' => false, 'message' => "Erreur de base de données : " . $e->getMessage());
        }
    }
    
    /**
     * Récupère les statistiques agrégées pour tous les ouvriers (pour Admin Tableau).
     */
    public function getWorkerStats() {
        $sql = "SELECT 
            u.id, u.nom, u.prenom, u.departement,
            SUM(CASE WHEN p.statut = 'Présent' THEN 1 ELSE 0 END) AS total_present,
            SUM(CASE WHEN p.statut = 'Retard' THEN 1 ELSE 0 END) AS total_retard,
            SUM(CASE WHEN p.statut = 'Absent' THEN 1 ELSE 0 END) AS total_absent,
            COUNT(p.id) AS total_pointage
        FROM users u 
        LEFT JOIN pointage p ON u.id = p.ouvrier_id
        WHERE u.grade_id = 4
        GROUP BY u.id, u.nom, u.prenom, u.departement
        ORDER BY total_present DESC, u.nom";
        
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Récupère l'historique de pointage d'un ouvrier (pour dashboard_ouvrier).
     */
    public function getPointageHistoryByOuvrier($ouvrierId) {
        $sql = "SELECT p.*, r.prenom AS resp_prenom, r.nom AS resp_nom
                FROM pointage p LEFT JOIN users r ON p.responsable_id = r.id
                WHERE p.ouvrier_id = :ouvrierId ORDER BY p.date_pointage DESC, p.heure_arrivee DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array('ouvrierId' => $ouvrierId));
        return $stmt->fetchAll();
    }

    /**
     * Marque automatiquement les absences quotidiennes (pour cron_absence_checker).
     */
    public function markDailyAbsences() {
        $datePointage = date('Y-m-d');
        
        // 1. Trouver les Ouvriers (grade_id=4) qui ne sont PAS dans le pointage d'aujourd'hui
        $sqlSelect = "SELECT id, departement FROM users 
                      WHERE grade_id = 4 
                      AND id NOT IN (SELECT ouvrier_id FROM pointage WHERE date_pointage = :date_pointage)";
        
        $stmtSelect = $this->db->prepare($sqlSelect);
        $stmtSelect->execute(array('date_pointage' => $datePointage));
        $absentWorkers = $stmtSelect->fetchAll();
        
        if (empty($absentWorkers)) {
            return array('success' => true, 'message' => "Aucune absence à marquer pour aujourd'hui.");
        }

        // 2. Insérer le statut 'Absent' pour chaque ouvrier manquant
        $count = 0;
        $sqlInsert = "INSERT INTO pointage (ouvrier_id, responsable_id, date_pointage, heure_arrivee, statut) 
                      VALUES (:ouvrier_id, NULL, :date_pointage, NULL, 'Absent')";
        $stmtInsert = $this->db->prepare($sqlInsert);
        
        foreach ($absentWorkers as $worker) {
            $stmtInsert->execute(array(
                'ouvrier_id' => $worker['id'],
                'date_pointage' => $datePointage,
            ));
            $count++;
            
            // OPTIONNEL : Logguer l'absence ou envoyer une notification au responsable du département.
        }
        
        return array('success' => true, 'message' => "$count absences marquées pour la journée du $datePointage.");
    }
}
?>
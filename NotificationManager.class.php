<?php
// NotificationManager.class.php
require_once 'Database.class.php';

class NotificationManager {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getPdo();
    }

    /**
     * Crée une notification
     */
    public function creerNotification($contenu, $idCoordination, $typeNotification = 'message', $idOuvriers = [], $idResponsables = []) {
        $this->db->beginTransaction();
        
        try {
            // Insère la notification
            $sql = "INSERT INTO notification (contenu, id_coordination, type_notification) 
                    VALUES (:contenu, :id_coordination, :type_notification)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'contenu' => $contenu,
                'id_coordination' => $idCoordination,
                'type_notification' => $typeNotification
            ]);
            
            $idNotification = $this->db->lastInsertId();
            
            // Associe aux ouvriers
            if (!empty($idOuvriers)) {
                $sql = "INSERT INTO notification_ouvrier (id_notification, id_ouvrier) VALUES (:id_notification, :id_ouvrier)";
                $stmt = $this->db->prepare($sql);
                foreach ($idOuvriers as $idOuvrier) {
                    $stmt->execute(['id_notification' => $idNotification, 'id_ouvrier' => $idOuvrier]);
                }
            }
            
            // Associe aux responsables
            if (!empty($idResponsables)) {
                $sql = "INSERT INTO notification_responsable (id_notification, id_responsable) VALUES (:id_notification, :id_responsable)";
                $stmt = $this->db->prepare($sql);
                foreach ($idResponsables as $idResponsable) {
                    $stmt->execute(['id_notification' => $idNotification, 'id_responsable' => $idResponsable]);
                }
            }
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Notification envoyée avec succès'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }

    /**
     * Récupère les notifications d'un ouvrier
     */
    public function getNotificationsOuvrier($idOuvrier) {
        $sql = "SELECT n.*, c.nom as coord_nom 
                FROM notification n
                JOIN notification_ouvrier no ON n.id_notification = no.id_notification
                JOIN coordination c ON n.id_coordination = c.id_coordination
                WHERE no.id_ouvrier = :id_ouvrier
                ORDER BY n.date_envoi DESC
                LIMIT 20";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_ouvrier' => $idOuvrier]);
        return $stmt->fetchAll();
    }

    /**
     * Récupère les notifications d'un responsable
     */
    public function getNotificationsResponsable($idResponsable) {
        $sql = "SELECT n.*, c.nom as coord_nom 
                FROM notification n
                JOIN notification_responsable nr ON n.id_notification = nr.id_notification
                JOIN coordination c ON n.id_coordination = c.id_coordination
                WHERE nr.id_responsable = :id_responsable
                ORDER BY n.date_envoi DESC
                LIMIT 20";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_responsable' => $idResponsable]);
        return $stmt->fetchAll();
    }
}
?>


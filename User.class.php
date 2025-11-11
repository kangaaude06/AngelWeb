<?php
// User.class.php
require_once 'config.php'; 

// CLASSE DB
class DB {
    private $pdo;
    public function __construct() {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8';
        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données: " . $e->getMessage());
        }
    }
    public function getPdo() { return $this->pdo; }
}

// CLASSE USER
class User {
    private $db;
    public function __construct() { $this->db = (new DB())->getPdo(); }

    public function registerUser(array $data) {
        $sql = "INSERT INTO users (nom, prenom, numero_telephone, departement, grade_id, password_hash) 
                VALUES (:nom, :prenom, :numero_telephone, :departement, :grade_id, :password_hash)";
        $password_hash = isset($data['password']) ? password_hash($data['password'], PASSWORD_DEFAULT) : NULL;
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'nom' => $data['nom'], 'prenom' => $data['prenom'], 'numero_telephone' => $data['numero_telephone'] ?? NULL,
                'departement' => $data['departement'], 'grade_id' => $data['grade_id'], 'password_hash' => $password_hash
            ]);
        } catch (PDOException $e) { return false; } 
    }
    
    public function loginUser($numero_telephone, $password) {
        $sql = "SELECT u.*, g.nom_grade FROM users u JOIN grades g ON u.grade_id = g.id 
                WHERE u.numero_telephone = :numero_telephone";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['numero_telephone' => $numero_telephone]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return false;
    }

    public function getUsersByDepartment($departement, $gradeId) {
        $sql = "SELECT id, nom, prenom, departement, numero_telephone FROM users 
                WHERE departement = :departement AND grade_id = :gradeId AND est_actif = TRUE
                ORDER BY nom, prenom";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['departement' => $departement, 'gradeId' => $gradeId]);
        return $stmt->fetchAll();
    }
    
    // CORRECTION : Suppression du '?' dans le type de retour
    public function getAllUsers(string $searchTerm = '') { 
        $sql = "SELECT u.id, u.nom, u.prenom, u.numero_telephone, u.departement, g.nom_grade
                FROM users u JOIN grades g ON u.grade_id = g.id WHERE u.est_actif = TRUE ";
        $params = [];
        if (!empty($searchTerm)) {
            $sql .= " AND (u.nom LIKE :search OR u.prenom LIKE :search OR u.departement LIKE :search) ";
            $params['search'] = '%' . $searchTerm . '%';
        }
        $sql .= " ORDER BY g.nom_grade, u.nom";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }


    // Logique d'Inscription Ouvrier
    public function checkOuvrierExistsByInfo($nom, $prenom, $departement) {
        $sql = "SELECT id FROM users 
                WHERE nom = :nom AND prenom = :prenom AND departement = :departement 
                AND grade_id = 4 AND password_hash IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['nom' => $nom, 'prenom' => $prenom, 'departement' => $departement]);
        return $stmt->rowCount() > 0;
    }
    public function finalizeOuvrierRegistration(array $data) {
        $sql = "UPDATE users SET numero_telephone = :numero_telephone, password_hash = :password_hash 
                WHERE nom = :nom AND prenom = :prenom AND departement = :departement AND grade_id = 4";
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'numero_telephone' => $data['numero_telephone'], 'password_hash' => $password_hash,
                'nom' => $data['nom'], 'prenom' => $data['prenom'], 'departement' => $data['departement']
            ]);
        } catch (PDOException $e) { return false; }
    }
    
    /**
     * Envoie une communication SIMULÉE à un utilisateur via son numéro de téléphone.
     */
    public function sendCommunication(int $userId, string $message) {
        $sql = "SELECT numero_telephone, nom, prenom FROM users WHERE id = :userId";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['userId' => $userId]);
        $user = $stmt->fetch();

        if ($user && !empty($user['numero_telephone'])) {
            $logMessage = "[" . date('Y-m-d H:i:s') . "] MESSAGE SIMULÉ vers " . $user['prenom'] . " " . $user['nom'] . 
                          " (Numéro: " . $user['numero_telephone'] . "): " . $message . "\n";
            // Pour un envoi réel (Twilio, etc.), le code irait ici.
            
            return ['success' => true, 'numero' => $user['numero_telephone']];
        }
        
        return ['success' => false, 'message' => "Numéro non trouvé ou communication impossible."];
    }
}
?>
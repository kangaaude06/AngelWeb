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
    
<<<<<<< HEAD
    public function loginUser($numero_telephone, $password) {
        $sql = "SELECT u.*, g.nom_grade FROM users u JOIN grades g ON u.grade_id = g.id 
                WHERE u.numero_telephone = :numero_telephone";
=======
    /**
     * Authentification universelle par numéro de téléphone et grade
     */
    public function loginUser($identifiant, $password, $grade_id = null) {
        // Préparer la requête de base
        $sql = "SELECT u.*, g.nom_grade FROM users u 
                JOIN grades g ON u.grade_id = g.id 
                WHERE u.numero_telephone = :identifiant";
        
        // Ajouter le filtre sur le grade si spécifié
        if ($grade_id !== null) {
            $sql .= " AND u.grade_id = :grade_id";
        }
        
>>>>>>> 9908589319a8248a0a604cef3955a579ded8a2cb
        $stmt = $this->db->prepare($sql);
        
        // Lier les paramètres
        $params = ['identifiant' => $identifiant];
        if ($grade_id !== null) {
            $params['grade_id'] = $grade_id;
        }
        
        $stmt->execute($params);
        $user = $stmt->fetch();
<<<<<<< HEAD
        if ($user && password_verify($password, $user['password_hash'])) {
=======

        // Vérifier le mot de passe
        if ($user && !empty($user['password_hash']) && password_verify($password, $user['password_hash'])) {
>>>>>>> 9908589319a8248a0a604cef3955a579ded8a2cb
            return $user;
        }
        
        return false;
    }
    
    /**
     * Retourne le type d'utilisateur à partir de l'ID du grade
     */
    public function getUserTypeFromGrade($grade_id) {
        switch ($grade_id) {
            case 1: return 'admin';
            case 2: return 'coordination';
            case 3: return 'responsable';
            case 4: return 'ouvrier';
            default: return 'inconnu';
        }
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
<<<<<<< HEAD
    public function finalizeOuvrierRegistration(array $data) {
        $sql = "UPDATE users SET numero_telephone = :numero_telephone, password_hash = :password_hash 
                WHERE nom = :nom AND prenom = :prenom AND departement = :departement AND grade_id = 4";
=======

    /**
     * Finalise l'inscription de l'ouvrier (ajout du numéro de tél. et du mot de passe)
     * Vérifie d'abord que le numéro de téléphone n'est pas déjà utilisé
     */
    public function finalizeOuvrierRegistration(array $data) {
        // Vérifier d'abord si le numéro de téléphone est déjà utilisé
        $checkPhone = $this->db->prepare("SELECT id FROM users WHERE numero_telephone = ? AND id != ?");
        $checkPhone->execute([$data['numero_telephone'], $data['id'] ?? 0]);
        
        if ($checkPhone->rowCount() > 0) {
            return false; // Numéro déjà utilisé
        }

        $sql = "UPDATE users SET 
                    numero_telephone = :numero_telephone, 
                    password_hash = :password_hash,
                    date_inscription = NOW()
                WHERE nom = :nom 
                AND prenom = :prenom 
                AND departement = :departement 
                AND grade_id = 4";
        
>>>>>>> 9908589319a8248a0a604cef3955a579ded8a2cb
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        try {
            $stmt = $this->db->prepare($sql);
<<<<<<< HEAD
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
=======
            $result = $stmt->execute([
                'numero_telephone' => $data['numero_telephone'],
                'password_hash' => $password_hash,
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'departement' => $data['departement']
            ]);
            
            return $result && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erreur lors de la finalisation de l'inscription: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère la connexion PDO
     */
    public function getPdo() {
        return $this->db;
    }
    
    // ... Méthodes utilitaires pour la récupération des listes
>>>>>>> 9908589319a8248a0a604cef3955a579ded8a2cb
}
?>
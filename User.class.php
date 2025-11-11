<?php
// User.class.php
require_once 'config.php'; 

// CLASSE DE CONNEXION À LA BASE DE DONNÉES
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

    public function getPdo() {
        return $this->pdo;
    }
}

// CLASSE DE GESTION DES UTILISATEURS
class User {
    private $db;

    public function __construct() {
        $this->db = (new DB())->getPdo();
    }

    /**
     * Enregistre un nouvel utilisateur (utilisé par Admin, Coord, ou lors de l'initialisation).
     */
    public function registerUser(array $data) {
        $sql = "INSERT INTO users (nom, prenom, numero_telephone, departement, grade_id, password_hash) 
                VALUES (:nom, :prenom, :numero_telephone, :departement, :grade_id, :password_hash)";
        
        $password_hash = isset($data['password']) ? password_hash($data['password'], PASSWORD_DEFAULT) : NULL;
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'numero_telephone' => $data['numero_telephone'] ?? NULL,
                'departement' => $data['departement'],
                'grade_id' => $data['grade_id'],
                'password_hash' => $password_hash
            ]);
        } catch (PDOException $e) {
            return false; // Échec si le numéro de téléphone est déjà pris (UNIQUE)
        }
    }
    
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
        
        $stmt = $this->db->prepare($sql);
        
        // Lier les paramètres
        $params = ['identifiant' => $identifiant];
        if ($grade_id !== null) {
            $params['grade_id'] = $grade_id;
        }
        
        $stmt->execute($params);
        $user = $stmt->fetch();

        // Vérifier le mot de passe
        if ($user && !empty($user['password_hash']) && password_verify($password, $user['password_hash'])) {
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

    // =============================================================
    // LOGIQUE D'INSCRIPTION DE L'OUVRIER (par l'ouvrier lui-même)
    // =============================================================

    /** Vérifie si l'ouvrier a été initialisé (grade 4, pas de mot de passe) */
    public function checkOuvrierExistsByInfo($nom, $prenom, $departement) {
        $sql = "SELECT id FROM users 
                WHERE nom = :nom AND prenom = :prenom AND departement = :departement 
                AND grade_id = 4 AND password_hash IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['nom' => $nom, 'prenom' => $prenom, 'departement' => $departement]);
        return $stmt->rowCount() > 0;
    }

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
        
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        try {
            $stmt = $this->db->prepare($sql);
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
}
?>
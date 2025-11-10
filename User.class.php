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
     * Authentification universelle par numéro de téléphone.
     */
    public function loginUser($numero_telephone, $password) {
        $sql = "SELECT u.*, g.nom_grade FROM users u JOIN grades g ON u.grade_id = g.id 
                WHERE u.numero_telephone = :numero_telephone";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['numero_telephone' => $numero_telephone]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            return $user; // Retourne toutes les données utilisateur, y compris le grade
        }
        return false;
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

    /** Finalise l'inscription de l'ouvrier (ajout du numéro de tél. et du mot de passe) */
    public function finalizeOuvrierRegistration(array $data) {
        $sql = "UPDATE users SET 
                    numero_telephone = :numero_telephone, 
                    password_hash = :password_hash 
                WHERE nom = :nom AND prenom = :prenom AND departement = :departement AND grade_id = 4";
        
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'numero_telephone' => $data['numero_telephone'],
                'password_hash' => $password_hash,
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'departement' => $data['departement']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // ... Méthodes utilitaires pour la récupération des listes
}
?>
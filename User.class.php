<?php
// User.class.php
// Ce fichier contient la logique de connexion (DB) et de gestion des utilisateurs (User)
require_once 'config.php'; 

// CLASSE DB : Gère la connexion à la base de données (Pattern Singleton simplifié)
class DB {
    private $pdo;
    
    public function __construct() {
        // Paramètres de connexion tirés de config.php
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8';
        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Afficher les erreurs SQL
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Récupérer les données en tableau associatif
            ));
        } catch (PDOException $e) {
            // Affichage de l'erreur si la connexion DB échoue
            die("Erreur de connexion à la base de données: " . $e->getMessage() . 
                "<br>Veuillez vérifier les identifiants DB_HOST, DB_USER et DB_PASS dans config.php.");
        }
    }
    
    // Fournit l'objet PDO aux autres classes (User, Tache, Pointage)
    public function getPdo() { 
        return $this->pdo; 
    }
}

// CLASSE USER : Gère l'authentification et les opérations sur les utilisateurs
class User {
    private $db;
    
    public function __construct() { 
        // Récupère l'objet PDO pour effectuer les requêtes
        $this->db = (new DB())->getPdo(); 
    }

    /**
     * Enregistre un nouvel utilisateur.
     * @param array $data Données de l'utilisateur.
     * @return bool Succès de l'opération.
     */
    public function registerUser($data) {
        // Définir grade_id: par défaut 4 (Ouvrier), sauf si une autre valeur > 0 est fournie.
        $grade_id = (isset($data['grade_id']) && $data['grade_id'] > 0) ? $data['grade_id'] : 4; 
        
        // Récupérer le mot de passe (s'assurer qu'il existe)
        $password = isset($data['password']) ? $data['password'] : '';
        
        // Hachage du mot de passe (essentiel)
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (nom, prenom, numero_telephone, departement, grade_id, password_hash) 
                VALUES (:nom, :prenom, :numero_telephone, :departement, :grade_id, :password_hash)";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(array(
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'numero_telephone' => $data['numero_telephone'],
                'departement' => $data['departement'],
                'grade_id' => $grade_id,
                'password_hash' => $password_hash
            ));
        } catch (PDOException $e) {
            // 23000 est souvent le code pour violation de contrainte unique (ex: numero_telephone déjà utilisé)
            if ($e->getCode() == '23000') { 
                return false; 
            } 
            // Pour le débogage, vous pouvez décommenter la ligne suivante:
            // die("Erreur SQL lors de l'enregistrement : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Tente de connecter un utilisateur.
     * @param string $numero_telephone
     * @param string $password
     * @return array|bool Les données de l'utilisateur ou false.
     */
    public function loginUser($numero_telephone, $password) {
        $sql = "SELECT u.*, g.nom_grade FROM users u JOIN grades g ON u.grade_id = g.id 
                WHERE u.numero_telephone = :numero_telephone AND u.est_actif = TRUE";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array('numero_telephone' => $numero_telephone));
        $user = $stmt->fetch();

        // 1. Vérifie si l'utilisateur existe ET 2. Vérifie le mot de passe haché
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return false;
    }
    
    /**
     * Récupère tous les utilisateurs (pour le TdB Admin).
     * @param string $searchTerm Terme de recherche optionnel.
     * @return array
     */
    public function getAllUsers($searchTerm = '') { 
        $sql = "SELECT u.id, u.nom, u.prenom, u.numero_telephone, u.departement, g.nom_grade
                FROM users u JOIN grades g ON u.grade_id = g.id WHERE u.est_actif = TRUE ";
        $params = array();
        if (!empty($searchTerm)) {
            $sql .= " AND (u.nom LIKE :search OR u.prenom LIKE :search OR u.departement LIKE :search) ";
            $params['search'] = '%' . $searchTerm . '%';
        }
        $sql .= " ORDER BY g.nom_grade, u.nom";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère les utilisateurs d'un certain grade dans un département (pour le TdB Responsable).
     * @param string $departement
     * @param int $gradeId (par défaut 4 = Ouvrier)
     * @return array
     */
    public function getUsersByDepartment($departement, $gradeId = 4) {
        $sql = "SELECT u.id, u.nom, u.prenom, u.numero_telephone
                FROM users u 
                WHERE u.departement = :departement AND u.grade_id = :grade_id AND u.est_actif = TRUE
                ORDER BY u.nom, u.prenom";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array('departement' => $departement, 'grade_id' => $gradeId));
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère les utilisateurs par grade (pour le TdB Coordination).
     * @param int $gradeId
     * @return array
     */
    public function getUsersByGrade($gradeId) {
        $sql = "SELECT id, nom, prenom, departement FROM users WHERE grade_id = :grade_id AND est_actif = TRUE ORDER BY nom, prenom";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array('grade_id' => $gradeId));
        return $stmt->fetchAll();
    }

    /**
     * Récupère les données d'un utilisateur par son ID.
     * @param int $userId
     * @return array|bool
     */
    public function getUserById($userId) {
        $sql = "SELECT u.*, g.nom_grade FROM users u JOIN grades g ON u.grade_id = g.id WHERE u.id = :userId AND u.est_actif = TRUE";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array('userId' => $userId));
        return $stmt->fetch();
    }
    
    /**
     * Récupère les données d'un utilisateur par son numéro de téléphone.
     * Utilisé pour le débogage de la connexion.
     * @param string $numero_telephone
     * @return array|bool
     */
    public function getUserByIdentifiant($numero_telephone) {
        $sql = "SELECT u.*, g.nom_grade FROM users u JOIN grades g ON u.grade_id = g.id WHERE u.numero_telephone = :numero_telephone";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array('numero_telephone' => $numero_telephone));
        return $stmt->fetch();
    }
    
    /**
     * Vérifie si un ouvrier appartient au département d'un responsable donné.
     * Utilisé par Pointage.class.php
     * @param int $ouvrierId
     * @param int $responsableId
     * @return bool
     */
    public function checkOuvrierInResponsableDepartment($ouvrierId, $responsableId) {
        // 1. Récupérer le département du Responsable
        $sqlResp = "SELECT departement FROM users WHERE id = :responsableId";
        $stmtResp = $this->db->prepare($sqlResp);
        $stmtResp->execute(array('responsableId' => $responsableId));
        $responsable = $stmtResp->fetch();
        
        if (!$responsable) { return false; }

        // 2. Vérifier si l'Ouvrier est dans ce même département
        $sqlOuvrier = "SELECT id FROM users WHERE id = :ouvrierId AND departement = :departement";
        $stmtOuvrier = $this->db->prepare($sqlOuvrier);
        $stmtOuvrier->execute(array('ouvrierId' => $ouvrierId, 'departement' => $responsable['departement']));

        return $stmtOuvrier->rowCount() > 0;
    }
}
?>
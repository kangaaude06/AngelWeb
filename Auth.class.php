<?php
// Auth.class.php
require_once 'Database.class.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getPdo();
    }

    /**
     * Vérifie le mot de passe (hashé ou en clair)
     */
    private function verifyPassword($password, $hash) {
        // Si le hash commence par $2y$ ou $2a$, c'est un hash bcrypt
        if (strpos($hash, '$2y$') === 0 || strpos($hash, '$2a$') === 0) {
            return password_verify($password, $hash);
        }
        // Sinon, comparaison directe (mot de passe en clair)
        return $password === $hash;
    }

    /**
     * Connexion pour administrateur
     */
    public function loginAdmin($email, $password) {
        $sql = "SELECT * FROM administrateur WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $admin = $stmt->fetch();

        if ($admin && $this->verifyPassword($password, $admin['mot_de_passe'])) {
            $_SESSION['user_type'] = 'admin';
            $_SESSION['user_id'] = $admin['id_admin'];
            $_SESSION['user_name'] = $admin['nom'] . ' ' . $admin['prenom'];
            return true;
        }
        return false;
    }

    /**
     * Connexion pour coordination
     */
    public function loginCoordination($email, $password) {
        $sql = "SELECT * FROM coordination WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $coord = $stmt->fetch();

        if ($coord && $this->verifyPassword($password, $coord['mot_de_passe'])) {
            $_SESSION['user_type'] = 'coordination';
            $_SESSION['user_id'] = $coord['id_coordination'];
            $_SESSION['user_name'] = $coord['nom'] . ' ' . $coord['prenom'];
            $_SESSION['departement'] = $coord['departement'];
            return true;
        }
        return false;
    }

    /**
     * Connexion pour responsable
     */
    public function loginResponsable($email, $password) {
        $sql = "SELECT * FROM responsable WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $resp = $stmt->fetch();

        if ($resp && $this->verifyPassword($password, $resp['mot_de_passe'])) {
            $_SESSION['user_type'] = 'responsable';
            $_SESSION['user_id'] = $resp['id_responsable'];
            $_SESSION['user_name'] = $resp['nom'] . ' ' . $resp['prenom'];
            // Ne pas définir le département automatiquement, laisser l'utilisateur choisir
            // Le département sera défini lors de la sélection
            return true;
        }
        return false;
    }

    /**
     * Connexion pour ouvrier
     */
    public function loginOuvrier($email, $password) {
        $sql = "SELECT * FROM ouvrier WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $ouvrier = $stmt->fetch();

        if ($ouvrier && $this->verifyPassword($password, $ouvrier['mot_de_passe'])) {
            $_SESSION['user_type'] = 'ouvrier';
            $_SESSION['user_id'] = $ouvrier['id_ouvrier'];
            $_SESSION['user_name'] = $ouvrier['nom'] . ' ' . $ouvrier['prenom'];
            $_SESSION['departement'] = $ouvrier['departement'];
            return true;
        }
        return false;
    }

    /**
     * Vérifie si l'utilisateur est connecté
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_type']) && isset($_SESSION['user_id']);
    }

    /**
     * Vérifie le type d'utilisateur
     */
    public static function requireRole($role) {
        if (!self::isLoggedIn() || $_SESSION['user_type'] !== $role) {
            header('Location: login.php');
            exit;
        }
    }

    /**
     * Déconnexion
     */
    public function logout() {
        session_destroy();
        header('Location: index.php');
        exit;
    }
}
?>


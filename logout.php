<?php
// logout.php
// Nous incluons config pour nous assurer que la session est démarrée avant de la détruire
require_once 'config.php'; 

// Détruire toutes les variables de session
$_SESSION = array();

// Si vous utilisez des cookies de session, détruisez également le cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion
header("Location: login.php");
exit;
?>
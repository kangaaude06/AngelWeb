<?php

// Paramètres de connexion à la base de données (WampServer par défaut)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // Utilisateur par défaut de Wamp
define('DB_PASS', '');          // Mot de passe par défaut de Wamp (vide)
define('DB_NAME', 'angels_web'); // << REMPLACEZ CECI PAR LE NOM EXACT DE VOTRE BASE DE DONNÉES

try {
    // Établir la connexion PDO
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Arrête le script ete l'erreur en cas d'échec de connexion
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

?>
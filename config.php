<?php
// config.php

// --- LIGNES POUR DÉBOGAGE (Affichez les erreurs PHP) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Constantes de connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_personnel');
define('DB_USER', 'root');   // <-- CORRIGÉ : Utilisateur MySQL par défaut
define('DB_PASS', '');       // <-- CORRIGÉ : Mot de passe vide par défaut

// Constantes métier
define('HEURE_LIMITE_POINTAGE', '18:30:00'); 
define('HEURE_DEBUT_TRAVAIL', '08:00:00'); 

// Démarrage de la session (Assure une seule session_start() au chargement)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
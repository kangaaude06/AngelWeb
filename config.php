<?php
// config.php

// Constantes de connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_personnel');
define('DB_USER', 'root'); // Remplacez par votre utilisateur
define('DB_PASS', '');     // Remplacez par votre mot de passe

// Constantes métier
define('HEURE_LIMITE_POINTAGE', '18:30:00'); 
define('HEURE_DEBUT_TRAVAIL', '08:00:00'); 

// Démarrage de la session
session_start();
?>
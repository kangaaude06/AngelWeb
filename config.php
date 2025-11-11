<?php
// config.php

// Configuration de la Base de Données
define('DB_HOST', 'localhost');
define('DB_NAME', 'angels'); // Base de données Angels
define('DB_USER', 'root');             // Mettez votre utilisateur MySQL
define('DB_PASS', ''); // Mettez votre mot de passe MySQL (vide par défaut sur WAMP)

// Contrainte de temps
define('HEURE_LIMITE_POINTAGE', '18:30:00'); 
define('HEURE_DEBUT_TRAVAIL', '08:00:00'); // Pour le calcul des retards

// Démarre la session pour gérer la connexion des utilisateurs
session_start();
?>
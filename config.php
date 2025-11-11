<?php
// config.php

// Configuration de la Base de Données
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_personnel'); 
define('DB_USER', 'root');             
define('DB_PASS', 'votre_mot_de_passe'); 

// Contraintes de temps
define('HEURE_LIMITE_POINTAGE', '18:30:00'); 
define('HEURE_DEBUT_TRAVAIL', '08:00:00'); 

// Démarre la session
session_start();
?>
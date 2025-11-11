<?php
// config.php

// Configuration de la Base de Données
define('DB_HOST', 'localhost');
<<<<<<< HEAD
define('DB_NAME', 'gestion_personnel'); 
define('DB_USER', 'root');             
define('DB_PASS', 'votre_mot_de_passe'); 
=======
define('DB_NAME', 'angels'); // Base de données Angels
define('DB_USER', 'root');             // Mettez votre utilisateur MySQL
define('DB_PASS', ''); // Mettez votre mot de passe MySQL (vide par défaut sur WAMP)
>>>>>>> 9908589319a8248a0a604cef3955a579ded8a2cb

// Contraintes de temps
define('HEURE_LIMITE_POINTAGE', '18:30:00'); 
define('HEURE_DEBUT_TRAVAIL', '08:00:00'); 

// Démarre la session
session_start();
?>
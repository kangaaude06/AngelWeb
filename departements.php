<?php
// departements.php - Liste des départements disponibles

define('DEPARTEMENTS', [
    'accueil',
    'administration',
    'royals angels',
    'troupe artistique',
    'chantres',
    'kedesh',
    'kodesh',
    'logistique',
    'protocole',
    'annonce',
    'communication',
    'commandos'
]);

/**
 * Retourne la liste des départements
 */
function getDepartements() {
    return DEPARTEMENTS;
}

/**
 * Vérifie si un département est valide
 */
function isDepartementValide($departement) {
    return in_array(strtolower($departement), array_map('strtolower', DEPARTEMENTS));
}

/**
 * Retourne le département avec la bonne casse
 */
function normaliserDepartement($departement) {
    foreach (DEPARTEMENTS as $dep) {
        if (strtolower($dep) === strtolower($departement)) {
            return $dep;
        }
    }
    return null;
}
?>


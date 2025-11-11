# Instructions pour la gestion des départements multiples

## Création des tables de liaison

Pour permettre aux ouvriers et responsables d'avoir jusqu'à 2 départements, vous devez exécuter le script de création des tables.

### Méthode 1 : Via le navigateur
1. Accédez à : `http://localhost/AngelWeb/create_tables_departements.php`
2. Le script créera automatiquement les tables nécessaires et migrera les données existantes

### Méthode 2 : Via phpMyAdmin
Exécutez les requêtes SQL suivantes dans phpMyAdmin :

```sql
-- Table de liaison pour les départements des ouvriers
CREATE TABLE IF NOT EXISTS ouvrier_departement (
    id_ouvrier INT(11) NOT NULL,
    departement VARCHAR(100) NOT NULL,
    PRIMARY KEY (id_ouvrier, departement),
    KEY idx_ouvrier (id_ouvrier),
    KEY idx_departement (departement)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Table de liaison pour les départements des responsables
CREATE TABLE IF NOT EXISTS responsable_departement (
    id_responsable INT(11) NOT NULL,
    departement VARCHAR(100) NOT NULL,
    PRIMARY KEY (id_responsable, departement),
    KEY idx_responsable (id_responsable),
    KEY idx_departement (departement)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
```

## Migration des données existantes

Après la création des tables, migrez les données existantes :

```sql
-- Migrer les départements des ouvriers
INSERT IGNORE INTO ouvrier_departement (id_ouvrier, departement)
SELECT id_ouvrier, departement FROM ouvrier WHERE departement IS NOT NULL AND departement != '';

-- Migrer les départements des responsables
INSERT IGNORE INTO responsable_departement (id_responsable, departement)
SELECT id_responsable, departement FROM responsable WHERE departement IS NOT NULL AND departement != '';
```

## Fonctionnalités

- **Responsables** : Peuvent avoir jusqu'à 2 départements maximum
- **Ouvriers** : Peuvent avoir jusqu'à 2 départements maximum
- **Département actif** : Le responsable peut choisir quel département utiliser pour le pointage
- **Gestion** : Interface dédiée pour ajouter/supprimer/changer de département

## Utilisation

1. **Pour les responsables** :
   - Après connexion, redirection vers la page de sélection des départements
   - Ajoutez jusqu'à 2 départements
   - Choisissez le département actif pour le pointage
   - Changez de département actif depuis la page de pointage ou de gestion

2. **Pour les ouvriers** :
   - Les départements sont gérés par l'administration
   - Un ouvrier peut appartenir à 2 départements maximum

## Départements disponibles

- Accueil
- Administration
- Royals Angels
- Troupe Artistique
- Chantres
- Kedesh
- Kodesh
- Logistique
- Protocole
- Annonce
- Communication
- Commandos


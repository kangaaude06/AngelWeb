-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mar. 11 nov. 2025 à 00:46
-- Version du serveur : 5.7.40
-- Version de PHP : 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `angels`
--

-- --------------------------------------------------------

--
-- Structure de la table `administrateur`
--

DROP TABLE IF EXISTS `administrateur`;
CREATE TABLE IF NOT EXISTS `administrateur` (
  `id_admin` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  PRIMARY KEY (`id_admin`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `administrateur`
--

INSERT INTO `administrateur` (`id_admin`, `nom`, `prenom`, `email`, `mot_de_passe`) VALUES
(1, 'Admin', 'Root', 'admin@eglise.com', 'motdepasse123');

-- --------------------------------------------------------

--
-- Structure de la table `coordination`
--

DROP TABLE IF EXISTS `coordination`;
CREATE TABLE IF NOT EXISTS `coordination` (
  `id_coordination` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `departement` varchar(100) NOT NULL,
  PRIMARY KEY (`id_coordination`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

DROP TABLE IF EXISTS `notification`;
CREATE TABLE IF NOT EXISTS `notification` (
  `id_notification` int(11) NOT NULL AUTO_INCREMENT,
  `contenu` text NOT NULL,
  `date_envoi` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_coordination` int(11) NOT NULL,
  `type_notification` enum('message','alerte','rappel') DEFAULT 'message',
  PRIMARY KEY (`id_notification`),
  KEY `id_coordination` (`id_coordination`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `notification_ouvrier`
--

DROP TABLE IF EXISTS `notification_ouvrier`;
CREATE TABLE IF NOT EXISTS `notification_ouvrier` (
  `id_notification` int(11) NOT NULL,
  `id_ouvrier` int(11) NOT NULL,
  PRIMARY KEY (`id_notification`,`id_ouvrier`),
  KEY `id_ouvrier` (`id_ouvrier`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `notification_responsable`
--

DROP TABLE IF EXISTS `notification_responsable`;
CREATE TABLE IF NOT EXISTS `notification_responsable` (
  `id_notification` int(11) NOT NULL,
  `id_responsable` int(11) NOT NULL,
  PRIMARY KEY (`id_notification`,`id_responsable`),
  KEY `id_responsable` (`id_responsable`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `ouvrier`
--

DROP TABLE IF EXISTS `ouvrier`;
CREATE TABLE IF NOT EXISTS `ouvrier` (
  `id_ouvrier` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `departement` varchar(100) NOT NULL,
  `date_inscription` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_responsable` int(11) NOT NULL,
  PRIMARY KEY (`id_ouvrier`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_ouvrier_departement` (`departement`),
  KEY `idx_ouvrier_responsable` (`id_responsable`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `pointage_ouvrier`
--

DROP TABLE IF EXISTS `pointage_ouvrier`;
CREATE TABLE IF NOT EXISTS `pointage_ouvrier` (
  `id_pointage` int(11) NOT NULL AUTO_INCREMENT,
  `id_ouvrier` int(11) NOT NULL,
  `id_responsable` int(11) NOT NULL,
  `date_heure_pointage` datetime NOT NULL,
  `type_pointage` enum('entrée','sortie') NOT NULL,
  `statut` enum('présent','absent','retard') NOT NULL,
  PRIMARY KEY (`id_pointage`),
  KEY `id_responsable` (`id_responsable`),
  KEY `idx_pointage_ouvrier_date` (`id_ouvrier`,`date_heure_pointage`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `pointage_responsable`
--

DROP TABLE IF EXISTS `pointage_responsable`;
CREATE TABLE IF NOT EXISTS `pointage_responsable` (
  `id_pointage_resp` int(11) NOT NULL AUTO_INCREMENT,
  `id_responsable` int(11) NOT NULL,
  `id_coordination` int(11) NOT NULL,
  `date_heure_pointage` datetime NOT NULL,
  `type_pointage` enum('entrée','sortie') NOT NULL,
  `statut` enum('présent','absent','retard') NOT NULL,
  PRIMARY KEY (`id_pointage_resp`),
  KEY `id_coordination` (`id_coordination`),
  KEY `idx_pointage_resp_date` (`id_responsable`,`date_heure_pointage`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `responsable`
--

DROP TABLE IF EXISTS `responsable`;
CREATE TABLE IF NOT EXISTS `responsable` (
  `id_responsable` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `departement` varchar(100) NOT NULL,
  `id_coordination` int(11) NOT NULL,
  PRIMARY KEY (`id_responsable`),
  UNIQUE KEY `email` (`email`),
  KEY `id_coordination` (`id_coordination`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `tache`
--

DROP TABLE IF EXISTS `tache`;
CREATE TABLE IF NOT EXISTS `tache` (
  `id_tache` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) NOT NULL,
  `description` text,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime DEFAULT NULL,
  `statut` enum('à faire','en cours','terminée') DEFAULT 'à faire',
  `id_coordination` int(11) NOT NULL,
  `id_ouvrier` int(11) NOT NULL,
  PRIMARY KEY (`id_tache`),
  KEY `idx_tache_ouvrier` (`id_ouvrier`),
  KEY `idx_tache_coordination` (`id_coordination`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

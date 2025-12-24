-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 22, 2025 at 01:57 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wertani`
--

-- --------------------------------------------------------

--
-- Table structure for table `article`
--

CREATE TABLE `article` (
  `Id` int(11) NOT NULL,
  `Name` varchar(200) NOT NULL,
  `Description` text DEFAULT NULL,
  `Quantity` int(11) DEFAULT 0,
  `PurchasePrice` decimal(10,3) DEFAULT 0.000,
  `SellingPrice` decimal(10,3) DEFAULT 0.000,
  `CreationDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `article_ws`
--

CREATE TABLE `article_ws` (
  `Id` int(11) NOT NULL,
  `Reference` varchar(100) DEFAULT NULL,
  `Name` varchar(200) NOT NULL,
  `Description` text DEFAULT NULL,
  `Quantity` int(11) DEFAULT 0,
  `PrixVente` decimal(10,3) DEFAULT 0.000,
  `CreationDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `article_ws_photos`
--

CREATE TABLE `article_ws_photos` (
  `Id` int(11) NOT NULL,
  `ArticleWS_Id` int(11) NOT NULL,
  `PhotoPath` varchar(500) NOT NULL,
  `PhotoName` varchar(255) NOT NULL,
  `IsMain` tinyint(1) DEFAULT 0 COMMENT '1 if this is the main photo to show in dashboard',
  `PhotoType` enum('voiture','piece') DEFAULT 'voiture' COMMENT 'Type de photo: voiture ou pièce',
  `VoitureDossier` varchar(100) DEFAULT NULL COMMENT 'Nom du dossier pour les photos de voitures (ex: Mercedes 2020)',
  `Description` text DEFAULT NULL,
  `CreationDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `Id` int(11) NOT NULL,
  `Marque` varchar(100) NOT NULL COMMENT 'Brand (Toyota, Nissan, etc.)',
  `Modele` varchar(100) NOT NULL COMMENT 'Model (Hilux, Patrol, etc.)',
  `Annee` int(4) DEFAULT NULL COMMENT 'Year',
  `Type` enum('4x4','SUV','Pickup','Autre') DEFAULT '4x4',
  `IsActive` tinyint(1) DEFAULT 1 COMMENT '1=Active, 0=Archived',
  `CreationDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`Id`, `Marque`, `Modele`, `Annee`, `Type`, `IsActive`, `CreationDate`) VALUES
(1, 'Toyota', 'Hilux', 2024, '4x4', 1, '2025-12-22 12:56:47'),
(2, 'Toyota', 'Land Cruiser', 2024, '4x4', 1, '2025-12-22 12:56:47'),
(3, 'Toyota', 'Fortuner', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(4, 'Toyota', 'Prado', 2024, '4x4', 1, '2025-12-22 12:56:47'),
(5, 'Toyota', 'RAV4', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(6, 'Nissan', 'Patrol', 2024, '4x4', 1, '2025-12-22 12:56:47'),
(7, 'Nissan', 'Navara', 2024, 'Pickup', 1, '2025-12-22 12:56:47'),
(8, 'Nissan', 'Pathfinder', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(9, 'Nissan', 'X-Trail', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(10, 'Mitsubishi', 'L200', 2024, 'Pickup', 1, '2025-12-22 12:56:47'),
(11, 'Mitsubishi', 'Pajero', 2024, '4x4', 1, '2025-12-22 12:56:47'),
(12, 'Mitsubishi', 'Outlander', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(13, 'Ford', 'Ranger', 2024, 'Pickup', 1, '2025-12-22 12:56:47'),
(14, 'Ford', 'Ranger Raptor', 2024, 'Pickup', 1, '2025-12-22 12:56:47'),
(15, 'Ford', 'Everest', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(16, 'Isuzu', 'D-Max', 2024, 'Pickup', 1, '2025-12-22 12:56:47'),
(17, 'Isuzu', 'MU-X', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(18, 'Jeep', 'Wrangler', 2024, '4x4', 1, '2025-12-22 12:56:47'),
(19, 'Jeep', 'Grand Cherokee', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(20, 'Jeep', 'Compass', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(21, 'Land Rover', 'Defender', 2024, '4x4', 1, '2025-12-22 12:56:47'),
(22, 'Land Rover', 'Discovery', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(23, 'Land Rover', 'Range Rover', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(24, 'Volkswagen', 'Amarok', 2024, 'Pickup', 1, '2025-12-22 12:56:47'),
(25, 'Volkswagen', 'Tiguan', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(26, 'Mazda', 'BT-50', 2024, 'Pickup', 1, '2025-12-22 12:56:47'),
(27, 'Mazda', 'CX-5', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(28, 'Mazda', 'CX-9', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(29, 'Suzuki', 'Jimny', 2024, '4x4', 1, '2025-12-22 12:56:47'),
(30, 'Suzuki', 'Vitara', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(31, 'Hyundai', 'Tucson', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(32, 'Hyundai', 'Santa Fe', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(33, 'Kia', 'Sportage', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(34, 'Kia', 'Sorento', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(35, 'Chevrolet', 'Colorado', 2024, 'Pickup', 1, '2025-12-22 12:56:47'),
(36, 'Chevrolet', 'Tahoe', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(37, 'Mercedes-Benz', 'Classe G', 2024, '4x4', 1, '2025-12-22 12:56:47'),
(38, 'Mercedes-Benz', 'GLE', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(39, 'BMW', 'X5', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(40, 'BMW', 'X7', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(41, 'Renault', 'Duster', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(42, 'Renault', 'Alaskan', 2024, 'Pickup', 1, '2025-12-22 12:56:47'),
(43, 'Peugeot', '3008', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(44, 'Peugeot', '5008', 2024, 'SUV', 1, '2025-12-22 12:56:47'),
(0, 'nissab', 'patrol', 2025, '4x4', 1, '2025-12-22 12:57:17');

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `Id` int(11) NOT NULL,
  `Name` varchar(200) NOT NULL,
  `PhoneNumber` varchar(20) NOT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Type` varchar(20) DEFAULT 'Normal' COMMENT 'Normal or Societe',
  `FiscalMatricule` varchar(50) DEFAULT NULL COMMENT 'Matricule fiscal (pour Société uniquement)',
  `Address` text DEFAULT NULL,
  `City` varchar(100) DEFAULT NULL,
  `Notes` text DEFAULT NULL,
  `CreationDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `composant_matiere`
--

CREATE TABLE `composant_matiere` (
  `Id` int(11) NOT NULL,
  `Name` varchar(200) NOT NULL,
  `Reference` varchar(100) DEFAULT NULL,
  `Quantity` int(11) DEFAULT 0,
  `Description` text DEFAULT NULL,
  `Fournisseur_Id` int(11) DEFAULT NULL,
  `MontantTTC` decimal(10,3) DEFAULT 0.000,
  `Categorie` varchar(100) DEFAULT NULL COMMENT 'électrique, métallique, bois, tissu, Accessoire',
  `CreationDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `Id` int(11) NOT NULL,
  `Matricule` varchar(50) DEFAULT NULL COMMENT 'Numéro matricule employé',
  `Nom` varchar(100) NOT NULL,
  `Prenom` varchar(100) NOT NULL,
  `CIN` varchar(20) DEFAULT NULL COMMENT 'Carte d''identité nationale',
  `DateNaissance` date DEFAULT NULL,
  `Telephone` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Adresse` text DEFAULT NULL,
  `Poste` varchar(100) DEFAULT NULL COMMENT 'Titre du poste',
  `DateEmbauche` date DEFAULT NULL COMMENT 'Date d''embauche',
  `Salaire` decimal(10,3) DEFAULT 0.000 COMMENT 'Salaire mensuel',
  `TypeContrat` enum('CDI','CDD','Temporaire','Stage') DEFAULT 'CDI' COMMENT 'Type de contrat',
  `Statut` enum('Actif','Inactif','Suspendu') DEFAULT 'Actif',
  `NumCNSS` varchar(50) DEFAULT NULL COMMENT 'Numéro CNSS',
  `RIB` varchar(50) DEFAULT NULL COMMENT 'Relevé d''identité bancaire',
  `Photo` varchar(255) DEFAULT NULL COMMENT 'Photo de l''employé',
  `Notes` text DEFAULT NULL,
  `CreationDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdateDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Gestion des employés';

-- --------------------------------------------------------

--
-- Table structure for table `employee_absences`
--

CREATE TABLE `employee_absences` (
  `Id` int(11) NOT NULL,
  `Employee_Id` int(11) NOT NULL,
  `DateDebut` date NOT NULL,
  `DateFin` date NOT NULL,
  `NombreJours` int(11) DEFAULT 1,
  `TypeAbsence` enum('Congé Payé','Congé Maladie','Absence Autorisée','Absence Non Autorisée','Autre') DEFAULT 'Congé Payé',
  `Motif` text DEFAULT NULL,
  `Justificatif` varchar(255) DEFAULT NULL COMMENT 'Fichier justificatif',
  `Statut` enum('En Attente','Approuvé','Refusé') DEFAULT 'En Attente',
  `Notes` text DEFAULT NULL,
  `CreationDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Absences des employés';

-- --------------------------------------------------------

--
-- Table structure for table `employee_attendance`
--

CREATE TABLE `employee_attendance` (
  `Id` int(11) NOT NULL,
  `Employee_Id` int(11) NOT NULL,
  `DatePointage` date NOT NULL,
  `HeureArrivee` time DEFAULT NULL,
  `HeureDepart` time DEFAULT NULL,
  `HeuresTravaillees` decimal(5,2) DEFAULT 0.00,
  `Statut` enum('Présent','Absent','Retard','Congé') DEFAULT 'Présent',
  `Notes` text DEFAULT NULL,
  `CreationDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Pointage et présence';

-- --------------------------------------------------------

--
-- Table structure for table `employee_documents`
--

CREATE TABLE `employee_documents` (
  `Id` int(11) NOT NULL,
  `Employee_Id` int(11) NOT NULL,
  `TypeDocument` varchar(100) DEFAULT NULL COMMENT 'Type de document',
  `NomDocument` varchar(255) NOT NULL,
  `CheminFichier` varchar(255) NOT NULL,
  `DateAjout` date DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `CreationDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Documents des employés';

-- --------------------------------------------------------

--
-- Table structure for table `employee_salaires`
--

CREATE TABLE `employee_salaires` (
  `Id` int(11) NOT NULL,
  `Employee_Id` int(11) NOT NULL,
  `Mois` int(11) NOT NULL COMMENT 'Mois (1-12)',
  `Annee` int(11) NOT NULL,
  `SalaireBase` decimal(10,3) DEFAULT 0.000,
  `Primes` decimal(10,3) DEFAULT 0.000 COMMENT 'Primes et bonus',
  `Deductions` decimal(10,3) DEFAULT 0.000 COMMENT 'Déductions (retards, absences)',
  `Avance` decimal(10,3) DEFAULT 0.000 COMMENT 'Avance sur salaire (argent déjà donné)',
  `SalaireNet` decimal(10,3) DEFAULT 0.000 COMMENT 'Salaire net à payer (Base + Primes - Deductions - Avance)',
  `HeuresTravaillees` decimal(10,2) DEFAULT 0.00,
  `HeuresSupplementaires` decimal(10,2) DEFAULT 0.00,
  `DatePaiement` date DEFAULT NULL,
  `StatutPaiement` enum('En Attente','Payé','Annulé') DEFAULT 'En Attente',
  `ModePaiement` enum('Espèces','Virement','Chèque') DEFAULT 'Virement',
  `Notes` text DEFAULT NULL,
  `CreationDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Historique des salaires';

-- --------------------------------------------------------

--
-- Table structure for table `journal_depenses`
--

CREATE TABLE `journal_depenses` (
  `Id` int(11) NOT NULL,
  `DateAchat` date NOT NULL COMMENT 'Purchase date',
  `NumDoc` varchar(100) NOT NULL COMMENT 'Document number',
  `Fournisseur_Id` int(11) DEFAULT NULL,
  `TypeDoc` varchar(50) NOT NULL COMMENT 'Bon de livraison or Facture',
  `BonLivraisonGroup` varchar(100) DEFAULT NULL COMMENT 'Group ID for bon de livraison',
  `FactureParent_Id` int(11) DEFAULT NULL COMMENT 'Parent facture if this is a bon grouped',
  `CptsCharger` varchar(50) NOT NULL COMMENT 'Wertani Saber or Wertani Service',
  `TauxTVA` decimal(5,2) DEFAULT 0.00 COMMENT 'TVA percentage',
  `TVA` decimal(10,3) DEFAULT 0.000 COMMENT 'TVA amount calculated',
  `MontantHT` decimal(10,3) DEFAULT 0.000 COMMENT 'Amount before tax',
  `MontantTTC` decimal(10,3) NOT NULL COMMENT 'Total amount including tax',
  `Etat` varchar(20) DEFAULT 'Non Payee' COMMENT 'Non Payee or Payee',
  `PayeeLe` date DEFAULT NULL COMMENT 'Payment date',
  `TypePaiement` varchar(50) DEFAULT NULL COMMENT 'Espèce, Virement, Chèque, Payer par Saber',
  `NumVirement` varchar(100) DEFAULT NULL,
  `NumCheque` varchar(100) DEFAULT NULL,
  `Comptabiliser` tinyint(1) DEFAULT 0 COMMENT 'Include in financial reports',
  `Entite` varchar(50) DEFAULT 'Wertani Services' COMMENT 'Wertani Services ou Wertani Saber',
  `Notes` text DEFAULT NULL,
  `CreationDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `journal_depenses`
--
DELIMITER $$
CREATE TRIGGER `before_depense_insert` BEFORE INSERT ON `journal_depenses` FOR EACH ROW BEGIN
    IF NEW.TauxTVA > 0 THEN
        SET NEW.MontantHT = NEW.MontantTTC / (1 + (NEW.TauxTVA / 100));
        SET NEW.TVA = NEW.MontantTTC - NEW.MontantHT;
    ELSE
        SET NEW.MontantHT = NEW.MontantTTC;
        SET NEW.TVA = 0;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_depense_update` BEFORE UPDATE ON `journal_depenses` FOR EACH ROW BEGIN
    IF NEW.TauxTVA > 0 THEN
        SET NEW.MontantHT = NEW.MontantTTC / (1 + (NEW.TauxTVA / 100));
        SET NEW.TVA = NEW.MontantTTC - NEW.MontantHT;
    ELSE
        SET NEW.MontantHT = NEW.MontantTTC;
        SET NEW.TVA = 0;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE `project` (
  `Id` int(11) NOT NULL,
  `ProjectNumber` varchar(50) DEFAULT NULL,
  `Client_Id` int(11) DEFAULT NULL,
  `CarId` int(11) DEFAULT NULL COMMENT 'Reference to cars table',
  `Voiture` varchar(200) DEFAULT NULL COMMENT 'Vehicle model (legacy/fallback)',
  `Matricule` varchar(50) DEFAULT NULL COMMENT 'License plate',
  `ProjectType_Id` int(11) NOT NULL DEFAULT 1,
  `Title` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL,
  `TotalAmount` decimal(10,3) DEFAULT 0.000,
  `FinalAmount` decimal(10,3) DEFAULT 0.000,
  `PaymentStatus` varchar(50) DEFAULT 'En attente',
  `Notes` text DEFAULT NULL,
  `CreationDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `project`
--
DELIMITER $$
CREATE TRIGGER `before_project_insert` BEFORE INSERT ON `project` FOR EACH ROW BEGIN
    IF NEW.ProjectNumber IS NULL THEN
        SET NEW.ProjectNumber = CONCAT('PRJ-', YEAR(NOW()), '-', LPAD((SELECT COALESCE(MAX(CAST(SUBSTRING(ProjectNumber, 10) AS UNSIGNED)), 0) + 1 FROM Project WHERE ProjectNumber LIKE CONCAT('PRJ-', YEAR(NOW()), '-%')), 3, '0'));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `projectarticles`
--

CREATE TABLE `projectarticles` (
  `Id` int(11) NOT NULL,
  `Project_Id` int(11) NOT NULL,
  `Article_Id` int(11) NOT NULL,
  `Quantity` int(11) DEFAULT 1,
  `UnitPrice` decimal(10,3) DEFAULT 0.000,
  `TotalPrice` decimal(10,3) DEFAULT 0.000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projecttype`
--

CREATE TABLE `projecttype` (
  `Id` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `Id` int(11) NOT NULL,
  `Name` varchar(200) NOT NULL,
  `Description` text DEFAULT NULL,
  `PrixService` decimal(10,3) DEFAULT 0.000,
  `CreationDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_articles`
--

CREATE TABLE `service_articles` (
  `Id` int(11) NOT NULL,
  `Service_Id` int(11) NOT NULL,
  `ArticleType` varchar(50) NOT NULL COMMENT 'article_ws, composant_matiere',
  `Article_Id` int(11) NOT NULL COMMENT 'ID from respective article table',
  `Quantity` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sous_article_ws`
--

CREATE TABLE `sous_article_ws` (
  `Id` int(11) NOT NULL,
  `ArticleWS_Id` int(11) NOT NULL,
  `Name` varchar(200) NOT NULL,
  `Reference` varchar(100) DEFAULT NULL,
  `Quantity` int(11) DEFAULT 0,
  `Description` text DEFAULT NULL,
  `Matiere` varchar(200) DEFAULT NULL COMMENT 'Material type',
  `FilePath` varchar(500) DEFAULT NULL COMMENT 'Path to uploaded file',
  `FileName` varchar(255) DEFAULT NULL COMMENT 'Original filename',
  `CreationDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suivi_projet`
--

CREATE TABLE `suivi_projet` (
  `Id` int(11) NOT NULL,
  `Project_Id` int(11) NOT NULL,
  `NumSuivi` varchar(50) DEFAULT NULL,
  `Client_Id` int(11) DEFAULT NULL,
  `Voiture` varchar(200) DEFAULT NULL,
  `Matricule` varchar(50) DEFAULT NULL,
  `Title` varchar(200) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `MontantProjet` decimal(10,3) DEFAULT 0.000,
  `Avance` decimal(10,3) DEFAULT 0.000,
  `CptsCharger` varchar(50) NOT NULL DEFAULT 'Wertani Service',
  `AppliqueTVA` tinyint(1) DEFAULT 0,
  `TauxTVA` decimal(5,2) DEFAULT 19.00,
  `TVA` decimal(10,3) DEFAULT 0.000,
  `MontantTTC` decimal(10,3) DEFAULT 0.000,
  `ResteAPayer` decimal(10,3) DEFAULT 0.000,
  `Etat` varchar(20) DEFAULT 'Non Payee',
  `PayeeLe` date DEFAULT NULL,
  `TypePaiement` varchar(50) DEFAULT NULL,
  `NumVirement` varchar(100) DEFAULT NULL,
  `NumCheque` varchar(100) DEFAULT NULL,
  `Comptabiliser` tinyint(1) DEFAULT 0,
  `Entite` varchar(50) DEFAULT 'Wertani Services' COMMENT 'Wertani Services ou Wertani Saber',
  `StatutProjet` varchar(50) DEFAULT 'En cours',
  `DateDebut` date DEFAULT NULL,
  `DateFin` date DEFAULT NULL,
  `Notes` text DEFAULT NULL,
  `CreationDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `suivi_projet`
--
DELIMITER $$
CREATE TRIGGER `before_suivi_insert` BEFORE INSERT ON `suivi_projet` FOR EACH ROW BEGIN
    IF NEW.NumSuivi IS NULL THEN
        SET NEW.NumSuivi = CONCAT('SV-', YEAR(NOW()), '-', LPAD((SELECT COALESCE(MAX(CAST(SUBSTRING(NumSuivi, 9) AS UNSIGNED)), 0) + 1 FROM suivi_projet WHERE NumSuivi LIKE CONCAT('SV-', YEAR(NOW()), '-%')), 3, '0'));
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_suivi_insert_calc` BEFORE INSERT ON `suivi_projet` FOR EACH ROW BEGIN
    IF NEW.AppliqueTVA = 1 THEN
        SET NEW.TVA = NEW.MontantProjet * (NEW.TauxTVA / 100);
        SET NEW.MontantTTC = NEW.MontantProjet + NEW.TVA;
    ELSE
        SET NEW.TVA = 0;
        SET NEW.MontantTTC = NEW.MontantProjet;
    END IF;
    SET NEW.ResteAPayer = NEW.MontantTTC - NEW.Avance;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_suivi_update_calc` BEFORE UPDATE ON `suivi_projet` FOR EACH ROW BEGIN
    IF NEW.AppliqueTVA = 1 THEN
        SET NEW.TVA = NEW.MontantProjet * (NEW.TauxTVA / 100);
        SET NEW.MontantTTC = NEW.MontantProjet + NEW.TVA;
    ELSE
        SET NEW.TVA = 0;
        SET NEW.MontantTTC = NEW.MontantProjet;
    END IF;
    SET NEW.ResteAPayer = NEW.MontantTTC - NEW.Avance;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `suivi_projet_articles`
--

CREATE TABLE `suivi_projet_articles` (
  `Id` int(11) NOT NULL,
  `SuiviProjet_Id` int(11) NOT NULL,
  `ArticleType` varchar(50) NOT NULL,
  `Article_Id` int(11) NOT NULL,
  `Quantity` int(11) DEFAULT 1,
  `UnitPrice` decimal(10,3) DEFAULT 0.000,
  `TotalPrice` decimal(10,3) DEFAULT 0.000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `matricule_fiscale` varchar(50) DEFAULT NULL COMMENT 'Numéro de matricule fiscale du fournisseur',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `created_at`, `is_active`) VALUES
(1, 'admin', '$2y$10$sSa2dyXJBLg0OSG28jX9UuNCP9gYwWfpNQYsRgtkUPvxQZ1cLX76e', 'admin@wertaniservice.tn', '2025-12-13 15:00:00', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`Id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

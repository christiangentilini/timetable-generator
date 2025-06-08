-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 31.11.39.211
-- Creato il: Giu 08, 2025 alle 13:42
-- Versione del server: 8.0.41-32
-- Versione PHP: 8.0.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `Sql1855798_1`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `changelog`
--

CREATE TABLE `changelog` (
  `date` date NOT NULL,
  `version` varchar(15) NOT NULL,
  `title` varchar(50) NOT NULL,
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `changelog`
--

INSERT INTO `changelog` (`date`, `version`, `title`, `id`) VALUES
('2025-04-02', '1.0', 'Benvenuto Timetable Generator!', 12);

-- --------------------------------------------------------

--
-- Struttura della tabella `changelog_data`
--

CREATE TABLE `changelog_data` (
  `id` int NOT NULL,
  `version_id` int NOT NULL,
  `item` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `changelog_data`
--

INSERT INTO `changelog_data` (`id`, `version_id`, `item`) VALUES
(10, 12, 'Implementazione del sistema di gestione cronologici'),
(11, 12, 'Interfaccia utente moderna e intuitiva con Bootstrap 5'),
(12, 12, 'Sistema di autenticazione e gestione utenti'),
(13, 12, 'Gestione dei ruoli (admin e utenti standard)'),
(14, 12, 'Creazione e modifica dei cronologici'),
(15, 12, 'Caricamento e gestione dei loghi personalizzati'),
(16, 12, 'Sistema di duplicazione dei cronologici'),
(17, 12, 'Generazione PDF dei cronologici'),
(18, 12, 'Ordinamento e organizzazione dei dettagli'),
(19, 12, 'Registrazione e login utenti'),
(20, 12, 'Profili utente personalizzabili'),
(21, 12, 'Gestione password sicura'),
(22, 12, 'Pannello amministrativo per la gestione utenti'),
(23, 12, 'Design responsive per tutti i dispositivi'),
(24, 12, 'Tema moderno con Bootstrap Icons'),
(25, 12, 'Footer fluttuante con informazioni di sistema'),
(26, 12, 'Navigazione intuitiva con menu dropdown'),
(27, 12, 'Implementazione delle policy sulla privacy'),
(28, 12, 'Termini e condizioni d\'uso'),
(29, 12, 'Cookie policy'),
(30, 12, 'Protezione delle sessioni utente'),
(31, 12, 'Pannello di controllo admin'),
(32, 12, 'Gestione completa degli utenti'),
(33, 12, 'Monitoraggio delle attività'),
(34, 12, 'Sistema di changelog integrato');

-- --------------------------------------------------------

--
-- Struttura della tabella `definizioni`
--

CREATE TABLE `definizioni` (
  `id` int NOT NULL,
  `definition` varchar(100) NOT NULL,
  `definition_parent` enum('disciplina','categoria','classe','tipo','turno','logo','linea_descrittiva') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `definizioni`
--

INSERT INTO `definizioni` (`id`, `definition`, `definition_parent`, `image_path`, `created_at`) VALUES
(1, 'Finale', 'turno', '', '2025-03-19 10:47:06'),
(2, 'Semifinale', 'turno', '', '2025-03-19 10:50:08'),
(3, 'Elimin.', 'turno', '', '2025-03-19 11:05:16'),
(5, 'FIDESM', 'logo', 'assets/logos/logo_1742407408_67db06f0e0da8.png', '2025-03-19 18:03:28'),
(6, 'Danze Latine', 'disciplina', NULL, '2025-03-19 20:26:41'),
(7, 'Solo', 'tipo', NULL, '2025-03-19 20:30:53'),
(8, 'Solo Femminile', 'tipo', NULL, '2025-03-19 20:31:00'),
(9, 'Solo Maschile', 'tipo', NULL, '2025-03-19 20:31:08'),
(10, 'Formation', 'tipo', NULL, '2025-03-19 20:39:48'),
(11, 'Crew', 'tipo', NULL, '2025-03-19 20:39:48'),
(12, 'Duo', 'tipo', NULL, '2025-03-19 20:39:48'),
(13, 'Coppie', 'tipo', NULL, '2025-03-19 20:39:48'),
(16, 'Combi', 'tipo', NULL, '2025-03-19 20:39:48'),
(17, 'Gruppo Danza', 'tipo', NULL, '2025-03-19 20:39:48'),
(18, 'Piccolo Gruppo', 'tipo', NULL, '2025-03-19 20:39:48'),
(19, 'Formazione', 'tipo', NULL, '2025-03-19 20:39:48'),
(20, 'Bboy', 'tipo', NULL, '2025-03-19 20:39:48'),
(22, 'Gruppo Para', 'tipo', NULL, '2025-03-19 20:39:48'),
(23, 'Gruppo Mix', 'tipo', NULL, '2025-03-19 20:39:48'),
(24, 'Gruppo', 'tipo', NULL, '2025-03-19 20:39:48'),
(25, 'Bgirl', 'tipo', NULL, '2025-03-19 20:39:48'),
(26, 'Production', 'tipo', NULL, '2025-03-19 20:39:48'),
(27, 'Duo Mix', 'tipo', NULL, '2025-03-19 20:39:48'),
(28, 'Duo Femminile', 'tipo', NULL, '2025-03-19 20:39:48'),
(29, 'Duo Maschile', 'tipo', NULL, '2025-03-19 20:39:48'),
(30, 'Trio', 'tipo', NULL, '2025-03-19 20:39:48'),
(31, 'Hip Hop', 'disciplina', NULL, '2025-03-19 20:42:50'),
(32, 'Latin Style Show', 'disciplina', NULL, '2025-03-19 20:42:50'),
(33, 'Danze Standard', 'disciplina', NULL, '2025-03-19 20:42:50'),
(34, 'Danze Latine', 'disciplina', NULL, '2025-03-19 20:42:50'),
(35, 'Danze Caraibiche', 'disciplina', NULL, '2025-03-19 20:42:50'),
(36, 'Combinata ST e LA', 'disciplina', NULL, '2025-03-19 20:42:50'),
(37, 'Salsa On2', 'disciplina', NULL, '2025-03-19 20:42:50'),
(38, 'Salsa Cuban Style', 'disciplina', NULL, '2025-03-19 20:42:50'),
(39, 'Merengue', 'disciplina', NULL, '2025-03-19 20:42:50'),
(40, 'Bachata', 'disciplina', NULL, '2025-03-19 20:42:50'),
(41, 'Cheerleading', 'disciplina', NULL, '2025-03-19 20:42:50'),
(42, 'Line Dance', 'disciplina', NULL, '2025-03-19 20:42:50'),
(43, 'Freestyle', 'disciplina', NULL, '2025-03-19 20:42:50'),
(44, 'Show Dance', 'disciplina', NULL, '2025-03-19 20:42:50'),
(45, 'Oriental Dance', 'disciplina', NULL, '2025-03-19 20:42:50'),
(46, 'Rueda', 'disciplina', NULL, '2025-03-19 20:42:50'),
(47, 'Folk Oriental Dance', 'disciplina', NULL, '2025-03-19 20:42:50'),
(48, 'Breaking', 'disciplina', NULL, '2025-03-19 20:42:50'),
(49, 'Combinata Danze Argentine', 'disciplina', NULL, '2025-03-19 20:42:50'),
(50, 'Conventional', 'disciplina', NULL, '2025-03-19 20:42:50'),
(51, 'Disco Show', 'disciplina', NULL, '2025-03-19 20:42:50'),
(52, 'Disco Dance Freestyle (Acrobatica)', 'disciplina', NULL, '2025-03-19 20:42:50'),
(53, 'Disco Dance', 'disciplina', NULL, '2025-03-19 20:42:50'),
(54, 'Street Dance Show', 'disciplina', NULL, '2025-03-19 20:42:50'),
(55, 'Electric Boogie/Popping', 'disciplina', NULL, '2025-03-19 20:42:50'),
(56, 'Hip Hop Battle', 'disciplina', NULL, '2025-03-19 20:42:50'),
(57, 'Jazz Dance', 'disciplina', NULL, '2025-03-19 20:42:50'),
(58, 'Modern Contemporary', 'disciplina', NULL, '2025-03-19 20:42:50'),
(59, 'Danza Classica', 'disciplina', NULL, '2025-03-19 20:42:50'),
(60, 'Free Style Battle', 'disciplina', NULL, '2025-03-19 20:42:50'),
(61, 'Free Style Show', 'disciplina', NULL, '2025-03-19 20:42:50'),
(62, 'Free Style Coreografico', 'disciplina', NULL, '2025-03-19 20:42:50'),
(63, 'Free Style Sincro', 'disciplina', NULL, '2025-03-19 20:42:50'),
(64, 'Free Style Tecnica', 'disciplina', NULL, '2025-03-19 20:42:50'),
(65, 'Latin Style C. JI', 'disciplina', NULL, '2025-03-19 20:42:50'),
(66, 'Latin Style C. PD', 'disciplina', NULL, '2025-03-19 20:42:50'),
(67, 'Latin Style C. RU', 'disciplina', NULL, '2025-03-19 20:42:50'),
(68, 'Latin Style C. CCC', 'disciplina', NULL, '2025-03-19 20:42:50'),
(69, 'Latin Style C. SA', 'disciplina', NULL, '2025-03-19 20:42:50'),
(70, 'Latin Style C.', 'disciplina', NULL, '2025-03-19 20:42:50'),
(71, 'Latin Style S. JI', 'disciplina', NULL, '2025-03-19 20:42:50'),
(72, 'Latin Style S. PD', 'disciplina', NULL, '2025-03-19 20:42:50'),
(73, 'Latin Style S. RU', 'disciplina', NULL, '2025-03-19 20:42:50'),
(74, 'Latin Style S. CCC', 'disciplina', NULL, '2025-03-19 20:42:50'),
(75, 'Latin Style S. SA', 'disciplina', NULL, '2025-03-19 20:42:50'),
(76, 'Latin Style S.', 'disciplina', NULL, '2025-03-19 20:42:50'),
(77, 'Latin Style T. JI', 'disciplina', NULL, '2025-03-19 20:42:50'),
(78, 'Latin Style T. PD', 'disciplina', NULL, '2025-03-19 20:42:50'),
(79, 'Latin Style T. RU', 'disciplina', NULL, '2025-03-19 20:42:50'),
(80, 'Latin Style T. CCC', 'disciplina', NULL, '2025-03-19 20:42:50'),
(81, 'Latin Style T. SA', 'disciplina', NULL, '2025-03-19 20:42:50'),
(82, 'Danze Afro Latine', 'disciplina', NULL, '2025-03-19 20:42:50'),
(83, 'Country Show Dance', 'disciplina', NULL, '2025-03-19 20:42:50'),
(84, 'Country Dance', 'disciplina', NULL, '2025-03-19 20:42:50'),
(85, 'Country Synchro', 'disciplina', NULL, '2025-03-19 20:42:50'),
(86, 'Couple Dance', 'disciplina', NULL, '2025-03-19 20:42:50'),
(87, 'Flamenco', 'disciplina', NULL, '2025-03-19 20:42:50'),
(88, 'Tap Dance', 'disciplina', NULL, '2025-03-19 20:42:50'),
(89, 'Oriental Show Dance', 'disciplina', NULL, '2025-03-19 20:42:50'),
(90, 'Danze Filuzziane', 'disciplina', NULL, '2025-03-19 20:42:50'),
(91, 'Due Fruste', 'disciplina', NULL, '2025-03-19 20:42:50'),
(92, 'Frusta Romagnola', 'disciplina', NULL, '2025-03-19 20:42:50'),
(93, 'Segue Folk', 'disciplina', NULL, '2025-03-19 20:42:50'),
(94, 'Folk Show Dance', 'disciplina', NULL, '2025-03-19 20:42:50'),
(95, 'Folk Romagnolo', 'disciplina', NULL, '2025-03-19 20:42:50'),
(96, 'Liscio Tradizionale', 'disciplina', NULL, '2025-03-19 20:42:50'),
(97, 'Combinata Nazionale', 'disciplina', NULL, '2025-03-19 20:42:50'),
(98, 'Ballo da Sala', 'disciplina', NULL, '2025-03-19 20:42:50'),
(99, 'Liscio Unificato', 'disciplina', NULL, '2025-03-19 20:42:50'),
(100, 'Tango Escenario', 'disciplina', NULL, '2025-03-19 20:42:50'),
(101, 'Tango Salòn', 'disciplina', NULL, '2025-03-19 20:42:50'),
(102, 'Hustle Disco Fox', 'disciplina', NULL, '2025-03-19 20:42:50'),
(103, 'Bachata Shine', 'disciplina', NULL, '2025-03-19 20:42:50'),
(104, 'Salsa Shine', 'disciplina', NULL, '2025-03-19 20:42:50'),
(105, 'Car. Show Dance', 'disciplina', NULL, '2025-03-19 20:42:50'),
(106, 'Boogie Woogie', 'disciplina', NULL, '2025-03-19 20:42:50'),
(107, 'Rock Ladies', 'disciplina', NULL, '2025-03-19 20:42:50'),
(108, 'Rock', 'disciplina', NULL, '2025-03-19 20:42:50'),
(109, 'Rock couple dance', 'disciplina', NULL, '2025-03-19 20:42:50'),
(110, 'Rock free style', 'disciplina', NULL, '2025-03-19 20:42:50'),
(111, 'Rock contact style', 'disciplina', NULL, '2025-03-19 20:42:50'),
(112, 'Rock Acrobatico', 'disciplina', NULL, '2025-03-19 20:42:50'),
(113, 'Rock Tecnico', 'disciplina', NULL, '2025-03-19 20:42:50'),
(114, 'Rock and Roll', 'disciplina', NULL, '2025-03-19 20:42:50'),
(115, 'Show Freestyle Standard', 'disciplina', NULL, '2025-03-19 20:42:50'),
(116, 'Show Freestyle Latin', 'disciplina', NULL, '2025-03-19 20:42:50'),
(117, 'Latin Show', 'disciplina', NULL, '2025-03-19 20:43:47'),
(120, 'Over 31', 'categoria', NULL, '2025-03-19 21:03:08'),
(121, '19-34', 'categoria', NULL, '2025-03-19 21:03:08'),
(122, 'Under 21', 'categoria', NULL, '2025-03-19 21:03:08'),
(123, 'Over 45', 'categoria', NULL, '2025-03-19 21:03:08'),
(124, '35-44', 'categoria', NULL, '2025-03-19 21:03:08'),
(125, '18-34', 'categoria', NULL, '2025-03-19 21:03:08'),
(126, '18-30', 'categoria', NULL, '2025-03-19 21:03:08'),
(127, '8-17', 'categoria', NULL, '2025-03-19 21:03:08'),
(128, 'Open', 'categoria', NULL, '2025-03-19 21:03:08'),
(129, 'Over 46', 'categoria', NULL, '2025-03-19 21:03:08'),
(130, '31-45', 'categoria', NULL, '2025-03-19 21:03:08'),
(131, '17-30', 'categoria', NULL, '2025-03-19 21:03:08'),
(132, '13-16', 'categoria', NULL, '2025-03-19 21:03:08'),
(133, '8-12', 'categoria', NULL, '2025-03-19 21:03:08'),
(134, 'Over 13', 'categoria', NULL, '2025-03-19 21:03:08'),
(135, 'Under 12', 'categoria', NULL, '2025-03-19 21:03:08'),
(136, 'Over 18', 'categoria', NULL, '2025-03-19 21:03:08'),
(137, 'Over 17', 'categoria', NULL, '2025-03-19 21:03:08'),
(138, 'Over 16', 'categoria', NULL, '2025-03-19 21:03:08'),
(139, 'Under 15', 'categoria', NULL, '2025-03-19 21:03:08'),
(140, '15-16', 'categoria', NULL, '2025-03-19 21:03:08'),
(141, 'Under 16', 'categoria', NULL, '2025-03-19 21:03:08'),
(142, '13-14', 'categoria', NULL, '2025-03-19 21:03:08'),
(143, 'Over 35', 'categoria', NULL, '2025-03-19 21:03:08'),
(144, '10-12', 'categoria', NULL, '2025-03-19 21:03:08'),
(145, '8-9', 'categoria', NULL, '2025-03-19 21:03:08'),
(146, 'Over 19', 'categoria', NULL, '2025-03-19 21:03:08'),
(147, '16-18', 'categoria', NULL, '2025-03-19 21:03:08'),
(148, '12-15', 'categoria', NULL, '2025-03-19 21:03:08'),
(149, '8-11', 'categoria', NULL, '2025-03-19 21:03:08'),
(150, 'Under 17', 'categoria', NULL, '2025-03-19 21:03:08'),
(151, 'Over 50', 'categoria', NULL, '2025-03-19 21:03:08'),
(152, 'Over 55', 'categoria', NULL, '2025-03-19 21:03:08'),
(153, '45-54', 'categoria', NULL, '2025-03-19 21:03:08'),
(154, 'Over 40', 'categoria', NULL, '2025-03-19 21:03:08'),
(155, '14-15', 'categoria', NULL, '2025-03-19 21:03:08'),
(156, '12-13', 'categoria', NULL, '2025-03-19 21:03:08'),
(157, '10-11', 'categoria', NULL, '2025-03-19 21:03:08'),
(158, 'Over 70', 'categoria', NULL, '2025-03-19 21:03:08'),
(159, '65-6', 'categoria', NULL, '2025-03-19 21:03:08'),
(160, '61-64', 'categoria', NULL, '2025-03-19 21:03:08'),
(161, '55-60', 'categoria', NULL, '2025-03-19 21:03:08'),
(162, 'Over 61', 'categoria', NULL, '2025-03-19 21:03:08'),
(163, '16-34', 'categoria', NULL, '2025-03-19 21:03:08'),
(164, 'Over 56', 'categoria', NULL, '2025-03-19 21:03:08'),
(165, 'Under 11', 'categoria', NULL, '2025-03-19 21:03:08'),
(166, 'Adulti', 'categoria', NULL, '2025-03-19 21:03:08'),
(167, '19-27', 'categoria', NULL, '2025-03-19 21:03:08'),
(168, 'Over 15', 'categoria', NULL, '2025-03-19 21:03:08'),
(169, 'Over 14', 'categoria', NULL, '2025-03-19 21:03:08'),
(170, 'Over 65', 'categoria', NULL, '2025-03-19 21:03:08'),
(171, 'Over 75', 'categoria', NULL, '2025-03-19 21:03:08'),
(172, '70-74', 'categoria', NULL, '2025-03-19 21:03:08'),
(173, 'C', 'classe', NULL, '2025-03-19 21:04:51'),
(174, 'U', 'classe', NULL, '2025-03-19 21:04:51'),
(175, 'AS', 'classe', NULL, '2025-03-19 21:04:51'),
(176, 'A', 'classe', NULL, '2025-03-19 21:04:51'),
(177, 'A2', 'classe', NULL, '2025-03-19 21:04:51'),
(178, 'A1', 'classe', NULL, '2025-03-19 21:04:51'),
(179, 'B3', 'classe', NULL, '2025-03-19 21:04:51'),
(180, 'B2', 'classe', NULL, '2025-03-19 21:04:51'),
(181, 'B1', 'classe', NULL, '2025-03-19 21:04:51'),
(182, 'B', 'classe', NULL, '2025-03-19 21:04:51'),
(183, 'D', 'classe', NULL, '2025-03-19 21:04:51'),
(184, 'FIDA ITALIA', 'logo', 'assets/logos/logo_1743536420_67ec4124c1db1.jpeg', '2025-04-01 19:40:20');

-- --------------------------------------------------------

--
-- Struttura della tabella `timetables`
--

CREATE TABLE `timetables` (
  `id` int NOT NULL,
  `user_created` int NOT NULL,
  `titolo` varchar(255) NOT NULL,
  `sottotitolo` varchar(255) NOT NULL,
  `desc1` varchar(255) NOT NULL,
  `desc2` varchar(255) NOT NULL,
  `disclaimer` varchar(900) NOT NULL,
  `logo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `timetables`
--

INSERT INTO `timetables` (`id`, `user_created`, `titolo`, `sottotitolo`, `desc1`, `desc2`, `disclaimer`, `logo`) VALUES
(1, 2, 'Campionato Regionale FVG 2025 FIDESM', 'Sabato 15 marzo 2025', 'Palazzetto Bella Italia EIFA Village', 'Lignano Sabbiadoro', 'NOTA: l\'ordine delle competizioni, così come gli orari di seguito riportati, potrebbero subire variazioni fino a 60 minuti.\nGli atleti sono tenuti ad accreditarsi almeno 1 ora prima dell\'inizio della competizione loro riservata.', 'assets/logos/logo_1742407408_67db06f0e0da8.png'),
(2, 2, '1^ Trofeo Arte Danza Lab', 'Palazzetto dello Sport di Pozzoleone (VI)', 'Palazzetto dello Sport di Pozzoleone (VI)', 'Domenica 23 marzo 2025', 'NOTA: l\'ordine delle competizioni, così come gli orari di seguito riportati, potrebbero subire variazioni fino a 60 minuti.\r\nGli atleti sono tenuti ad accreditarsi almento 1 ora prima dell\'inizio della competizione loro riservata.', 'assets/logos/logo_1742407408_67db06f0e0da8.png');

-- --------------------------------------------------------

--
-- Struttura della tabella `timetable_details`
--

CREATE TABLE `timetable_details` (
  `id` int NOT NULL,
  `timetable_id` int NOT NULL,
  `entry_type` enum('normal','descriptive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_number` int DEFAULT NULL,
  `time_slot` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discipline` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `class_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `turn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `da` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `a` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `balli` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `batterie` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pannello` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `timetable_details`
--

INSERT INTO `timetable_details` (`id`, `timetable_id`, `entry_type`, `order_number`, `time_slot`, `discipline`, `category`, `class_name`, `type`, `turn`, `da`, `a`, `balli`, `batterie`, `pannello`, `description`, `created_at`) VALUES
(1, 1, 'normal', 1, '14:54', 'Prova', 'Prova', 'Prova', 'Solo', '1° Turno Finale', '24', '12', '3', '2', 'B', NULL, '2025-03-14 13:54:25'),
(2, 1, 'normal', 2, '15:00', 'Prova2', 'Prova2', 'Prova2', 'Solo', '1° Turno Finale', '24', '12', '3', '3', 'B', NULL, '2025-03-14 14:01:08'),
(3, 1, 'normal', 5, '15:10', 'prova3', 'prova3', 'prova3', 'Solo', '1° Turno Finale', '24', '12', '3', '3', 'B', NULL, '2025-03-14 14:11:16'),
(5, 1, 'normal', 4, '16:40', 'prova4', 'prova4', 'prova4', 'Solo', '1° Turno Finale', '1', '1', '1', '1', 'B', NULL, '2025-03-14 15:40:48'),
(6, 1, 'descriptive', 3, '16:41', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'Premiazioni2', '2025-03-14 15:40:57'),
(9, 1, 'descriptive', 7, '22:55', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'fsds', '2025-03-16 21:56:01'),
(10, 1, 'descriptive', 8, '22:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'jikkjkjkj', '2025-03-16 21:57:24'),
(11, 1, 'descriptive', 9, '12:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'dsdsd', '2025-03-16 22:03:09'),
(12, 1, 'descriptive', 10, '23:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'dsdsd', '2025-03-16 22:08:29'),
(14, 1, 'descriptive', 11, '11:47', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fdfd', '2025-03-17 10:47:20'),
(15, 1, 'normal', 12, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 11:17:28'),
(16, 1, 'normal', 38, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:23'),
(17, 1, 'normal', 20, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:23'),
(18, 1, 'normal', 19, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:23'),
(19, 1, 'normal', 18, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:23'),
(20, 1, 'normal', 17, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:24'),
(21, 1, 'normal', 16, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:24'),
(22, 1, 'normal', 15, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:24'),
(23, 1, 'normal', 14, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:24'),
(24, 1, 'normal', 13, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:24'),
(25, 1, 'normal', 34, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:26'),
(26, 1, 'normal', 33, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:26'),
(27, 1, 'normal', 29, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:26'),
(28, 1, 'normal', 28, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:26'),
(29, 1, 'normal', 26, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:26'),
(30, 1, 'normal', 27, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:27'),
(31, 1, 'normal', 25, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:27'),
(32, 1, 'normal', 24, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:27'),
(33, 1, 'normal', 23, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:27'),
(34, 1, 'normal', 22, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:27'),
(35, 1, 'normal', 21, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:28'),
(36, 1, 'normal', 37, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:29'),
(37, 1, 'normal', 36, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:29'),
(38, 1, 'normal', 35, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:29'),
(39, 1, 'normal', 32, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:30'),
(40, 1, 'normal', 31, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:30'),
(41, 1, 'normal', 30, '12:17', 'aa', 'aa', 'aa', 'Solo', '1° Turno Finale', '11', '11', '11', '11', 'B', NULL, '2025-03-17 21:20:30'),
(42, 1, 'normal', 6, '15:10', 'prova3', 'prova3', 'prova3', 'Solo', '1° Turno Finale', '24', '12', '3', '3', 'B', NULL, '2025-03-17 21:44:14'),
(44, 2, 'descriptive', 1, '09:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Presentazione Staff Tecnico e Giudicante', '2025-03-19 20:11:22'),
(46, 2, 'descriptive', 2, '10:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Inizio Competizione', '2025-03-19 20:11:33'),
(47, 2, 'normal', 4, '10:04', 'Latin Show', 'Over 35', 'U', 'Gruppo Danza', '1° Finale', '1', '1', '1', '1', 'A', NULL, '2025-03-19 20:14:28'),
(71, 2, 'normal', 5, '10:08', 'Latin Style Coreo', 'Under 12', 'U', 'Piccolo Gruppo', '1° Finale', '1', '1', '1', '1', 'A', NULL, '2025-03-21 14:57:07'),
(72, 2, 'normal', 6, '10:12', 'Latin Style Coreo', 'Under 16', 'U', 'Piccolo Gruppo', '1° Finale', '1', '1', '1', '1', 'A', NULL, '2025-03-21 15:06:34'),
(73, 2, 'normal', 7, '10:16', 'Show Dance', 'Under 12', 'U', 'Piccolo Gruppo', '1° Finale', '2', '1', '1', '1', 'A', NULL, '2025-03-21 15:07:03'),
(74, 2, 'normal', 8, '10:24', 'Show Dance', 'Under 16', 'U', 'Piccolo Gruppo', '1° Finale', '2', '1', '1', '1', 'A', NULL, '2025-03-21 15:07:34'),
(75, 2, 'normal', 9, '10:32', 'Show Dance', 'Under 16', 'U', 'Gruppo Danza', '1° Finale', '1', '1', '1', '1', 'A', NULL, '2025-03-21 15:07:58'),
(76, 2, 'normal', 10, '10:36', 'Show Dance', 'Over 16', 'U', 'Gruppo Danza', '1° Finale', '3', '1', '1', '1', 'A', NULL, '2025-03-21 15:08:47'),
(77, 2, 'normal', 11, '10:50', 'Car. Show Dance', 'Over 35', 'U', 'Piccolo Gruppo', '1° Finale', '1', '1', '1', '1', 'A', NULL, '2025-03-21 15:10:20'),
(78, 2, 'normal', 12, '10:54', 'Show Dance', 'Under 15', 'U', 'Solo', '1° Finale', '1', '1', '1', '1', 'A', NULL, '2025-03-21 15:11:57'),
(79, 2, 'normal', 14, '11:02', 'Car. Show Dance', 'Over 35', 'U', 'Solo', '1° Finale', '2', '1', '1', '1', 'A', NULL, '2025-03-21 15:13:32'),
(80, 2, 'normal', 13, '10:58', 'Car. Show Dance', 'Over 16', 'U', 'Solo', '1° Finale', '1', '1', '1', '1', 'A', NULL, '2025-03-21 15:13:32'),
(81, 2, 'normal', 15, '11:06', 'Show Dance', 'Under 15', 'U', 'Duo', '1° Finale', '1', '1', '1', '1', 'A', NULL, '2025-03-21 15:15:56'),
(82, 2, 'descriptive', 16, '11:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Premiazioni', '2025-03-21 15:17:23'),
(83, 2, 'normal', 17, '16:22', 'Latin Style T. CCC', '10-11', 'A', 'Bboy', '1° Finale', '1', '1', '1', '1', 'A', NULL, '2025-03-21 15:23:03');

-- --------------------------------------------------------

--
-- Struttura della tabella `timetable_shares`
--

CREATE TABLE `timetable_shares` (
  `id` int NOT NULL,
  `timetable_id` int NOT NULL,
  `user_id` int NOT NULL,
  `permission_level` enum('view','edit') NOT NULL DEFAULT 'view',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `timetable_shares`
--

INSERT INTO `timetable_shares` (`id`, `timetable_id`, `user_id`, `permission_level`, `created_at`) VALUES
(2, 2, 3, 'view', '2025-03-25 22:51:37');

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_path` varchar(255) DEFAULT NULL,
  `nome` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `cognome` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `type` varchar(255) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `temp_password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `profile_path`, `nome`, `cognome`, `type`, `last_login`, `temp_password`) VALUES
(2, 'christian', 'christian.gentilini@outlook.com', '$2y$10$wO9aBx7rcbD6l2i/cl.S7.vXcEZpwdKDF//L2dUBZ07LRR2.NAUse', 'src/users/christian/logo/christian-profile.jpg', 'Christian', 'Gentilini', 'admin', '2025-04-04 10:55:00', NULL),
(3, 'chri2', 'chri.genti@gmail.com', '$2y$10$BFvg0hwLXrLFPZg5qpi4RuuWdon5d1czNMHbB/PGNlMytQeyBMZ/G', NULL, 'Christian', 'Secondario', 'user', '2025-03-26 00:10:22', NULL),
(4, 'chri3', 'chri@prova.it', '$2y$10$W4hU..qpjRYAtDKBfDYzVOTfbuvII8wJmuU5EQ5XIS5zxX2BQXQRi', NULL, 'Prova', 'Prova', 'admin', '2025-03-18 16:30:33', NULL),
(7, 'prova15', 'prova@prova.com', '$2y$10$j1hMEEd0/9/lE1WjuS9FrO6e03yigBJhCXR.Mo8ShLBWggArSpn.6', NULL, 'prova', 'prova', 'user', '2025-03-18 16:28:35', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `user_last_version`
--

CREATE TABLE `user_last_version` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `last_version_seen` varchar(20) NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `user_last_version`
--

INSERT INTO `user_last_version` (`id`, `user_id`, `last_version_seen`, `updated_at`) VALUES
(1, 7, '1.1', '2025-03-18 15:28:35'),
(2, 4, '1.1', '2025-03-18 15:30:33'),
(3, 2, '1.1', '2025-03-18 15:31:37');

-- --------------------------------------------------------

--
-- Struttura della tabella `timetable_config`
--

CREATE TABLE `timetable_config` (
  `id` int NOT NULL AUTO_INCREMENT,
  `timetable_id` int NOT NULL,
  `ora_inizio` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ora_apertura` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `timetable_id` (`timetable_id`),
  CONSTRAINT `timetable_config_ibfk_1` FOREIGN KEY (`timetable_id`) REFERENCES `timetables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `changelog`
--
ALTER TABLE `changelog`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `changelog_data`
--
ALTER TABLE `changelog_data`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `definizioni`
--
ALTER TABLE `definizioni`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `timetables`
--
ALTER TABLE `timetables`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `timetable_details`
--
ALTER TABLE `timetable_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `timetable_id` (`timetable_id`);

--
-- Indici per le tabelle `timetable_shares`
--
ALTER TABLE `timetable_shares`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_share` (`timetable_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `user_last_version`
--
ALTER TABLE `user_last_version`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user` (`user_id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `changelog`
--
ALTER TABLE `changelog`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT per la tabella `changelog_data`
--
ALTER TABLE `changelog_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT per la tabella `definizioni`
--
ALTER TABLE `definizioni`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=185;

--
-- AUTO_INCREMENT per la tabella `timetables`
--
ALTER TABLE `timetables`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `timetable_details`
--
ALTER TABLE `timetable_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT per la tabella `timetable_shares`
--
ALTER TABLE `timetable_shares`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT per la tabella `user_last_version`
--
ALTER TABLE `user_last_version`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `timetable_details`
--
ALTER TABLE `timetable_details`
  ADD CONSTRAINT `timetable_details_ibfk_1` FOREIGN KEY (`timetable_id`) REFERENCES `timetables` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `timetable_shares`
--
ALTER TABLE `timetable_shares`
  ADD CONSTRAINT `timetable_shares_ibfk_1` FOREIGN KEY (`timetable_id`) REFERENCES `timetables` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `timetable_shares_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `user_last_version`
--
ALTER TABLE `user_last_version`
  ADD CONSTRAINT `user_last_version_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

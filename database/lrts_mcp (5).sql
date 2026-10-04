-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 11, 2026 at 01:57 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 7.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lrts_mcp`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcement`
--

CREATE TABLE `announcement` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `audience` varchar(50) NOT NULL DEFAULT 'all',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `colleges`
--

CREATE TABLE `colleges` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `colleges`
--

INSERT INTO `colleges` (`id`, `code`, `name`, `created_at`, `updated_at`) VALUES
(1, 'BRC', 'Biomedical Research Center', NULL, NULL),
(2, 'CAM', 'College of Advanced Materials', NULL, NULL),
(3, 'CAS', 'College of Arts and Sciences', NULL, NULL),
(4, 'CBE', 'College of Business and Education', NULL, NULL),
(5, 'CDM', 'College of Dental Medicine', NULL, NULL),
(6, 'CED', 'College of Education', NULL, NULL),
(7, 'CENG', 'College of Engineering', NULL, NULL),
(8, 'CHS', 'College of Health Sciences', NULL, NULL),
(9, 'CLAW', 'College of Law', NULL, NULL),
(10, 'CLU', 'Central Laboratories Unit', NULL, NULL),
(11, 'CMED', 'College of Medicine', NULL, NULL),
(12, 'CPH', 'College of Pharmacy', NULL, NULL),
(13, 'CSIS', 'College of Sharia and Islamic Studies', NULL, NULL),
(14, 'ESC', 'Environmental Science Center', NULL, NULL),
(15, 'IK-CHSS', 'Ibn Khaldon Center for Humanities & Social Sciences', NULL, NULL),
(16, 'LARC', 'Laboratory of Animal Research Center', NULL, NULL),
(17, 'SESRI', 'Social and Economic Survey Research Institute', NULL, NULL),
(18, 'YSC', 'Youth Service Center', NULL, NULL),
(19, 'GS', 'General Studies', NULL, NULL),
(20, 'ARS', 'Agriculture Research Station', NULL, NULL),
(21, 'QUH', 'QU Health', NULL, NULL),
(22, 'CNRS', 'College of Nursing', NULL, NULL),
(23, 'CSS', 'College of Sports Sciences', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `commitments`
--

CREATE TABLE `commitments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `q1article` int(11) NOT NULL,
  `q2article` int(11) NOT NULL,
  `q3article` int(11) NOT NULL,
  `q4article` int(11) NOT NULL,
  `confArticle` int(11) NOT NULL,
  `books` int(11) NOT NULL,
  `editBooks` int(11) NOT NULL,
  `chapters` int(11) NOT NULL,
  `ip` int(11) NOT NULL,
  `filedPatent` int(11) NOT NULL,
  `openSourceSW` int(11) NOT NULL,
  `startUp` tinyint(1) NOT NULL,
  `ethical` tinyint(1) NOT NULL DEFAULT 0,
  `master` int(11) NOT NULL,
  `UG` int(11) NOT NULL,
  `Phd` int(11) NOT NULL,
  `crossCollege` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `commitments`
--

INSERT INTO `commitments` (`id`, `project_id`, `q1article`, `q2article`, `q3article`, `q4article`, `confArticle`, `books`, `editBooks`, `chapters`, `ip`, `filedPatent`, `openSourceSW`, `startUp`, `ethical`, `master`, `UG`, `Phd`, `crossCollege`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1, 1, 0, 0, 0, 0, 1, 0, 1, 0, 1, 1, 0, 1, 0, 1, '2026-08-03 06:24:38', '2026-08-03 06:24:38');

-- --------------------------------------------------------

--
-- Table structure for table `cycle_configs`
--

CREATE TABLE `cycle_configs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `year` year(4) NOT NULL,
  `title` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cycle_configs`
--

INSERT INTO `cycle_configs` (`id`, `year`, `title`, `created_at`, `updated_at`) VALUES
(1, 2019, 'Cycle-2019', '2026-07-05 02:44:53', '2026-07-05 02:46:05'),
(2, 2020, 'Cycle-2020', '2026-07-05 02:46:20', '2026-07-05 02:46:20'),
(3, 2021, 'Cycle-2021', '2026-07-05 02:46:35', '2026-07-05 02:46:35'),
(4, 2022, 'Cycle-2022', '2026-07-05 02:47:11', '2026-07-05 02:47:11'),
(5, 2023, 'Cycle-2023', '2026-07-05 02:47:24', '2026-07-05 02:47:24'),
(6, 2024, 'Cycle-2024', '2026-07-05 02:47:36', '2026-07-05 02:47:36'),
(7, 2025, 'Cycle-2025', '2026-07-05 02:47:56', '2026-07-05 02:47:56'),
(8, 2026, 'Cycle-2026', '2026-07-05 02:48:07', '2026-07-05 02:48:07');

-- --------------------------------------------------------

--
-- Table structure for table `email_send_log`
--

CREATE TABLE `email_send_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sent_by` bigint(20) UNSIGNED NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `recipient_name` varchar(255) DEFAULT NULL,
  `cc` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `attachment_original_name` varchar(255) DEFAULT NULL,
  `status` enum('queued','sent','failed') NOT NULL DEFAULT 'queued',
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_send_log`
--

INSERT INTO `email_send_log` (`id`, `sent_by`, `recipient_email`, `recipient_name`, `cc`, `subject`, `body`, `attachment_path`, `attachment_original_name`, `status`, `error_message`, `sent_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'admin@qu.edu.qa', 'Admin', NULL, 'fdgs', 'dfgsd', 'email-attachments/6a79a014f3ede_signature.jpg', 'signature.jpg', 'failed', 'Connection could not be established with host mailhog :stream_socket_client(): php_network_getaddresses: getaddrinfo failed: No such host is known. ', NULL, '2026-08-10 04:55:33', '2026-08-10 04:55:35'),
(2, 1, 'lpi@qu.edu.qa', 'LPI', 'baharalikhaan@gmail.com', 'baharalikhaan@gmail.com', 'wert', NULL, NULL, 'failed', 'Connection could not be established with host mailhog :stream_socket_client(): php_network_getaddresses: getaddrinfo failed: No such host is known. ', NULL, '2026-08-10 04:55:50', '2026-08-10 04:55:52');

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `signature` text DEFAULT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_templates`
--

INSERT INTO `email_templates` (`id`, `name`, `subject`, `body`, `signature`, `category`, `created_at`, `updated_at`) VALUES
(1, 'qwer', 'qwer', 'you sent the ID version.\r\nMedical is now clean: 3000 for almost every grade (BPS-1 through BPS-16), with two exceptions at BPS-17 and BPS-19 flagged \"Varies.\"\r\nConveyance Allowance and Special Allow:2021 20% of initial BP 2017 Frozen are fully consistent within each grade — no flags at all.\r\nSeveral \"Frozen\" adhoc-relief types (DRA 2022, DRA 2025, Adhoc Relief 2013/2015) show heavy \"Varies\" flags across most grades — that\'s expected since these are usually calculated as a percentage of each person\'s frozen basic pay at a point in time, so within a grade different people can land on different amounts depending on their pay step.\r\n\r\nRates by Grade tab now has 106 rows across 8 grade/frozen-grade allowance types; Fixed & Percent Types has 13 types that don\'t vary by grade.\r\n\r\nWant me to dig into any of the \"Varies\" rows — for example, checking whether they cluster by BPS step/stage rather than being random?', 'qwer', 'general', '2026-08-11 05:47:52', '2026-08-11 05:50:46');

-- --------------------------------------------------------

--
-- Table structure for table `final_report_grading`
--

CREATE TABLE `final_report_grading` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `gradeA` double(8,2) DEFAULT NULL,
  `scoreA` decimal(8,2) NOT NULL DEFAULT 0.00,
  `autoGradeA` decimal(5,2) NOT NULL DEFAULT 0.00,
  `commentA` longtext DEFAULT NULL,
  `gradeB` double(8,2) DEFAULT NULL,
  `scoreB` decimal(8,2) NOT NULL DEFAULT 0.00,
  `autoGradeB` decimal(5,2) NOT NULL DEFAULT 0.00,
  `commentB` longtext DEFAULT NULL,
  `gradeC` double(8,2) DEFAULT NULL,
  `scoreC` decimal(8,2) NOT NULL DEFAULT 0.00,
  `autoGradeC` decimal(5,2) NOT NULL DEFAULT 0.00,
  `commentC` longtext DEFAULT NULL,
  `gradeD` double(8,2) DEFAULT NULL,
  `commentD` longtext DEFAULT NULL,
  `total` double(8,2) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `publish` enum('accepted','rejected','pending','reserved') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `isAdmin` bit(1) DEFAULT b'0',
  `isAccepted` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `final_report_grading`
--

INSERT INTO `final_report_grading` (`id`, `gradeA`, `scoreA`, `autoGradeA`, `commentA`, `gradeB`, `scoreB`, `autoGradeB`, `commentB`, `gradeC`, `scoreC`, `autoGradeC`, `commentC`, `gradeD`, `commentD`, `total`, `user_id`, `project_id`, `publish`, `created_at`, `updated_at`, `isAdmin`, `isAccepted`) VALUES
(1, 1.00, '7.00', '0.90', '', 2.00, '8.00', '1.82', '', 5.00, '3.00', '5.00', '', 4.00, '', 18.00, 4, 2, 'accepted', '2026-08-03 06:46:11', '2026-08-03 06:46:11', b'0', 1);

-- --------------------------------------------------------

--
-- Table structure for table `gauge_settings`
--

CREATE TABLE `gauge_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `redfrom` int(11) NOT NULL DEFAULT 0,
  `redto` int(11) NOT NULL DEFAULT 33,
  `yellowfrom` int(11) NOT NULL DEFAULT 34,
  `yellowto` int(11) NOT NULL DEFAULT 66,
  `greenfrom` int(11) NOT NULL DEFAULT 67,
  `greento` int(11) NOT NULL DEFAULT 100,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gauge_settings`
--

INSERT INTO `gauge_settings` (`id`, `name`, `redfrom`, `redto`, `yellowfrom`, `yellowto`, `greenfrom`, `greento`, `created_at`, `updated_at`) VALUES
(1, 'LPI Outcome', 0, 50, 34, 66, 67, 100, '2026-08-11 06:02:58', '2026-08-11 06:07:36'),
(2, 'LPI Average Score', 0, 33, 34, 66, 67, 100, '2026-08-11 06:02:58', '2026-08-11 06:02:58'),
(3, 'Reviewer Grading', 0, 33, 34, 66, 67, 100, '2026-08-11 06:02:58', '2026-08-11 06:02:58');

-- --------------------------------------------------------

--
-- Table structure for table `grants`
--

CREATE TABLE `grants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `grant_code` varchar(50) NOT NULL,
  `grant_name` varchar(255) NOT NULL,
  `category` enum('student','regular') NOT NULL DEFAULT 'regular',
  `funding_agency` varchar(255) DEFAULT NULL,
  `max_duration_years` smallint(5) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grants`
--

INSERT INTO `grants` (`id`, `grant_code`, `grant_name`, `category`, `funding_agency`, `max_duration_years`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'QUHI', 'Qatar University High Impact Grant', 'regular', 'Qatar University', 3, 'High-impact research funding for Qatar University faculty.', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 'QUST', 'Qatar University Student Grant', 'regular', 'Qatar University', 1, 'Research grant program for undergraduate and graduate students.', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(3, 'IRCC', 'International Research Collaboration Co-Fund', 'regular', 'Qatar University', 2, 'Co-funding scheme to support international research collaborations.', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(4, 'CDIRCC', 'Concept Development-International Research Collaboration Co-Fund', 'regular', 'Qatar University', 1, 'Concept development phase for international research collaboration co-funding.', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(5, 'QUCP', 'National Capacity Building Program', 'regular', 'Qatar University', 2, 'Capacity building program for national research development.', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(6, 'QUCG', 'Qatar University Collaborative Grant', 'regular', 'Qatar University', 2, 'Collaborative research grant program for multi-disciplinary teams.', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(7, 'QUT2RP', 'Transformative Research Priorities Readiness Program', 'regular', 'Qatar University', 2, 'Readiness program targeting transformative research priorities.', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(8, 'QUIHS', 'Interdisciplinary Humanities Grant', 'student', 'Qatar University', 2, 'Research funding for interdisciplinary humanities projects.', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(9, 'CG', 'Conference Grant', 'regular', 'Qatar University', 1, 'Funding support for attending and presenting at academic conferences.', 1, NULL, NULL),
(10, 'NRPU', 'National Research Program for Universities', 'regular', 'Qatar Research Development and Innovation Council', 3, 'National-level research funding program for Qatari universities.', 1, NULL, NULL),
(11, 'SRGP', 'Startup Research Grant Program', 'regular', 'Qatar University', 2, 'Seed funding for new research initiatives and early-stage projects.', 1, NULL, NULL),
(12, 'TDF', 'Technology Development Fund', 'regular', 'Qatar Research Development and Innovation Council', 2, 'Funding for technology development and commercialization projects.', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_07_28_000001_create_all_tables', 1),
(2, '2026_07_29_000001_add_report_submission_state_and_reviewer_rejections', 2),
(3, '2026_07_30_000001_update_workflow_statuses', 2),
(4, '2026_07_31_000001_add_outcome_id_and_url_to_project_publications', 3),
(5, '2026_08_01_000001_add_category_to_scores_table', 4),
(6, '2026_08_01_000002_create_project_students_details_table', 5),
(7, '2026_08_02_000001_add_isextended_to_projects_table', 6),
(8, '2026_08_02_000002_add_score_fields_to_final_report_grading_table', 7),
(9, '2026_08_02_000003_add_verification_to_project_researchers_table', 8),
(10, '2026_08_02_000004_add_verification_to_project_students_table', 9),
(11, '2026_08_03_000001_backfill_reviewer_rejections', 10),
(12, '2026_08_06_000001_create_report_reminders_sent_table', 11),
(13, '2026_08_10_000001_create_email_send_log_table', 12),
(14, '2026_08_10_000002_create_reviewer_ratings_table', 13),
(15, '2026_08_11_000001_create_email_templates_table', 14),
(16, '2026_08_11_000002_create_gauge_settings_table', 15),
(17, '2026_08_11_000003_create_team_table', 16),
(18, '2026_08_11_000004_create_project_budgets_table', 16);

-- --------------------------------------------------------

--
-- Table structure for table `nationalities`
--

CREATE TABLE `nationalities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `nationalities`
--

INSERT INTO `nationalities` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Bangladeshi', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(2, 'Egyptian', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(3, 'Indian', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(4, 'Iranian', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(5, 'Iraqi', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(6, 'Jordanian', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(7, 'Kuwaiti', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(8, 'Lebanese', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(9, 'Moroccan', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(10, 'Omani', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(11, 'Pakistani', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(12, 'Palestinian', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(13, 'Qatari', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(14, 'Saudi', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(15, 'Somali', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(16, 'Sudanese', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(17, 'Syrian', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(18, 'Tunisian', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(19, 'Turkish', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(20, 'Yemeni', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(21, 'Algerian', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(22, 'Bahraini', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(23, 'British', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(24, 'Canadian', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(25, 'Emirati', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(26, 'Filipino', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(27, 'Indonesian', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(28, 'Malaysian', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(29, 'Nigerian', '2026-07-27 02:41:02', '2026-07-27 02:41:02'),
(30, 'American', '2026-07-27 02:41:02', '2026-07-27 02:41:02');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pillars`
--

CREATE TABLE `pillars` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pillar` varchar(250) NOT NULL,
  `subpillar` longtext DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pillars`
--

INSERT INTO `pillars` (`id`, `pillar`, `subpillar`, `description`, `created_at`, `updated_at`) VALUES
(0, 'Health', 'Health\nArtificial Intelligence in Healthcare\nRegenerative Medicine and Stem Cell Research\nHealth and Wellness\nMembrane Destillation', NULL, NULL, NULL),
(1, 'Energy and Environment', 'Core Research Priority: Oil and Gas\nCore Research Priority: Energy Efficiency and Renewable Energy\nCore Research Priority: Materials\nCore Research Priority: Water and Food Security\nCore Research Priority: Environment and Biodiversity\nTransformative Research Priority: Waste to Value Solutions for Food, Water and Energy Sectors\nTransformative Research Priority: Cost effective CO2 capture and storage technology\nTransformative Research Priority: Combining high-performance materials and ICT for energy conversion, storage and transport\nTransformative Research Priority: Agriculture Technologies for Food and Other Applications\nEnergy and Environment', NULL, NULL, NULL),
(10, 'Social Sciences and Humanities', 'Core Research Priority: Economic Diversification and Sustainable Development\nCore Research Priority: Social Change and Identity\nCore Research Priority: National Security\nCore Research Priorities: Education and Capacity Building\nCore Research Priority: Women and Family\nTransformative Research Priority: Human Security\nTransformative Research Priority: Education and Economic Sustainability\nTransformative Research Priority: Entrepreneurial Strategies and Business Models\nSocial Sciences and Humanities\nSocial Sciences', NULL, NULL, NULL),
(18, 'Health and Biomedical Sciences', 'Core Research Priority: Diabetes and Cardiovascular Diseases\nCore Research Priority: Cancer\nCore Research Priority: Infectious Diseases\nCore Research Priority: Neurological, Psychiatric Disorders, and Mental Health\nCore Research Priority: Respiratory Diseases\nTransformative Research Priority: Stem Cells, Tissue Engineering and AI for Body Organs Repair\nTransformative Research Priority: Novel Techniques and Approaches in the Diagnosis and Treatment of Priority Diseases\nHealth and Biomedical Sciences', NULL, NULL, NULL),
(26, 'Information and Communication Technologies', 'Core Research Priority: Telecommunications and Infrastructure\nCore Research Priority: Artificial Intelligence and Smart Systems\nCore Research Priority: ICT in Health and Biomedical Applications\nTransformative Research Priority: Advanced ICT Tools\nTransformative Research Priority: Self-defending cybersecurity architecture\nTransformative Research Priority: Blockchain Based Efficiencies\nInformation and Communication Technologies\nICT', NULL, NULL, NULL),
(46, 'Digital Technology', 'Digital Technology', NULL, NULL, NULL),
(47, 'Resource Sustainability', 'Resource Sustainability', NULL, NULL, NULL),
(49, 'Social', 'Social Change', NULL, NULL, NULL),
(50, 'Environment', 'Environment\nEnvironmental sustainability', NULL, NULL, NULL),
(51, 'Energy', 'Energy', NULL, NULL, NULL),
(52, 'Engineering', 'Engineering\nArchitecture - Building Design\nStructural Engineering- Materials\nChemical Sensing (Chemical Engineering)\nCivil Engineering\nArchitecture and Urban planning\nStructural Engineering', NULL, NULL, NULL),
(56, 'Information and Communication Technology', 'Artificial Intelligence and Smart Systems\nICT medical application\nRobotics\nArtificial Intelligence', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `program_title` varchar(255) NOT NULL,
  `prog_rpt_deadline` timestamp NULL DEFAULT NULL,
  `extended_prog_rpt_deadline` timestamp NULL DEFAULT NULL,
  `prog_rpt2_deadline` date DEFAULT NULL,
  `extended_prog_rpt2_deadline` date DEFAULT NULL,
  `final_rpt_deadline` timestamp NULL DEFAULT NULL,
  `extended_final_rpt_deadline` timestamp NULL DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `grant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cycle_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `program_title`, `prog_rpt_deadline`, `extended_prog_rpt_deadline`, `prog_rpt2_deadline`, `extended_prog_rpt2_deadline`, `final_rpt_deadline`, `extended_final_rpt_deadline`, `description`, `is_visible`, `created_at`, `updated_at`, `grant_id`, `cycle_id`) VALUES
(1, 'CG - Cycle-2021', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 06:24:07', '2026-08-11 06:26:47', 9, 3);

-- --------------------------------------------------------

--
-- Table structure for table `progress_report_grading`
--

CREATE TABLE `progress_report_grading` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `analysis` varchar(255) DEFAULT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `recommendation` varchar(255) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `publish` enum('accepted','rejected','reserved','pending') NOT NULL DEFAULT 'pending',
  `achievementsRating` int(1) DEFAULT 1,
  `publicationsRating` int(1) DEFAULT 1,
  `studentsRating` int(1) DEFAULT 1,
  `achievementsComments` varchar(1200) DEFAULT NULL,
  `publicationsComments` varchar(1200) DEFAULT NULL,
  `studentsComments` varchar(1200) DEFAULT NULL,
  `ethical` int(11) DEFAULT -1,
  `isAccepted` int(1) NOT NULL,
  `report_type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `budgetRating` int(11) DEFAULT NULL,
  `budgetComments` varchar(1200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `progress_report_grading`
--

INSERT INTO `progress_report_grading` (`id`, `analysis`, `comments`, `recommendation`, `path`, `user_id`, `project_id`, `publish`, `achievementsRating`, `publicationsRating`, `studentsRating`, `achievementsComments`, `publicationsComments`, `studentsComments`, `ethical`, `isAccepted`, `report_type`, `created_at`, `updated_at`, `budgetRating`, `budgetComments`) VALUES
(1, '', '', '', NULL, 4, 2, 'accepted', 4, 3, 3, NULL, NULL, NULL, 1, 1, 'progress', '2026-08-03 06:27:14', '2026-08-03 06:44:46', 5, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `title_ar` varchar(500) DEFAULT NULL,
  `abstract` text DEFAULT NULL,
  `keywords` varchar(500) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `old_project_id` varchar(255) DEFAULT NULL,
  `total_score` decimal(10,2) DEFAULT NULL,
  `is_extended` tinyint(1) NOT NULL DEFAULT 0,
  `lpi_id` bigint(20) UNSIGNED DEFAULT NULL,
  `program_id` bigint(20) UNSIGNED DEFAULT NULL,
  `requested_budget_qar` decimal(12,2) DEFAULT NULL,
  `proposal_filename` varchar(255) DEFAULT NULL,
  `college_decision` varchar(255) DEFAULT NULL,
  `rsd_feedback` text DEFAULT NULL,
  `final_rsd_decision` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `title`, `title_ar`, `abstract`, `keywords`, `author`, `email`, `phone`, `old_project_id`, `total_score`, `is_extended`, `lpi_id`, `program_id`, `requested_budget_qar`, `proposal_filename`, `college_decision`, `rsd_feedback`, `final_rsd_decision`, `created_at`, `updated_at`) VALUES
(1, 'student test project 1', NULL, NULL, NULL, 'lpi@qu.edu.qa', '345345lpi@qu.edu.qa', NULL, 'QUHI-CENG-2425-000', NULL, 0, 6, 1, NULL, 'QUHI-CENG-2425-000_Application.pdf', NULL, NULL, NULL, '2026-08-03 06:24:07', '2026-08-03 06:24:07'),
(2, 'student test project 2', NULL, NULL, NULL, 'lpi@qu.edu.qa', 'lpi@qu.edu.qa', NULL, 'QUHI-CENG-2425-001', NULL, 0, 3, 1, NULL, 'QUHI-CENG-2425-001_Application.pdf', NULL, NULL, NULL, '2026-08-03 06:24:07', '2026-08-03 06:24:07');

-- --------------------------------------------------------

--
-- Table structure for table `projects_reviewers`
--

CREATE TABLE `projects_reviewers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(20) DEFAULT NULL,
  `proposalstatus` varchar(50) DEFAULT '0',
  `statusdate` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects_reviewers`
--

INSERT INTO `projects_reviewers` (`id`, `project_id`, `user_id`, `role`, `proposalstatus`, `statusdate`, `created_at`, `updated_at`) VALUES
(2, 2, 4, 'Reviewer', 'accepted', '2026-08-03', '2026-08-03 06:26:26', '2026-08-03 06:26:26');

-- --------------------------------------------------------

--
-- Table structure for table `project_college`
--

CREATE TABLE `project_college` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `college_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_college`
--

INSERT INTO `project_college` (`id`, `project_id`, `college_id`, `created_at`, `updated_at`) VALUES
(1, 1, 7, NULL, NULL),
(2, 2, 7, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `project_contributions`
--

CREATE TABLE `project_contributions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL COMMENT 'journal_q1, journal_q2, journal_q3, journal_q4, conference, book, edited_book, book_chapter, ip_disclosure, provisional_patent, patent_granted, open_source_sw, startup, hired_researcher, cross_college, research_awards',
  `detail` text DEFAULT NULL,
  `score` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_outcomes`
--

CREATE TABLE `project_outcomes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `online_date` date DEFAULT NULL,
  `verifcation_by_system` enum('verified','not-verified','pending') NOT NULL DEFAULT 'pending',
  `verifcation_by_reviewer` enum('verified','not-verified','pending') DEFAULT 'pending',
  `score` tinyint(4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_outcomes`
--

INSERT INTO `project_outcomes` (`id`, `project_id`, `user_id`, `type`, `identifier`, `online_date`, `verifcation_by_system`, `verifcation_by_reviewer`, `score`, `created_at`, `updated_at`) VALUES
(1, 2, 3, 'journal_q1', '345', NULL, 'pending', 'verified', 0, '2026-08-03 06:25:16', '2026-08-03 06:45:52'),
(2, 2, 3, 'provisional_patent', '345345', NULL, 'pending', 'verified', 0, '2026-08-03 06:25:18', '2026-08-03 06:45:50');

-- --------------------------------------------------------

--
-- Table structure for table `project_pillar`
--

CREATE TABLE `project_pillar` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `pillar_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_pillar`
--

INSERT INTO `project_pillar` (`id`, `project_id`, `pillar_id`, `created_at`, `updated_at`) VALUES
(1, 1, 26, NULL, NULL),
(2, 2, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `project_publications`
--

CREATE TABLE `project_publications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `outcome_id` bigint(20) UNSIGNED DEFAULT NULL,
  `authors` varchar(255) DEFAULT NULL,
  `publication_title` varchar(255) NOT NULL,
  `journal` varchar(255) DEFAULT NULL,
  `year` varchar(255) DEFAULT NULL,
  `doi` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_researchers`
--

CREATE TABLE `project_researchers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `days` int(11) NOT NULL DEFAULT 0,
  `score` tinyint(4) NOT NULL DEFAULT 0,
  `verifcation_by_reviewer` varchar(50) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_researchers`
--

INSERT INTO `project_researchers` (`id`, `project_id`, `user_id`, `name`, `category`, `days`, `score`, `verifcation_by_reviewer`, `created_at`, `updated_at`) VALUES
(1, 2, NULL, '345', 'RA', 0, 0, 'verified', '2026-08-03 06:25:24', '2026-08-03 06:45:54');

-- --------------------------------------------------------

--
-- Table structure for table `project_students`
--

CREATE TABLE `project_students` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('UG','masters','PhD') NOT NULL,
  `std_id` varchar(255) NOT NULL,
  `days` int(11) NOT NULL,
  `score` tinyint(4) NOT NULL,
  `verifcation_by_system` varchar(50) NOT NULL DEFAULT 'pending',
  `verifcation_by_reviewer` varchar(50) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_students`
--

INSERT INTO `project_students` (`id`, `project_id`, `user_id`, `type`, `std_id`, `days`, `score`, `verifcation_by_system`, `verifcation_by_reviewer`, `created_at`, `updated_at`) VALUES
(1, 2, 3, 'UG', '345', 345, 0, 'pending', 'verified', '2026-08-03 06:25:22', '2026-08-03 06:45:53');

-- --------------------------------------------------------

--
-- Table structure for table `project_students_details`
--

CREATE TABLE `project_students_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_student_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `student_status` varchar(50) DEFAULT NULL,
  `major` varchar(100) DEFAULT NULL,
  `minor` varchar(100) DEFAULT NULL,
  `college` varchar(100) DEFAULT NULL,
  `std_program` varchar(150) DEFAULT NULL,
  `std_level` varchar(50) DEFAULT NULL,
  `admission_term` varchar(50) DEFAULT NULL,
  `reg_in_course` varchar(100) DEFAULT NULL,
  `raw_response` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_students_details`
--

INSERT INTO `project_students_details` (`id`, `project_student_id`, `student_id`, `first_name`, `last_name`, `student_status`, `major`, `minor`, `college`, `std_program`, `std_level`, `admission_term`, `reg_in_course`, `raw_response`, `created_at`, `updated_at`) VALUES
(1, 1, '345', 'Test', 'Student', 'Active', 'Computer Science', 'Undeclared', 'Engineering', 'Master of Science', 'Master', '202410', 'Registered', '{\"items\":[{\"student_id\":\"345\",\"first_name\":\"Test\",\"last_name\":\"Student\",\"student_status\":\"Active\",\"major\":\"Computer Science\",\"minor\":\"Undeclared\",\"college\":\"Engineering\",\"std_program\":\"Master of Science\",\"std_level\":\"Master\",\"admission_term\":\"202410\",\"reg_in_course\":\"Registered\"}],\"hasMore\":false,\"limit\":25,\"offset\":0,\"count\":1,\"links\":[{\"rel\":\"self\",\"href\":\"http:\\/\\/quapxweb1.qu.edu.qa\\/sisapx\\/qusis\\/student_info\\/std\"}]}', '2026-08-03 06:25:22', '2026-08-03 06:25:22');

-- --------------------------------------------------------

--
-- Table structure for table `project_submissions`
--

CREATE TABLE `project_submissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('progress','final','readiness') NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `stored_filename` varchar(255) NOT NULL,
  `version` int(11) NOT NULL DEFAULT 1,
  `submitted` tinyint(1) NOT NULL DEFAULT 0,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_submissions`
--

INSERT INTO `project_submissions` (`id`, `project_id`, `user_id`, `type`, `original_filename`, `stored_filename`, `version`, `submitted`, `submitted_at`, `file_path`, `notes`, `created_at`, `updated_at`) VALUES
(1, 2, 3, 'progress', 'QUHI-CENG-2425-001_Application.pdf', 'QUHI-CENG-2425-001_progress.pdf', 1, 0, NULL, 'uploads/2021/CG/progress_reports/QUHI-CENG-2425-001_progress.pdf', NULL, '2026-08-03 06:25:27', '2026-08-03 06:25:27'),
(2, 2, 3, 'progress', 'QUHI-CENG-2425-001_Application.pdf', 'QUHI-CENG-2425-001_progress_v2.pdf', 2, 0, NULL, 'uploads/2021/CG/progress_reports/QUHI-CENG-2425-001_progress_v2.pdf', NULL, '2026-08-03 06:28:47', '2026-08-03 06:28:47'),
(3, 2, 3, 'progress', 'Vicentiu 584.pdf', 'QUHI-CENG-2425-001_progress_v3.pdf', 3, 0, NULL, 'uploads/2021/CG/progress_reports/QUHI-CENG-2425-001_progress_v3.pdf', NULL, '2026-08-03 06:44:20', '2026-08-03 06:44:20'),
(4, 2, 3, 'final', 'Vicentiu 584.pdf', 'QUHI-CENG-2425-001_final.pdf', 1, 0, NULL, 'uploads/2021/CG/final_reports/QUHI-CENG-2425-001_final.pdf', NULL, '2026-08-03 06:45:08', '2026-08-03 06:45:08'),
(5, 2, 3, 'readiness', 'Vicentiu 584.pdf', 'QUHI-CENG-2425-001_readiness.pdf', 1, 0, NULL, 'uploads/2021/CG/readiness_reports/QUHI-CENG-2425-001_readiness.pdf', NULL, '2026-08-03 06:45:11', '2026-08-03 06:45:11');

-- --------------------------------------------------------

--
-- Table structure for table `report_reminders_sent`
--

CREATE TABLE `report_reminders_sent` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `program_id` bigint(20) UNSIGNED DEFAULT NULL,
  `column_key` varchar(255) NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `recipient_type` varchar(20) NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviewer_grading`
--

CREATE TABLE `reviewer_grading` (
  `id` int(11) NOT NULL,
  `reviewer` int(11) NOT NULL,
  `cycle` int(11) NOT NULL,
  `conflict` int(11) NOT NULL,
  `responsiveness` int(11) NOT NULL,
  `comprehensiveness` int(11) NOT NULL,
  `no_reviewers` int(11) NOT NULL,
  `behaviour` int(11) NOT NULL,
  `scope_of_supply` varchar(250) DEFAULT 'Written Scientific Review',
  `mode_of_selection` varchar(250) DEFAULT 'From ORS Database',
  `basis_of_approval` varchar(250) DEFAULT 'Previous Successful Review',
  `type_extent_of_control` varchar(250) DEFAULT 'Former review Evaluation',
  `designation_of_approver` varchar(250) DEFAULT 'Post-Award Manager',
  `user_id` int(11) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp(),
  `updated_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviewer_ratings`
--

CREATE TABLE `reviewer_ratings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reviewer_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `program_id` bigint(20) UNSIGNED NOT NULL,
  `conflict` int(11) NOT NULL DEFAULT 0,
  `responsiveness` int(11) NOT NULL DEFAULT 0,
  `comprehensiveness` int(11) NOT NULL DEFAULT 0,
  `no_reviewers` int(11) NOT NULL DEFAULT 0,
  `behaviour` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviewer_ratings`
--

INSERT INTO `reviewer_ratings` (`id`, `reviewer_id`, `user_id`, `program_id`, `conflict`, `responsiveness`, `comprehensiveness`, `no_reviewers`, `behaviour`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 1, 4, 3, 5, 4, 4, '2026-08-10 05:32:27', '2026-08-10 05:37:49');

-- --------------------------------------------------------

--
-- Table structure for table `reviewer_rejections`
--

CREATE TABLE `reviewer_rejections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviewer_rejections`
--

INSERT INTO `reviewer_rejections` (`id`, `project_id`, `user_id`, `reason`, `created_at`) VALUES
(1, 2, 2, NULL, '2026-08-03 06:26:01'),
(2, 2, 4, NULL, '2026-08-03 06:27:14'),
(3, 2, 4, NULL, '2026-08-03 06:42:49'),
(4, 2, 4, NULL, '2026-08-03 06:43:52');

-- --------------------------------------------------------

--
-- Table structure for table `scores`
--

CREATE TABLE `scores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `label` varchar(20) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `value` decimal(5,2) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `scores`
--

INSERT INTO `scores` (`id`, `name`, `label`, `category`, `value`, `description`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'journal_q1', 'Q1', 'publication', '8.00', 'Journal articles (Web of Science — Q1)', 1, 1, '2026-08-02 11:40:21', '2026-08-02 11:40:21'),
(2, 'journal_q2', 'Q2', 'publication', '6.00', 'Journal articles (Web of Science — Q2)', 2, 1, '2026-08-02 11:40:21', '2026-08-02 11:40:21'),
(3, 'journal_q3', 'Q3', 'publication', '4.00', 'Journal articles (Web of Science — Q3)', 3, 1, '2026-08-02 11:40:21', '2026-08-02 11:40:21'),
(4, 'journal_q4', 'Q4', 'publication', '3.00', 'Journal articles (Web of Science — Q4)', 4, 1, '2026-08-02 11:40:21', '2026-08-02 11:40:21'),
(5, 'conference', 'Conf', 'publication', '2.00', 'Indexed international conferences', 5, 1, '2026-08-02 11:40:21', '2026-08-02 11:40:21'),
(6, 'book', 'Book', 'publication', '8.00', 'Published Books', 6, 1, '2026-08-02 11:40:21', '2026-08-02 11:40:21'),
(7, 'edited_book', 'EdBook', 'publication', '6.00', 'Edited Books (collection)', 7, 1, '2026-08-02 11:40:21', '2026-08-02 11:40:21'),
(8, 'book_chapter', 'Chap', 'publication', '4.00', 'Book Chapters', 8, 1, '2026-08-02 11:40:21', '2026-08-02 11:40:21'),
(9, 'ip_disclosure', 'IP', 'ip', '4.00', 'Intellectual Property Disclosure', 10, 1, '2026-08-02 11:40:21', '2026-08-02 11:40:21'),
(10, 'provisional_patent', 'FP', 'ip', '7.00', 'Provisional Patent Filed', 11, 1, '2026-08-02 11:40:21', '2026-08-02 11:40:21'),
(11, 'patent_granted', 'GP', 'ip', '9.00', 'Patents Granted', 12, 1, '2026-08-02 11:40:21', '2026-08-02 11:40:21'),
(12, 'open_source_sw', 'SW', 'ip', '8.00', 'Open Source Software', 13, 1, '2026-08-02 11:40:21', '2026-08-02 11:40:21'),
(13, 'startup', 'SUp', 'ip', '10.00', 'Start-Up Created', 14, 1, '2026-08-02 11:40:21', '2026-08-02 11:40:21'),
(14, 'masters', 'MSc', 'student', '2.00', 'Masters Student', 20, 1, '2026-08-02 11:40:21', '2026-08-02 11:40:21'),
(15, 'ug', 'UG', 'student', '1.00', 'Undergraduate Student', 21, 1, '2026-08-02 11:40:21', '2026-08-02 11:40:21'),
(16, 'phd', 'PhD', 'student', '3.00', 'PhD Student', 22, 1, '2026-08-02 11:40:21', '2026-08-02 11:40:21'),
(17, 'researcher', 'Res', 'student', '2.00', 'Researcher', 23, 1, '2026-08-02 11:40:21', '2026-08-02 11:40:21'),
(18, 'cross_college', 'CC', 'contribution', '2.00', 'Cross-College Participation', 30, 1, '2026-08-02 11:40:21', '2026-08-02 11:40:21'),
(19, 'research_awards', 'RA', 'contribution', '0.00', 'Research Awards (bonus)', 31, 1, '2026-08-02 11:40:21', '2026-08-02 11:40:21');

-- --------------------------------------------------------

--
-- Table structure for table `status_histories`
--

CREATE TABLE `status_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'User who performed the action',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `status_histories`
--

INSERT INTO `status_histories` (`id`, `project_id`, `status`, `user_id`, `metadata`, `created_at`, `updated_at`) VALUES
(1, 1, 'unregistered', 1, '{\"imported\":true}', '2026-08-03 06:24:07', '2026-08-03 11:24:07'),
(2, 2, 'unregistered', 1, '{\"imported\":true}', '2026-08-03 06:24:07', '2026-08-03 11:24:07'),
(3, 2, 'registered', 3, '[]', '2026-08-03 06:24:38', '2026-08-03 11:24:38'),
(4, 2, 'Assigned', 1, '{\"triggered_by\":\"assign\",\"reviewer_id\":\"2\"}', '2026-08-03 06:24:57', '2026-08-03 11:24:57'),
(5, 2, 'progress_added', 3, '{\"triggered_by\":\"progress\"}', '2026-08-03 06:25:29', '2026-08-03 11:25:29'),
(6, 2, 'proposal_rejected', 2, '{\"triggered_by\":\"reject-proposal\",\"rejected_by\":2,\"reason\":null}', '2026-08-03 06:26:01', '2026-08-03 11:26:01'),
(7, 2, 'Assigned', 1, '{\"triggered_by\":\"assign\",\"reviewer_id\":\"4\"}', '2026-08-03 06:26:26', '2026-08-03 11:26:26'),
(8, 2, 'Claimed', 4, '{\"triggered_by\":\"claim\",\"reviewer_role\":\"Reviewer\"}', '2026-08-03 06:27:01', '2026-08-03 11:27:01'),
(12, 2, 'progress_rejected', 4, '{\"triggered_by\":\"progress-grade-reject\",\"comment\":null}', '2026-08-03 06:42:49', '2026-08-03 11:42:49'),
(13, 2, 'progress_added', 3, '{\"triggered_by\":\"progress\"}', '2026-08-03 06:43:13', '2026-08-03 11:43:13'),
(14, 2, 'progress_rejected', 4, '{\"triggered_by\":\"progress-grade-reject\",\"comment\":null}', '2026-08-03 06:43:52', '2026-08-03 11:43:52'),
(15, 2, 'progress_added', 3, '{\"triggered_by\":\"progress\"}', '2026-08-03 06:44:23', '2026-08-03 11:44:23'),
(16, 2, 'progress_reviewed', 4, '{\"triggered_by\":\"progress-grade-accept\"}', '2026-08-03 06:44:46', '2026-08-03 11:44:46'),
(17, 2, 'final_added', 3, '{\"triggered_by\":\"final-report\"}', '2026-08-03 06:45:16', '2026-08-03 11:45:16'),
(18, 2, 'Graded', 4, '{\"triggered_by\":\"final-grade\"}', '2026-08-03 06:46:11', '2026-08-03 11:46:11');

-- --------------------------------------------------------

--
-- Table structure for table `team`
--

CREATE TABLE `team` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `path` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `introduction` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `team`
--

INSERT INTO `team` (`id`, `path`, `name`, `role`, `introduction`, `email`, `created_at`, `updated_at`, `phone`, `address`) VALUES
(1, 'storage/images/AZ6pAmkASza4X6fO0GttS2Jkd6yzHcF75lYDpMpf.jpg', 'Dr. Mustafa Saleh Nasser', 'Admin', 'Manager of Post-Award', 'm.nasser@qu.edu.qa', NULL, '2024-05-21 05:53:23', '4403-5610', ' H10 - zone1 - Room G224'),
(2, 'storage\\images\\userImage.jpg', 'Ms. Reem Hizam', 'Admin', 'Senior Post Award Specialist', 'reem.m@qu.edu.qa', NULL, NULL, '4403-3923', 'H10 - zone1 - Room G209'),
(3, 'serveImg?file=hYrLUHwukppjbDFEJpqO1OP9FkcHDKiTATld84Lf.png', 'Mrs. Maysoon Gharzeddine', 'Admin', 'Senior Post-Award Specialist', 'maysoon@qu.edu.qa', NULL, '2024-01-14 04:32:30', '4403-3926', 'H10 - zone1 - Room G212'),
(4, 'storage\\images\\userImage.jpg', 'Ms. Hala Almahmoud', 'Admin', 'Research Support Service Specialist', 'halmahmoud@qu.edu.qa', NULL, NULL, '4403-7126', 'H10 - zone1 - Room G209'),
(5, 'storage/images/userImage.jpg', 'Mrs. Wadha Labeed', 'Admin', 'Senior Post Award Specialist', 'w.alabdullah@qu.edu.qa', NULL, '2024-05-19 13:37:04', '4403-3924', 'H10 - zone1- Room G209');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'Student',
  `faculty` varchar(255) DEFAULT NULL,
  `qu_id` varchar(255) DEFAULT NULL,
  `nationality_id` bigint(20) UNSIGNED DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `college` varchar(255) DEFAULT NULL,
  `pillars` varchar(200) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `type`, `faculty`, `qu_id`, `nationality_id`, `phone`, `department`, `college`, `pillars`, `is_active`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@qu.edu.qa', NULL, '$2y$10$4omSl6V5HjgNk2lBcKgZD.htiCarlH72IcVUadVEJpzMlBkLAFAe.', 'Admin', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'DdDlw2gMm7po3vZGDglgpa2QOuHorjrlhAt3alJKoWAhmoT05PLxxHidK8kA', '2026-06-28 14:15:37', '2026-06-28 14:15:37'),
(2, 'Reviewer', 'reviewer@qu.edu.qa', NULL, '$2y$10$4omSl6V5HjgNk2lBcKgZD.htiCarlH72IcVUadVEJpzMlBkLAFAe.', 'Reviewer', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, '3yews6LLIoq0ciIDbstDSFo1XuUHOPVbTOGes64NF8mfpYsA7e39giGIwvJ2', '2026-06-28 14:15:37', '2026-06-28 14:15:37'),
(3, 'LPI', 'lpi@qu.edu.qa', NULL, '$2y$10$4omSl6V5HjgNk2lBcKgZD.htiCarlH72IcVUadVEJpzMlBkLAFAe.', 'LPI', '0', NULL, NULL, NULL, NULL, 'College of Education', '', 1, '9ULdjq3wUZsy5Mn6xaKINaimBGrwMNoQkghIaqC34Vog7g4TKtObSvoPwWDc', '2026-06-28 14:15:37', '2026-07-27 02:44:18'),
(4, 'Reviewer2', 'reviewer2@qu.edu.qa', NULL, '$2y$10$4omSl6V5HjgNk2lBcKgZD.htiCarlH72IcVUadVEJpzMlBkLAFAe.', 'Reviewer', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'UCYEZUd1VUaSP7V4NUY0NQ3qOJZNcbCH8Ud37uH1KlHRAgIuhO9g1GhjN3Qq', '2026-06-28 14:15:37', '2026-06-28 14:15:37'),
(6, 'lpi@qu.edu.qa', '345345lpi@qu.edu.qa', NULL, '$2y$10$8lB0YArEWFYUfbwhOQLx2eJN82oAkW2yo54tEtFQj6dsiRO9m2ApC', 'LPI', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2026-07-19 05:52:31', '2026-07-19 05:52:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcement`
--
ALTER TABLE `announcement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcement_created_by_foreign` (`created_by`);

--
-- Indexes for table `colleges`
--
ALTER TABLE `colleges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `commitments`
--
ALTER TABLE `commitments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commitments_project_id_foreign` (`project_id`);

--
-- Indexes for table `cycle_configs`
--
ALTER TABLE `cycle_configs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_send_log`
--
ALTER TABLE `email_send_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_send_log_sent_by_foreign` (`sent_by`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `final_report_grading`
--
ALTER TABLE `final_report_grading`
  ADD PRIMARY KEY (`id`),
  ADD KEY `final_report_grading_user_id_foreign` (`user_id`),
  ADD KEY `final_report_grading_project_id_foreign` (`project_id`);

--
-- Indexes for table `gauge_settings`
--
ALTER TABLE `gauge_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grants`
--
ALTER TABLE `grants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `grants_grant_code_unique` (`grant_code`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nationalities`
--
ALTER TABLE `nationalities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nationalities_name_unique` (`name`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `pillars`
--
ALTER TABLE `pillars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cycles_grant_id_foreign` (`grant_id`),
  ADD KEY `programs_cycle_id_foreign` (`cycle_id`);

--
-- Indexes for table `progress_report_grading`
--
ALTER TABLE `progress_report_grading`
  ADD PRIMARY KEY (`id`),
  ADD KEY `progress_report_grading_user_id_foreign` (`user_id`),
  ADD KEY `progress_report_grading_project_id_foreign` (`project_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `projects_user_id_foreign` (`lpi_id`),
  ADD KEY `projects_cycle_id_foreign` (`program_id`);

--
-- Indexes for table `projects_reviewers`
--
ALTER TABLE `projects_reviewers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `projects_reviewers_project_id_foreign` (`project_id`),
  ADD KEY `projects_reviewers_user_id_foreign` (`user_id`);

--
-- Indexes for table `project_college`
--
ALTER TABLE `project_college`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_tag_project_id_foreign` (`project_id`),
  ADD KEY `project_tag_tag_id_foreign` (`college_id`);

--
-- Indexes for table `project_contributions`
--
ALTER TABLE `project_contributions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_contributions_project_id_foreign` (`project_id`),
  ADD KEY `project_contributions_user_id_foreign` (`user_id`);

--
-- Indexes for table `project_outcomes`
--
ALTER TABLE `project_outcomes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `outcomes_project_id_foreign` (`project_id`),
  ADD KEY `outcomes_user_id_foreign` (`user_id`);

--
-- Indexes for table `project_pillar`
--
ALTER TABLE `project_pillar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_pillar_project_id_foreign` (`project_id`);

--
-- Indexes for table `project_publications`
--
ALTER TABLE `project_publications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_publications_project_id_foreign` (`project_id`),
  ADD KEY `project_publications_outcome_id_foreign` (`outcome_id`);

--
-- Indexes for table `project_researchers`
--
ALTER TABLE `project_researchers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_researchers_project_id_foreign` (`project_id`),
  ADD KEY `project_researchers_user_id_foreign` (`user_id`);

--
-- Indexes for table `project_students`
--
ALTER TABLE `project_students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attached_students_project_id_foreign` (`project_id`),
  ADD KEY `attached_students_user_id_foreign` (`user_id`);

--
-- Indexes for table `project_students_details`
--
ALTER TABLE `project_students_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_students_details_project_student_id_foreign` (`project_student_id`),
  ADD KEY `project_students_details_student_id_index` (`student_id`);

--
-- Indexes for table `project_submissions`
--
ALTER TABLE `project_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_submissions_user_id_foreign` (`user_id`),
  ADD KEY `project_submissions_project_id_type_submitted_index` (`project_id`,`type`,`submitted`);

--
-- Indexes for table `report_reminders_sent`
--
ALTER TABLE `report_reminders_sent`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_reminders_sent_project_id_foreign` (`project_id`),
  ADD KEY `report_reminders_sent_program_id_column_key_index` (`program_id`,`column_key`);

--
-- Indexes for table `reviewer_grading`
--
ALTER TABLE `reviewer_grading`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviewer_ratings`
--
ALTER TABLE `reviewer_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reviewer_ratings_reviewer_id_program_id_unique` (`reviewer_id`,`program_id`),
  ADD KEY `reviewer_ratings_user_id_foreign` (`user_id`),
  ADD KEY `reviewer_ratings_program_id_foreign` (`program_id`);

--
-- Indexes for table `reviewer_rejections`
--
ALTER TABLE `reviewer_rejections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviewer_rejections_user_id_foreign` (`user_id`),
  ADD KEY `reviewer_rejections_project_id_user_id_index` (`project_id`,`user_id`);

--
-- Indexes for table `scores`
--
ALTER TABLE `scores`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status_histories`
--
ALTER TABLE `status_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status_histories_project_id_status_index` (`project_id`,`status`);

--
-- Indexes for table `team`
--
ALTER TABLE `team`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_nationality_id_foreign` (`nationality_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcement`
--
ALTER TABLE `announcement`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `colleges`
--
ALTER TABLE `colleges`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `commitments`
--
ALTER TABLE `commitments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cycle_configs`
--
ALTER TABLE `cycle_configs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `email_send_log`
--
ALTER TABLE `email_send_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `final_report_grading`
--
ALTER TABLE `final_report_grading`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `gauge_settings`
--
ALTER TABLE `gauge_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `grants`
--
ALTER TABLE `grants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `nationalities`
--
ALTER TABLE `nationalities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pillars`
--
ALTER TABLE `pillars`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `progress_report_grading`
--
ALTER TABLE `progress_report_grading`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `projects_reviewers`
--
ALTER TABLE `projects_reviewers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `project_college`
--
ALTER TABLE `project_college`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `project_contributions`
--
ALTER TABLE `project_contributions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_outcomes`
--
ALTER TABLE `project_outcomes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `project_pillar`
--
ALTER TABLE `project_pillar`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `project_publications`
--
ALTER TABLE `project_publications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_researchers`
--
ALTER TABLE `project_researchers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `project_students`
--
ALTER TABLE `project_students`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `project_students_details`
--
ALTER TABLE `project_students_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `project_submissions`
--
ALTER TABLE `project_submissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `report_reminders_sent`
--
ALTER TABLE `report_reminders_sent`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviewer_grading`
--
ALTER TABLE `reviewer_grading`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviewer_ratings`
--
ALTER TABLE `reviewer_ratings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reviewer_rejections`
--
ALTER TABLE `reviewer_rejections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `scores`
--
ALTER TABLE `scores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `status_histories`
--
ALTER TABLE `status_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `team`
--
ALTER TABLE `team`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcement`
--
ALTER TABLE `announcement`
  ADD CONSTRAINT `announcement_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `email_send_log`
--
ALTER TABLE `email_send_log`
  ADD CONSTRAINT `email_send_log_sent_by_foreign` FOREIGN KEY (`sent_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `programs`
--
ALTER TABLE `programs`
  ADD CONSTRAINT `cycles_grant_id_foreign` FOREIGN KEY (`grant_id`) REFERENCES `grants` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `programs_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `cycle_configs` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_cycle_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `projects_user_id_foreign` FOREIGN KEY (`lpi_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects_reviewers`
--
ALTER TABLE `projects_reviewers`
  ADD CONSTRAINT `projects_reviewers_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projects_reviewers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_college`
--
ALTER TABLE `project_college`
  ADD CONSTRAINT `project_tag_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_contributions`
--
ALTER TABLE `project_contributions`
  ADD CONSTRAINT `project_contributions_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_contributions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_pillar`
--
ALTER TABLE `project_pillar`
  ADD CONSTRAINT `project_pillar_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_publications`
--
ALTER TABLE `project_publications`
  ADD CONSTRAINT `project_publications_outcome_id_foreign` FOREIGN KEY (`outcome_id`) REFERENCES `project_outcomes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_publications_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_researchers`
--
ALTER TABLE `project_researchers`
  ADD CONSTRAINT `project_researchers_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_researchers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_students_details`
--
ALTER TABLE `project_students_details`
  ADD CONSTRAINT `project_students_details_project_student_id_foreign` FOREIGN KEY (`project_student_id`) REFERENCES `project_students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_submissions`
--
ALTER TABLE `project_submissions`
  ADD CONSTRAINT `project_submissions_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_submissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `report_reminders_sent`
--
ALTER TABLE `report_reminders_sent`
  ADD CONSTRAINT `report_reminders_sent_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `report_reminders_sent_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviewer_ratings`
--
ALTER TABLE `reviewer_ratings`
  ADD CONSTRAINT `reviewer_ratings_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviewer_ratings_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviewer_ratings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviewer_rejections`
--
ALTER TABLE `reviewer_rejections`
  ADD CONSTRAINT `reviewer_rejections_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviewer_rejections_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `status_histories`
--
ALTER TABLE `status_histories`
  ADD CONSTRAINT `status_histories_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_nationality_id_foreign` FOREIGN KEY (`nationality_id`) REFERENCES `nationalities` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

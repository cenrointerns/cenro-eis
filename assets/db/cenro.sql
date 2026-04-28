-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2026 at 06:36 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cenro`
--

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `document_type` varchar(100) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `employee_id`, `file_name`, `document_type`, `file_path`, `file_type`, `uploaded_at`) VALUES
(6, 11, '', 'Special Order', 'uploads/BINANLAO__IRVY_M_/Special_Order/special_order_1777281335.pdf', NULL, '2026-04-27 09:15:35'),
(7, 12, '', 'PDS', 'uploads/BAGANAO__ARNOLD_L_/PDS/pds_1777281356.docx', NULL, '2026-04-27 09:15:56'),
(8, 12, '', 'IPCR', 'uploads/BAGANAO__ARNOLD_L_/IPCR/ipcr_1777281365.pdf', NULL, '2026-04-27 09:16:05');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `gender` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `nosca_item_number` varchar(100) DEFAULT NULL,
  `place_of_assignment` varchar(150) DEFAULT NULL,
  `position_title` varchar(150) DEFAULT NULL,
  `salary_grade` varchar(50) DEFAULT NULL,
  `civil_service_eligibility` varchar(150) DEFAULT NULL,
  `education` varchar(150) DEFAULT NULL,
  `date_of_appointment` date DEFAULT NULL,
  `length_of_service` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `name`, `age`, `status`, `image`, `created_at`, `gender`, `date_of_birth`, `nosca_item_number`, `place_of_assignment`, `position_title`, `salary_grade`, `civil_service_eligibility`, `education`, `date_of_appointment`, `length_of_service`) VALUES
(1, 'PADING, ARVIE CRIS A.', 25, 'Contract of Service', '69e9bf2aa53bc.jpg', '2026-04-23 02:47:38', 'Female', '2000-06-14', '', 'Enforcement Monitoring Section', 'LAND EXAMINER/VERIFIER', '12,500', 'RA 1080 - Forester', 'BS FORESTRY', '2025-08-08', '0 years, 8 months, 15 days'),
(2, 'LAPORNINA, MARY LORIE JOY J.', 26, 'Contract of Service', '69e9c0d45033e.jpg', '2026-04-23 02:53:12', 'Female', '2000-12-17', '', 'Regulation and Permitting Section', 'LAND EXAMINER/ VERIFIER', '12,500.00', 'Penology Officer Eligibility', 'BS ENVIRONMENTAL SCIENCE', '2023-07-18', '2 years, 9 months, 5 days'),
(11, 'BINANLAO, IRVY M.', 30, 'Contract of Service', '69f0107c7cdd1.jpg', '2026-04-23 07:20:31', 'Male', '1995-12-26', '', '', 'DATA ENCODER', '16,000.00', 'NO ELIGIBILITY', 'BACHELOR OF ARTS IN SOCIOLOGY', '2025-07-01', '0 years, 9 months, 27 days'),
(12, 'BAGANAO, ARNOLD L.', 37, 'Contract of Service', NULL, '2026-04-23 07:27:04', 'Male', '1988-05-01', '', '', 'FOREST PROTECTION OFFICER', '12,500.00', 'NO ELIGIBILITY', 'HIGH SCHOOL GRADUATE', '2021-11-01', '4 years, 5 months, 22 days'),
(13, 'CABUGNASON, ARIEL D.', 33, 'Contract of Service', NULL, '2026-04-23 07:30:10', 'Male', '1992-08-28', '', '', 'FOREST PROTECTION OFFICER', '12,500.00', 'NO ELIGIBILITY', 'BS AGRICULTURE', '2013-07-15', '12 years, 9 months, 8 days'),
(14, 'DOVERTE, ROBERT PAUL U.', 38, 'Contract of Service', NULL, '2026-04-23 07:33:40', 'Male', '1988-01-12', '', '', 'FOREST PROTECTION OFFICER', '12,500.00', 'NO ELIGIBILITY', 'HIGH SCHOOL GRADUATE', '2016-06-01', '9 years, 10 months, 22 days'),
(16, 'DOVERTE, BLESILDA U.', 57, 'Contract of Service', NULL, '2026-04-23 07:35:56', 'Female', '1968-06-22', '', '', 'ADMIN AIDE (UTILITY)', '11,350.00', 'NO ELIGIBILITY', 'HIGH SCHOOL GRADUATE', '2015-04-21', '11 years, 0 months, 2 days'),
(17, 'PAREJO, KIT IVAN REY S.', 26, 'Contract of Service', NULL, '2026-04-23 07:38:24', 'Male', '1999-08-10', '', '', 'NETWORK SPECIALIST/ PC TECHNICIAN', '20,000.00', 'CS Professional', 'BS INFORMATION TECHNOLOGY', '2020-09-06', '5 years, 7 months, 17 days'),
(18, 'RAGMAC, EUGENE C.', 58, 'Contract of Service', NULL, '2026-04-23 07:41:02', 'Male', '1967-11-23', '', '', 'FOREST PROTECTION OFFICER', '12,500.00', 'NO ELIGIBILITY', 'HIGH SCHOOL GRADUATE', '2020-09-06', '5 years, 7 months, 17 days'),
(19, 'SUNOGAN, ANGELICA Z.', 30, 'Contract of Service', NULL, '2026-04-23 07:42:52', 'Female', '1995-09-01', '', '', 'PLANNING SUPPORT STAFF', '17,750.00', 'RA 1080 - Forester', 'BS FORESTRY/ MASTER IN GOVERNMENT MANAGEMENT (Graduate)', '2020-02-20', '6 years, 2 months, 3 days'),
(20, 'TARAY, MARCELINO III F.', 31, 'Contract of Service', NULL, '2026-04-23 07:44:57', 'Male', '1994-09-08', '', '', 'FOREST EXTENSION OFFICER', '25,586.00', 'CS Professional', 'BS ENVIRONMENTAL SCIENCE', '2014-09-01', '11 years, 7 months, 22 days'),
(21, 'PADLA, FLORENCIO ACAO', 61, 'Permanent', '', '2026-04-23 07:46:40', 'Male', '1965-02-10', 'OSEC-DENRB-DMO4-51-2014', 'Enforcement Monitoring Section', 'DEVELOPMENT OFFICER IV / DEPUTY CENRO', '82,963', 'RA 1080', 'BS FORESTRY, MGA, MSF', '1997-02-25', '29 years, 2 months, 3 days'),
(22, 'YAMBAGON, RICAHRD B.', 39, 'Contract of Service', NULL, '2026-04-23 07:48:22', 'Male', '1986-12-30', '', '', 'FOREST PROTECTION OFFICER', '12,500.00', 'NO ELIGIBILITY', 'BS FORESTRY', '2019-01-07', '7 years, 3 months, 16 days'),
(23, 'URSAL, DESIREE FE T.', 35, 'Contract of Service', NULL, '2026-04-23 07:49:47', 'Female', '1990-09-13', '', '', 'CDS STAFF', '12,500.00', 'RA 1080 - Forester', 'BS FORESTRY/ MASTER IN GOVERNMENT MANAGEMENT (Graduate)', '2021-12-15', '4 years, 4 months, 8 days'),
(24, 'BARRAQUIAS, CLARENCE C.', 53, 'Permanent', '', '2026-04-23 08:07:45', 'Male', '1972-07-07', '', '', 'UTILITY WORKER I', '15,456.00', 'NO ELIGIBILITY', 'AB 90 UNITS', '2004-02-16', '22 years, 2 months, 12 days'),
(25, 'DORIA, NELSON L.', 61, 'Permanent', NULL, '2026-04-23 08:10:04', 'Male', '1964-09-08', '', '', 'UTILITY WORKER I', '15,456.00', 'NO ELIGIBILITY', 'BS GEODETIC ENGINEERING', '1989-04-11', '37 years, 0 months, 12 days'),
(26, 'MENDOZA, MARIVIC N.', 59, 'Permanent', '', '2026-04-23 08:20:44', 'Female', '1967-03-12', '', '', 'UTILITY WORKER I', '15,456', 'NO ELIGIBILITY', 'ASSOCIATE GEODETIC ENGINEERING', '1997-01-20', '29 years, 3 months, 3 days'),
(27, 'TABAMO, MERLITA LUNA', 56, 'Permanent', '', '2026-04-27 10:06:53', 'Female', '1970-02-01', 'OSEC-DENRB-CENRO-22-1998', '', 'CENR OFFICE', '102603', 'CSP', 'BS FORESTRY, MASTER IN GOVERNMENT ADMINSTRATION/ DIPLOMA IN LAND VALUATION, 49 UNITS DOCTOR IN PUBLIC ADMINSTRATION-CAR', '2003-12-29', '22 years, 3 months, 29 days'),
(28, 'PAREJO, LUZVIMINDA SALPID', 53, 'Permanent', NULL, '2026-04-28 02:26:11', 'Female', '1972-07-12', 'OSEC-DENRB-FORST3-42-2014', 'Conservation and Development Section ', 'FORESTER III', '53,818', 'RA 1080 ', 'BS FORESTRY, 36 UNITS MPA', '2015-08-03', '10 years, 8 months, 25 days'),
(29, 'COLINARES, WILLIAM JAMES NAGUIO', 52, 'Permanent', NULL, '2026-04-28 02:29:47', 'Male', '1973-12-23', 'OSEC-DENRB-LAMO3-87-1998', 'Regulation and Permitting Section', 'LAND MANAGEMENT OFFICER III', '54,933', 'CSP', 'AB PHILOSOPHY, BACHELOR OF LAWS - 50 UNITS', '2002-02-01', '24 years, 2 months, 27 days'),
(30, 'PANDE, JAMES IAN DE JESUS', 41, 'Permanent', NULL, '2026-04-28 02:34:05', 'Male', '1984-07-30', 'OSEC-DENRB-SREMS-72-2014', 'Conservation and Development Section', 'SENIOR ECOSYSTEMS MANAGEMENT SPECIALIST', '54,371', 'RA 1080 ', 'BS FORESTRY, MASTERS OF SCIENCE IN FORESTRY MAJOR IN FOREST RESOURCE MANAGEMENT UNDERGRADUATE', '2019-05-28', '6 years, 11 months, 0 days'),
(32, 'GAYONA, EDGAR CLARITO', 63, 'Permanent', NULL, '2026-04-28 02:43:07', 'Male', '1962-12-09', 'OSEC-DENRB-ECOMS-59-2014', '', 'ECOSYSTEMS MANAGEMENT SPECIALIST II', '43,442', 'RA 1080 ', 'BS FORESTRY', '2004-01-16', '22 years, 3 months, 12 days'),
(33, 'AMBOLA, SHAIRA AIMA BANGCOLONGAN ', 27, 'Permanent', NULL, '2026-04-28 02:46:03', 'Female', '1999-04-08', 'OSEC-DENRB-ECOMS-60-1998', 'PLANNING SUPPORT UNIT', 'ECOSYSTEMS MANAGEMENT SPECIALIST II', '42,178', 'RA 1080, CSP', 'BS FORESTRY, MPA UNDERGRADUATE', '2020-04-27', '6 years, 0 months, 1 days'),
(34, 'NUÑEZA, RONIE PESCADERO', 44, 'Permanent', NULL, '2026-04-28 02:53:40', 'Male', '1982-01-05', 'OSEC-DENRB-FMS2-43-1998', '', 'FOREST MANAGEMENT SPECIALIST II - 15', '42,178', 'RA 1080 ', 'BS FORESTRY', '2021-02-01', '5 years, 2 months, 27 days');

--
-- Triggers `employees`
--
DELIMITER $$
CREATE TRIGGER `trg_employees_before_insert` BEFORE INSERT ON `employees` FOR EACH ROW BEGIN
    DECLARE y INT DEFAULT 0;
    DECLARE m INT DEFAULT 0;
    DECLARE d INT DEFAULT 0;
    DECLARE temp_date DATE;

    -- =========================
    -- ✅ AGE (from date_of_birth)
    -- =========================
    IF NEW.date_of_birth IS NOT NULL THEN
        SET NEW.age = TIMESTAMPDIFF(YEAR, NEW.date_of_birth, CURDATE());
    ELSE
        SET NEW.age = NULL;
    END IF;

    -- =========================
    -- ✅ LENGTH OF SERVICE
    -- (from date_of_appointment)
    -- =========================
    IF NEW.date_of_appointment IS NOT NULL THEN

        SET y = TIMESTAMPDIFF(YEAR, NEW.date_of_appointment, CURDATE());
        SET temp_date = DATE_ADD(NEW.date_of_appointment, INTERVAL y YEAR);

        SET m = TIMESTAMPDIFF(MONTH, temp_date, CURDATE());
        SET temp_date = DATE_ADD(temp_date, INTERVAL m MONTH);

        SET d = DATEDIFF(CURDATE(), temp_date);

        SET NEW.length_of_service =
            CONCAT(y, ' years, ', m, ' months, ', d, ' days');

    ELSE
        SET NEW.length_of_service = NULL;
    END IF;

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_employees_before_update` BEFORE UPDATE ON `employees` FOR EACH ROW BEGIN
    DECLARE y INT DEFAULT 0;
    DECLARE m INT DEFAULT 0;
    DECLARE d INT DEFAULT 0;
    DECLARE temp_date DATE;

    -- AGE
    IF NEW.date_of_birth IS NOT NULL THEN
        SET NEW.age = TIMESTAMPDIFF(YEAR, NEW.date_of_birth, CURDATE());
    ELSE
        SET NEW.age = NULL;
    END IF;

    -- LENGTH OF SERVICE
    IF NEW.date_of_appointment IS NOT NULL THEN

        SET y = TIMESTAMPDIFF(YEAR, NEW.date_of_appointment, CURDATE());
        SET temp_date = DATE_ADD(NEW.date_of_appointment, INTERVAL y YEAR);

        SET m = TIMESTAMPDIFF(MONTH, temp_date, CURDATE());
        SET temp_date = DATE_ADD(temp_date, INTERVAL m MONTH);

        SET d = DATEDIFF(CURDATE(), temp_date);

        SET NEW.length_of_service =
            CONCAT(y, ' years, ', m, ' months, ', d, ' days');

    ELSE
        SET NEW.length_of_service = NULL;
    END IF;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'Robert', '$2y$10$FZHOeojchCjT3qJ0sGiPdOYKDkQLkOHcVkX0Zl0fqK/QUWhqLuy36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

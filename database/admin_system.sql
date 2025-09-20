-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 12, 2025 at 06:17 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `admin_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `inmates`
--

CREATE TABLE `inmates` (
  `id` int(11) NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `middleName` varchar(100) NOT NULL,
  `lastName` varchar(100) NOT NULL,
  `birthday` date NOT NULL,
  `gender` enum('Male','Female','','') NOT NULL,
  `address` varchar(255) NOT NULL,
  `maritalStatus` enum('Single','Married','Divorced','Widowed') NOT NULL,
  `inmateNumber` varchar(50) NOT NULL,
  `crimeCommitted` text NOT NULL,
  `timeServeStart` date NOT NULL,
  `sentence` text NOT NULL,
  `timeServeEnds` date NOT NULL,
  `status` enum('Active',' Released','Transferred') NOT NULL DEFAULT 'Active',
  `dateCreated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inmates`
--

INSERT INTO `inmates` (`id`, `firstName`, `middleName`, `lastName`, `birthday`, `gender`, `address`, `maritalStatus`, `inmateNumber`, `crimeCommitted`, `timeServeStart`, `sentence`, `timeServeEnds`, `status`, `dateCreated`) VALUES
(1, '', '', '', '0000-00-00', '', '', '', '', '', '0000-00-00', '', '0000-00-00', '', '2025-08-31 20:53:21'),
(29, 'TIMMY', 'ANGELUZ M.', 'CALAWOD', '2025-08-07', 'Female', 'mmmm', 'Single', '1234', 'dsfhsdf', '2025-08-20', 'sadad', '2025-08-12', '', '2025-08-31 21:21:57'),
(30, 'Castrence', 'Justin Marck Sol', 'B.', '2025-08-07', 'Male', 'gdgdd', 'Single', '424323', 'hffg', '2025-08-21', 'hgfh', '2025-11-30', '', '2025-08-31 21:34:32'),
(32, 'Nick', 'C', 'Canaveral', '2025-08-07', 'Male', 'gdgdd', 'Single', '4567', 'nakaw', '2024-08-21', 'hgfh', '2025-11-30', '', '2025-08-31 21:48:53'),
(33, 'cath', 'm', 'mau', '2002-08-11', 'Male', 'kasoy', 'Married', '8901', 'nakaw', '2023-09-09', 'hgfh', '2025-01-18', '', '2025-08-31 21:59:59'),
(34, 'ave', 'sds', 'sda', '2025-08-13', 'Male', 'dsadsad', 'Single', '898', 'vbc', '2025-08-14', 'sswqs', '2025-08-28', '', '2025-08-31 22:15:41'),
(35, 'cath', 'm', 'mau', '2002-08-11', 'Male', 'kasoy', '', '8902', 'gfhht', '2023-09-09', 'hgfh', '2025-01-18', 'Active', '2025-08-31 22:22:19'),
(36, 'jassss', 's', 'sajjj', '0016-12-09', 'Male', 'conci', 'Single', '00026', 'hahaha', '2010-08-08', 'sabnb', '2026-08-05', 'Transferred', '2025-08-31 22:48:03');

-- --------------------------------------------------------

--
-- Table structure for table `pending_visitors`
--

CREATE TABLE `pending_visitors` (
  `id` int(11) NOT NULL,
  `firstName` varchar(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `middleName` varchar(255) NOT NULL,
  `gender` enum('Male','Female','Other','') NOT NULL,
  `phoneNumber` int(11) NOT NULL,
  `permanentAddress` varchar(255) NOT NULL,
  `relationship` varchar(250) NOT NULL,
  `idType` enum('National ID','Drivers License','Barangay ID','PhilHealth','Voters','UMID') NOT NULL,
  `idNumber` int(25) NOT NULL,
  `inmate` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected','') NOT NULL DEFAULT 'pending',
  `submitted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pending_visitors`
--

INSERT INTO `pending_visitors` (`id`, `firstName`, `lastName`, `middleName`, `gender`, `phoneNumber`, `permanentAddress`, `relationship`, `idType`, `idNumber`, `inmate`, `status`, `submitted_at`) VALUES
(1, 'TIMMY', 'CALAWOD', 'ANGELUZ M.', 'Female', 2121, 'asS', 'DAUGHTER', 'National ID', 1234, 'Nick C Canaveral – 4567', 'approved', '0000-00-00 00:00:00'),
(3, 'nick', 'Canaveral', 'pinero', 'Male', 2147483647, 'bagong sikat', 'son', 'National ID', 12345, 'Nick C Canaveral – 4567', 'rejected', '0000-00-00 00:00:00'),
(6, 'nick', 'Canaveral', 'pinero', 'Male', 2147483647, 'bagong sikat', 'son', 'Drivers License', 1212, 'Nick C Canaveral – 4567', 'rejected', '0000-00-00 00:00:00'),
(7, 'js', 'Canaveral', 'pinero', 'Male', 2147483647, 'bagong sikat', 'son', 'National ID', 123456, 'Nick C Canaveral – 4567', 'rejected', '0000-00-00 00:00:00'),
(8, 'bea', 'catalogo', 'lapera', 'Female', 2147483647, 'la carlota', 'son', 'National ID', 1234567, 'Nick C Canaveral – 4567', 'approved', '0000-00-00 00:00:00'),
(9, 'abegail', 'manongol', 'salmorin', 'Female', 2147483647, 'San Agustin', 'son', 'Barangay ID', 2222, 'Nick C Canaveral – 4567', 'approved', '0000-00-00 00:00:00'),
(10, 'cath', 'manongol', 'salmorin', 'Female', 2147483647, 'San Agustin', 'son', 'National ID', 22221, 'Nick C Canaveral – 4567', 'rejected', '0000-00-00 00:00:00'),
(11, 'catherine', 'manongol', 'salmorin', 'Female', 2147483647, 'San Agustin', 'son', 'National ID', 22, 'Nick C Canaveral – 4567', 'rejected', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `prison_guard_keys`
--

CREATE TABLE `prison_guard_keys` (
  `id` int(11) NOT NULL,
  `key_code` varchar(100) NOT NULL,
  `sent_to` varchar(150) DEFAULT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `status` enum('unused','used','expired') DEFAULT 'unused',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registration_keys`
--

CREATE TABLE `registration_keys` (
  `id` int(11) NOT NULL,
  `key_code` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `role_type` enum('warden','prison_guard') NOT NULL,
  `created_by` int(11) NOT NULL,
  `usage_limit` int(11) NOT NULL DEFAULT 1,
  `usage_used` int(11) NOT NULL,
  `is_blocked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registration_keys`
--

INSERT INTO `registration_keys` (`id`, `key_code`, `email`, `role_type`, `created_by`, `usage_limit`, `usage_used`, `is_blocked`, `expires_at`, `created_at`) VALUES
(22, 'acc5cd1367c83f11', 'cmmauricio123@gmail.com', 'warden', 0, 1, 0, 0, NULL, '2025-08-30 23:15:58'),
(23, '34117f9eb8c475d6', 'catherinemaemauricio.bsit@gmail.com', 'warden', 0, 1, 0, 1, '2025-08-31 17:51:04', '2025-08-30 23:51:04'),
(36, 'c16c394e7f1bd8d1', 'catherinemaemauricio.bsit@gmail.com', 'warden', 1, 1, 0, 0, '2025-09-08 16:38:07', '2025-09-07 22:38:07'),
(46, '32c0c60ec06ec0b2', 'catherinemauricio52@gmail.com', 'prison_guard', 3, 1, 3, 0, '2025-09-09 02:40:26', '2025-09-08 08:40:26'),
(47, '28ac4fcd266e2b08', 'catherinemaemauricio14@gmail.com', 'prison_guard', 3, 1, 1, 0, '2025-09-09 06:44:42', '2025-09-08 12:44:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `birthday` date NOT NULL,
  `age` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('warden','prison_guard') NOT NULL,
  `signup_key` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('on_duty','off_duty') DEFAULT 'off_duty',
  `time_in` datetime DEFAULT NULL,
  `time_out` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `first_name`, `last_name`, `middle_name`, `gender`, `phone_number`, `birthday`, `age`, `password`, `role`, `signup_key`, `created_at`, `updated_at`, `status`, `time_in`, `time_out`) VALUES
(3, 'catherinemaemauricio.bsit@gmail.com', 'Catherine', 'Mauricio', 'Osias', 'Female', '0954369', '2002-05-14', 23, '$2y$10$ICWU/7X.JHp0kqETWcoUR.Nfl6PNT7.YLE0mGCt6TVEC0pcl0xYf.', 'warden', '', '2025-09-07 18:10:08', '2025-09-08 01:11:38', 'off_duty', NULL, NULL),
(9, 'catherinemauricio52@gmail.com', 'Justin', 'Castrence', 'Batolio', 'Male', '0945866657', '2001-09-17', 23, '$2y$10$BUmPqiroQ5oL8FchfeQmCuxpOTmtq.RY4zV/ivkbVvmm7Qf7.YPtu', 'prison_guard', '32c0c60ec06ec0b2', '2025-09-08 00:41:45', '2025-09-08 00:41:45', 'off_duty', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `visitors`
--

CREATE TABLE `visitors` (
  `id` int(255) NOT NULL,
  `firstName` varchar(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `middleName` varchar(255) NOT NULL,
  `gender` enum('Male','Female','Other','') NOT NULL,
  `phoneNumber` int(11) NOT NULL,
  `permanentAddress` varchar(255) NOT NULL,
  `relationship` varchar(250) NOT NULL,
  `idType` enum('National ID','Drivers License','Barangay ID','PhilHealth','Voters','UMID') NOT NULL,
  `idNumber` int(25) NOT NULL,
  `inmate` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected','') NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `approved_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visitors`
--

INSERT INTO `visitors` (`id`, `firstName`, `lastName`, `middleName`, `gender`, `phoneNumber`, `permanentAddress`, `relationship`, `idType`, `idNumber`, `inmate`, `status`, `created_at`, `approved_at`) VALUES
(1, 'TIMMY', 'CALAWOD', 'ANGELUZ M.', 'Female', 2121, 'asS', 'DAUGHTER', 'National ID', 543535, 'Nick C Canaveral – 4567', 'pending', '2025-09-02 20:36:05', '2025-09-07 08:38:45'),
(11, 'bea', 'catalogo', 'lapera', 'Female', 2147483647, 'la carlota', 'son', 'National ID', 12345, 'Nick C Canaveral – 4567', 'pending', '2025-09-02 21:51:50', '2025-09-07 08:38:23'),
(12, 'abegail', 'manongol', 'salmorin', 'Female', 2147483647, 'San Agustin', 'son', 'Barangay ID', 5453535, 'Nick C Canaveral – 4567', 'pending', '2025-09-03 11:42:07', '2025-09-07 08:37:43');

-- --------------------------------------------------------

--
-- Table structure for table `visitors_log`
--

CREATE TABLE `visitors_log` (
  `id` int(11) NOT NULL,
  `visitorsFullName` varchar(255) NOT NULL,
  `visitorsIdNumber` varchar(255) NOT NULL,
  `inmateToVisit` varchar(255) DEFAULT NULL,
  `relationshipToInmate` varchar(255) DEFAULT NULL,
  `timeIn` datetime DEFAULT NULL,
  `timeOut` datetime DEFAULT NULL,
  `status` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visitors_log`
--

INSERT INTO `visitors_log` (`id`, `visitorsFullName`, `visitorsIdNumber`, `inmateToVisit`, `relationshipToInmate`, `timeIn`, `timeOut`, `status`) VALUES
(24, 'TIMMY ANGELUZ M. CALAWOD', '543535', 'Nick C Canaveral – 4567', 'DAUGHTER', '2025-09-07 13:11:08', NULL, 'IN'),
(25, 'TIMMY ANGELUZ M. CALAWOD', '543535', 'Nick C Canaveral – 4567', 'DAUGHTER', NULL, '2025-09-07 13:18:43', 'OUT');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inmates`
--
ALTER TABLE `inmates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inmateNumber` (`inmateNumber`);

--
-- Indexes for table `pending_visitors`
--
ALTER TABLE `pending_visitors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idNumber` (`idNumber`);

--
-- Indexes for table `prison_guard_keys`
--
ALTER TABLE `prison_guard_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key_code` (`key_code`);

--
-- Indexes for table `registration_keys`
--
ALTER TABLE `registration_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key_code` (`key_code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `visitors`
--
ALTER TABLE `visitors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idNumber` (`idNumber`);

--
-- Indexes for table `visitors_log`
--
ALTER TABLE `visitors_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idNumber` (`visitorsIdNumber`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inmates`
--
ALTER TABLE `inmates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `pending_visitors`
--
ALTER TABLE `pending_visitors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `prison_guard_keys`
--
ALTER TABLE `prison_guard_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registration_keys`
--
ALTER TABLE `registration_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `visitors`
--
ALTER TABLE `visitors`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `visitors_log`
--
ALTER TABLE `visitors_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

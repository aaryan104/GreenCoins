-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 29, 2025 at 11:02 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `green_credits_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `credit_transactions`
--

CREATE TABLE `credit_transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `factory_id` int(11) DEFAULT NULL,
  `type` enum('request','sell') NOT NULL,
  `credits` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `credit_transactions`
--

INSERT INTO `credit_transactions` (`id`, `user_id`, `factory_id`, `type`, `credits`, `status`, `created_at`) VALUES
(29, 21, NULL, 'sell', 100, 'approved', '2025-08-29 17:00:31'),
(30, 21, NULL, 'sell', 100, 'approved', '2025-08-29 17:05:17'),
(31, 21, NULL, 'sell', 200, 'approved', '2025-08-29 17:06:03'),
(32, 12, 7, 'request', 200, 'approved', '2025-08-29 17:07:40'),
(33, 18, NULL, 'sell', 250, 'approved', '2025-08-29 17:15:05'),
(34, 21, NULL, 'sell', 30, 'pending', '2025-08-29 18:59:48'),
(35, 21, NULL, 'sell', 30, 'approved', '2025-08-29 18:59:52'),
(36, 21, NULL, 'sell', 70, 'pending', '2025-08-29 19:02:10'),
(37, 21, NULL, 'sell', 50, 'pending', '2025-08-29 19:03:16'),
(38, 18, NULL, 'sell', 300, 'approved', '2025-08-29 20:19:05'),
(39, 12, 7, 'request', 175, 'approved', '2025-08-29 20:20:53');

-- --------------------------------------------------------

--
-- Table structure for table `factories`
--

CREATE TABLE `factories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `production_type` varchar(255) DEFAULT NULL,
  `registration_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `factories`
--

INSERT INTO `factories` (`id`, `user_id`, `name`, `location`, `production_type`, `registration_date`) VALUES
(7, 19, 'Shivam Manufacture Factory', 'Not Provided', 'Not Provided', '2025-08-27 14:55:38');

-- --------------------------------------------------------

--
-- Table structure for table `factory_requests`
--

CREATE TABLE `factory_requests` (
  `request_id` int(11) NOT NULL,
  `factory_id` int(11) NOT NULL,
  `credits` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `factory_requests`
--

INSERT INTO `factory_requests` (`request_id`, `factory_id`, `credits`, `status`, `created_at`) VALUES
(6, 7, 200, 'approved', '2025-08-29 17:07:25'),
(7, 7, 175, 'approved', '2025-08-29 20:20:36'),
(8, 7, 50, 'rejected', '2025-08-29 20:28:51');

-- --------------------------------------------------------

--
-- Table structure for table `green_credits`
--

CREATE TABLE `green_credits` (
  `id` int(11) NOT NULL,
  `factory_id` int(11) DEFAULT NULL,
  `credits` int(11) DEFAULT 0,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `green_credits`
--

INSERT INTO `green_credits` (`id`, `factory_id`, `credits`, `updated_at`) VALUES
(12, 7, 500, '2025-08-30 01:50:53');

-- --------------------------------------------------------

--
-- Table structure for table `planting_proofs`
--

CREATE TABLE `planting_proofs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `geo_location` varchar(255) DEFAULT NULL,
  `tree_species` varchar(255) DEFAULT NULL,
  `tree_count` int(11) DEFAULT NULL,
  `land_type` enum('public','private') DEFAULT NULL,
  `qrcode_file` varchar(255) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `planting_proofs`
--

INSERT INTO `planting_proofs` (`id`, `user_id`, `target_id`, `photo_url`, `video_url`, `geo_location`, `tree_species`, `tree_count`, `land_type`, `qrcode_file`, `uploaded_at`) VALUES
(28, 18, NULL, 'uploads/1756401545_tree1.jpeg', NULL, '12.6568, 56.9862', 'neem', 40, 'public', '/../assets/qrcodes/qr_28.png', '2025-08-28 22:49:05'),
(29, 18, NULL, 'uploads/1756402100_tree2.jpeg', NULL, '46.2397, 25.1395', 'limdo', 50, 'private', '/../assets/qrcodes/qr_29.png', '2025-08-28 22:58:20'),
(30, 20, NULL, 'uploads/1756469044_tree3.jpeg', NULL, '86.2015,62.9874', 'Piplo', 20, 'public', '/../assets/qrcodes/qr_30.png', '2025-08-29 17:34:04'),
(31, 20, NULL, 'uploads/1756469946_tree4.jpeg', NULL, '12.34567,89.1234', 'abc', 10, 'private', '/../assets/qrcodes/qr_31.png', '2025-08-29 17:49:07'),
(32, 21, NULL, 'uploads/1756483130_tree4.jpeg', NULL, '98.6532,78.4512', 'Neem', 60, 'private', '/../assets/qrcodes/qr_32.png', '2025-08-29 21:28:50'),
(33, 20, NULL, 'uploads/1756490074_tree1.jpeg', NULL, '21.1702,72.8311', 'Kanjir', 45, 'public', '/../assets/qrcodes/qr_33.png', '2025-08-29 23:24:34'),
(34, 18, NULL, 'uploads/1756498617_tree1.jpeg', NULL, '56.3214, 78.6321', 'ABC', 100, 'private', '/../assets/qrcodes/qr_34.png', '2025-08-30 01:46:57'),
(35, 21, NULL, NULL, NULL, '23.052288, 72.581120', 'oak', 6, 'private', '/../assets/qrcodes/qr_35.png', '2025-08-30 02:29:27'),
(36, 21, NULL, NULL, NULL, '23.052288, 72.581120', 'cedar', 10, '', '/../assets/qrcodes/qr_36.png', '2025-08-30 02:32:21');

-- --------------------------------------------------------

--
-- Table structure for table `pollution_data`
--

CREATE TABLE `pollution_data` (
  `id` int(11) NOT NULL,
  `factory_id` int(11) DEFAULT NULL,
  `source` enum('government','self-reported') NOT NULL,
  `emission_tons` decimal(10,2) NOT NULL,
  `report_month` date NOT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pollution_data`
--

INSERT INTO `pollution_data` (`id`, `factory_id`, `source`, `emission_tons`, `report_month`, `uploaded_at`) VALUES
(1, 7, 'government', 5.20, '2025-08-01', '2025-08-29 00:32:43');

-- --------------------------------------------------------

--
-- Table structure for table `tree_targets`
--

CREATE TABLE `tree_targets` (
  `id` int(11) NOT NULL,
  `factory_id` int(11) DEFAULT NULL,
  `target_month` date NOT NULL,
  `emission_tons` decimal(10,2) DEFAULT NULL,
  `tree_target` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('individual','NGO','admin','factory') NOT NULL DEFAULT 'individual',
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password_hash`, `role`, `profile_image`, `created_at`, `email_verified`, `email_verified_at`) VALUES
(11, 'Ngo', 'ngo@greencoin.com', '$2y$10$PCCfgH7w1TsK7ieEUKG5UOgSLYqvW0w20eQnlcAxNj8xutC9B3GxG', 'NGO', NULL, '2025-08-21 17:04:13', 1, NULL),
(12, 'Admin', 'admin@greencoin.com', '$2y$10$TAdgKMGTSQHEosaMJddRWuLjKUSzjA8oWf26vUAp4NdhpChIN2otm', 'admin', NULL, '2025-08-21 17:05:25', 1, NULL),
(18, 'Aaryan Mangukiya', 'aaryanmangukiya@gmail.com', '$2y$10$1aSAozNAzTXzYCKOhM0Quuo7vV0d.Ugk8EvyiW3Saa7DXL0MNcK9i', 'individual', NULL, '2025-08-27 09:22:15', 0, NULL),
(19, 'Shivam Manufacture', 'shivam.pvt@manufactur.com', '$2y$10$QXXqbORGkk460R3Jzg1iZuoIqzGTGK9ck2lpgeKpFfEo6S0UDXUf6', 'factory', NULL, '2025-08-27 09:25:38', 0, NULL),
(20, 'Darsh Boghara', 'darshboghara61@gmail.com', '$2y$10$XyQ8xQVsO90NU1tC4nnrpOX7Vem4kJvRbdirWse3If79qRz4HNyZu', 'individual', NULL, '2025-08-27 09:30:14', 0, NULL),
(21, 'Mangukiya Aaryan Ashvinbhai', 'mangukiyaaaryan46@gmail.com', '$2y$10$kZp1QpYdJ7URilyOOYS5S.EBLtsvELes31YpBcp8VtiN9yTnpP.Fq', 'individual', NULL, '2025-08-29 15:54:14', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_credits`
--

CREATE TABLE `user_credits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `credits` int(11) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_credits`
--

INSERT INTO `user_credits` (`id`, `user_id`, `credits`, `updated_at`) VALUES
(28, 18, 250, '2025-08-29 20:19:26'),
(29, 12, 605, '2025-08-29 20:20:53'),
(32, 21, 270, '2025-08-29 19:00:08'),
(51, 20, 275, '2025-08-29 17:55:13');

-- --------------------------------------------------------

--
-- Table structure for table `user_email_otps`
--

CREATE TABLE `user_email_otps` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `consumed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verifications`
--

CREATE TABLE `verifications` (
  `id` int(11) NOT NULL,
  `proof_id` int(11) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `verified_by` varchar(255) DEFAULT NULL,
  `verification_date` datetime DEFAULT NULL,
  `method` enum('automated','manual') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `verifications`
--

INSERT INTO `verifications` (`id`, `proof_id`, `is_verified`, `verified_by`, `verification_date`, `method`) VALUES
(11, 28, 1, 'Ngo', '2025-08-28 22:49:23', 'manual'),
(12, 29, 1, 'Ngo', '2025-08-28 22:58:51', 'manual'),
(13, 32, 1, 'Ngo', '2025-08-29 21:29:21', 'manual'),
(14, 33, 1, 'Ngo', '2025-08-29 23:25:10', 'manual'),
(15, 31, 1, 'Ngo', '2025-08-29 23:25:13', 'manual'),
(16, 34, 1, 'Ngo', '2025-08-30 01:47:24', 'manual');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `credit_transactions`
--
ALTER TABLE `credit_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_credit_transactions_factory` (`factory_id`);

--
-- Indexes for table `factories`
--
ALTER TABLE `factories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_factory_user` (`user_id`);

--
-- Indexes for table `factory_requests`
--
ALTER TABLE `factory_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `factory_id` (`factory_id`);

--
-- Indexes for table `green_credits`
--
ALTER TABLE `green_credits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `factory_id` (`factory_id`);

--
-- Indexes for table `planting_proofs`
--
ALTER TABLE `planting_proofs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `target_id` (`target_id`),
  ADD KEY `fk_planting_user` (`user_id`);

--
-- Indexes for table `pollution_data`
--
ALTER TABLE `pollution_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `factory_id` (`factory_id`);

--
-- Indexes for table `tree_targets`
--
ALTER TABLE `tree_targets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `factory_id` (`factory_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_credits`
--
ALTER TABLE `user_credits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `user_email_otps`
--
ALTER TABLE `user_email_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_consumed` (`user_id`,`consumed`);

--
-- Indexes for table `verifications`
--
ALTER TABLE `verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proof_id` (`proof_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `credit_transactions`
--
ALTER TABLE `credit_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `factories`
--
ALTER TABLE `factories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `factory_requests`
--
ALTER TABLE `factory_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `green_credits`
--
ALTER TABLE `green_credits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `planting_proofs`
--
ALTER TABLE `planting_proofs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `pollution_data`
--
ALTER TABLE `pollution_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tree_targets`
--
ALTER TABLE `tree_targets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `user_credits`
--
ALTER TABLE `user_credits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `user_email_otps`
--
ALTER TABLE `user_email_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `verifications`
--
ALTER TABLE `verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `factories`
--
ALTER TABLE `factories`
  ADD CONSTRAINT `fk_factory_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `factory_requests`
--
ALTER TABLE `factory_requests`
  ADD CONSTRAINT `factory_requests_ibfk_1` FOREIGN KEY (`factory_id`) REFERENCES `factories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `green_credits`
--
ALTER TABLE `green_credits`
  ADD CONSTRAINT `green_credits_ibfk_1` FOREIGN KEY (`factory_id`) REFERENCES `factories` (`id`);

--
-- Constraints for table `planting_proofs`
--
ALTER TABLE `planting_proofs`
  ADD CONSTRAINT `fk_planting_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `planting_proofs_ibfk_2` FOREIGN KEY (`target_id`) REFERENCES `tree_targets` (`id`);

--
-- Constraints for table `pollution_data`
--
ALTER TABLE `pollution_data`
  ADD CONSTRAINT `pollution_data_ibfk_1` FOREIGN KEY (`factory_id`) REFERENCES `factories` (`id`);

--
-- Constraints for table `tree_targets`
--
ALTER TABLE `tree_targets`
  ADD CONSTRAINT `tree_targets_ibfk_1` FOREIGN KEY (`factory_id`) REFERENCES `factories` (`id`);

--
-- Constraints for table `user_credits`
--
ALTER TABLE `user_credits`
  ADD CONSTRAINT `fk_usercredits_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_email_otps`
--
ALTER TABLE `user_email_otps`
  ADD CONSTRAINT `fk_otp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `verifications`
--
ALTER TABLE `verifications`
  ADD CONSTRAINT `verifications_ibfk_1` FOREIGN KEY (`proof_id`) REFERENCES `planting_proofs` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

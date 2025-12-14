-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Dec 14, 2025 at 03:56 PM
-- Server version: 8.0.40
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dompet_sesat`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(10) DEFAULT 0xF09F92B0,
  `color` varchar(7) DEFAULT '#6b7280',
  `type` enum('income','expense') NOT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `user_id`, `name`, `icon`, `color`, `type`, `is_default`, `created_at`) VALUES
(1, NULL, 'Makanan & Minuman', '🍴', '#4361ee', 'expense', 1, '2025-12-10 04:45:46'),
(2, NULL, 'Jajan', '🍔', '#f72585', 'expense', 1, '2025-12-10 04:45:46'),
(3, NULL, 'Token', '⚡', '#4cc9f0', 'expense', 1, '2025-12-10 04:45:46'),
(5, NULL, 'Transport', '🚗', '#fca311', 'expense', 1, '2025-12-10 04:45:46'),
(9, NULL, 'Entertainment', '🎬', '#9b5de5', 'expense', 1, '2025-12-10 04:45:46'),
(10, NULL, 'Kesehatan', '💊', '#00bbf9', 'expense', 1, '2025-12-10 04:45:46'),
(11, NULL, 'Pendidikan', '📚', '#f15bb5', 'expense', 1, '2025-12-10 04:45:46'),
(13, NULL, 'Gaji', '💰', '#10b981', 'income', 1, '2025-12-10 04:45:46'),
(14, NULL, 'Uang Saku', '🎁', '#3b82f6', 'income', 1, '2025-12-10 04:45:46'),
(15, NULL, 'Bonus', '🏆', '#8b5cf6', 'income', 1, '2025-12-10 04:45:46'),
(16, NULL, 'Investasi', '📈', '#f59e0b', 'income', 1, '2025-12-10 04:45:46'),
(20, 2, 'Makan', '🍚', '#4361ee', 'expense', 0, '2025-12-11 07:20:23'),
(21, 2, 'Lainnya', '💰', '#fca311', 'expense', 0, '2025-12-12 05:59:19'),
(22, 2, 'Pulsa', '📱', '#ff9f1c', 'expense', 0, '2025-12-12 06:03:20'),
(23, 1, 'Test Category', '💰', '#6b7280', 'expense', 0, '2025-12-12 06:08:54');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `category_id` int NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `date` date NOT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `category_id`, `type`, `description`, `amount`, `date`, `notes`, `created_at`, `updated_at`) VALUES
(9, 1, 1, 'expense', 'warteg tegal bahari', 20000.00, '2025-12-10', '', '2025-12-10 04:47:06', '2025-12-10 04:47:06'),
(10, 1, 13, 'income', 'gaji der', 10000000.00, '2025-12-01', '', '2025-12-10 04:48:35', '2025-12-10 04:49:30'),
(11, 1, 2, 'expense', 'Jajan lonte', 200000.00, '2025-12-03', '', '2025-12-10 04:50:29', '2025-12-10 04:50:48'),
(24, 2, 9, 'expense', 'ps', 4000.00, '2025-12-01', '', '2025-12-12 05:57:32', '2025-12-12 05:57:32'),
(25, 2, 20, 'expense', 'magelangan', 16000.00, '2025-12-12', '', '2025-12-12 05:57:49', '2025-12-12 05:57:49'),
(26, 2, 2, 'expense', 'liq & ct', 120000.00, '2025-12-12', '', '2025-12-12 05:58:10', '2025-12-12 05:58:10'),
(27, 2, 2, 'expense', 'kopi', 5000.00, '2025-12-01', '', '2025-12-12 05:58:38', '2025-12-12 05:58:38'),
(28, 2, 21, 'expense', 'spion, sen, lampu', 70000.00, '2025-12-12', '', '2025-12-12 05:59:19', '2025-12-12 05:59:19'),
(29, 2, 20, 'expense', 'nastel', 12000.00, '2025-12-12', '', '2025-12-12 05:59:36', '2025-12-12 05:59:36'),
(30, 2, 20, 'expense', 'naspad', 10000.00, '2025-12-03', '', '2025-12-12 06:00:00', '2025-12-12 06:00:00'),
(31, 2, 20, 'expense', 'ayam bali', 16000.00, '2025-12-04', '', '2025-12-12 06:00:23', '2025-12-12 06:00:23'),
(32, 2, 2, 'expense', 'mcd', 81000.00, '2025-12-04', '', '2025-12-12 06:00:47', '2025-12-12 06:00:47'),
(33, 2, 20, 'expense', 'warteg', 10000.00, '2025-12-05', '', '2025-12-12 06:01:15', '2025-12-12 06:01:15'),
(34, 2, 20, 'expense', 'nastel', 13500.00, '2025-12-05', '', '2025-12-12 06:01:49', '2025-12-12 06:01:49'),
(35, 2, 20, 'expense', 'magelangan', 13000.00, '2025-12-05', '', '2025-12-12 06:02:10', '2025-12-12 06:02:10'),
(36, 2, 21, 'expense', 'cukur', 40000.00, '2025-12-12', '', '2025-12-12 06:02:30', '2025-12-14 10:26:33'),
(37, 2, 20, 'expense', 'nastel', 10000.00, '2025-12-06', '', '2025-12-12 06:02:53', '2025-12-12 06:02:53'),
(39, 2, 14, 'income', 'dri mama', 200000.00, '2025-12-01', '', '2025-12-12 06:43:23', '2025-12-12 06:43:23'),
(40, 2, 14, 'income', 'dari mama lagi juga', 1800000.00, '2025-12-12', '', '2025-12-12 06:43:56', '2025-12-14 10:26:01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `monthly_budget` decimal(15,2) DEFAULT '1500000.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `email`, `password`, `role`, `monthly_budget`, `created_at`, `updated_at`) VALUES
(1, 'demo', 'Demo User', 'demo@example.com', '62cc2d8b4bf2d8728120d052163a77df', 'admin', 1500000.00, '2025-12-10 04:45:46', '2025-12-11 01:27:00'),
(2, 'rayy', 'Rayan User', 'urayan652@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'user', 1500000.00, '2025-12-10 04:55:39', '2025-12-10 10:36:55'),
(3, 'admin', 'Administrator', 'admin@dompetsesat.com', '0192023a7bbd73250516f069df18b500', 'admin', 0.00, '2025-12-10 10:34:26', '2025-12-10 10:34:26'),
(4, 'ngetes', 'Test User', 't@gmail.com', 'cc03e747a6afbbcbf8be7668acfebee5', 'user', 1500000.00, '2025-12-10 12:07:52', '2025-12-10 12:07:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categories_ibfk_1` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

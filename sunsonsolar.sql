-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 22, 2026 at 02:02 PM
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
-- Database: `sunsonsolar`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) NOT NULL,
  `birthdate` date NOT NULL,
  `gender` varchar(30) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(30) NOT NULL,
  `address` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('customer','employee','admin') NOT NULL DEFAULT 'customer',
  `department` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `middle_name`, `birthdate`, `gender`, `email`, `phone_number`, `address`, `username`, `password_hash`, `role`, `department`, `created_at`) VALUES
(1, 'Katherine', 'Sinagaraw', 'Kat', '2026-09-22', 'Female', 'kat@sunsonsolar.com', '+639123456789', 'Pasig City', 'Kittykat16', '$2y$10$RWX0Da/Amu2ZNmyP53yf5e7D/sxEmAo8hkTHUNaQ8T3rsj9LWiTvW', 'admin', NULL, '2026-09-22 11:43:40'),
(2, 'Sol', 'Solis', 'Soli', '2026-09-22', 'Female', 'solsolis@sunsonsolar.com', '+639987456321', 'Pasig City', 'admin', '$2y$10$Q.9NH0YvLjcoKEJ/ph/yHegWXf9.sG/7wtIj/DpsfX01C03ezg3Xq', 'admin', NULL, '2026-09-22 11:43:40'),
(3, 'Trestan', 'Pacalioga', 'Allas', '2005-12-30', 'male', 'trestanallas1230@gmail.com', '+639945015944', '2052', 'tantan', '$2y$10$y./OOq8ywt9QdkbqGoh8YeeR4CPQ6bkJYk9eG/X5bTJhqS3hmlOUu', 'customer', NULL, '2026-09-22 11:45:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

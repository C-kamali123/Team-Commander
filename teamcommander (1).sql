-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 21, 2026 at 08:12 AM
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
-- Database: `teamcommander`
--

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `task_name` varchar(150) NOT NULL,
  `task_description` text NOT NULL,
  `task_date` date NOT NULL,
  `due_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('completed','not_completed') DEFAULT 'not_completed',
  `assigned_to` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `task_name`, `task_description`, `task_date`, `due_date`, `created_at`, `status`, `assigned_to`) VALUES
(1, 'Design Login Page', 'Create UI and backend for login page', '2025-03-15', '2025-03-20', '2025-12-13 03:44:40', 'completed', NULL),
(2, 'Design Login Page', 'Create UI and backend for login page', '2025-03-15', '2025-03-20', '2025-12-13 04:09:00', 'not_completed', NULL),
(3, 'figma', 'create', '2026-01-03', '2026-01-04', '2026-01-03 14:38:24', 'not_completed', 9),
(4, 'app', 'aksvejd', '2026-01-06', '2026-01-07', '2026-01-06 17:31:58', 'not_completed', NULL),
(5, 'flj', 'nckh', '2026-01-06', '2026-01-07', '2026-01-06 17:34:46', 'not_completed', NULL),
(6, 'nbv', 'bgf', '2026-01-06', '2026-01-06', '2026-01-06 17:43:22', 'not_completed', 9),
(7, 'snebsbs', 'jsnsj', '2026-01-06', '2026-01-06', '2026-01-06 17:46:31', 'not_completed', 9),
(8, '7gv', 'hih', '2026-01-06', '2026-01-06', '2026-01-06 17:47:04', 'not_completed', 9),
(9, 'hi7v', 'gif6f', '2026-01-06', '2026-01-06', '2026-01-06 17:47:34', 'not_completed', 8),
(10, 'ejjsdjdj', 'hdjsje', '2026-01-06', '2026-01-06', '2026-01-06 17:47:55', 'completed', 9),
(11, 'djdb', 'hsjs', '2026-01-06', '2026-01-06', '2026-01-06 18:01:26', 'completed', 9),
(12, 'review', 'review eppudu', '2026-01-07', '2026-01-07', '2026-01-07 02:53:05', 'completed', 9),
(13, 'shdi', 'jdhd', '2026-01-07', '2026-01-07', '2026-01-07 07:08:22', 'completed', 9);

-- --------------------------------------------------------

--
-- Table structure for table `events_schedule`
--

CREATE TABLE `events_schedule` (
  `id` int(11) NOT NULL,
  `event_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `event_place` varchar(150) NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('completed','not_completed') DEFAULT 'not_completed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events_schedule`
--

INSERT INTO `events_schedule` (`id`, `event_name`, `description`, `event_place`, `event_date`, `event_time`, `created_at`, `status`) VALUES
(1, 'Team Meeting', NULL, 'Conference Room A', '2025-03-18', '10:30:00', '2025-12-13 04:07:54', 'completed'),
(2, 'feast', NULL, 'nalli arangam', '2026-01-06', '09:00:00', '2026-01-05 03:25:21', 'not_completed'),
(3, 'gcgkn', 'vnbxd', 'hxbmv', '2026-01-06', '23:13:00', '2026-01-06 17:43:50', 'not_completed');

-- --------------------------------------------------------

--
-- Table structure for table `event_participants`
--

CREATE TABLE `event_participants` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_participants`
--

INSERT INTO `event_participants` (`id`, `event_id`, `user_id`, `created_at`) VALUES
(1, 3, 9, '2026-01-06 17:43:50');

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('ADMIN','MEMBER') DEFAULT 'MEMBER',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `OTP` int(11) NOT NULL,
  `role` enum('team_leader','team_member') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `OTP`, `role`, `created_at`) VALUES
(1, 'Arun Kumar', 'arun@example.com', '$2y$10$E2KZ08bIwULFNfg.JZ.rS.wUnxLYFtvSIc4SMLZBCQUnEIX/OBdmy', 0, 'team_leader', '2025-12-12 06:56:13'),
(2, 'Ravi Teja', 'ravi@example.com', '$2y$10$HrI/YBAmrlEUOx7LcEganOtlk2TJCugkiin721nFbLe8rKbi4gul2', 0, 'team_member', '2025-12-12 06:57:20'),
(3, 'kamal', 'kamal@example.com', 'kamal123', 0, 'team_member', '2025-12-12 07:03:24'),
(4, 'Kamali Chinni', 'chinnikamali@gmail.com', '$2y$10$7ijHiG..0Uq2oRV.DTZJDu87t9GVgN2RQpy/XzRlyUELGTsGvtQVG', 942190, 'team_leader', '2026-01-03 07:49:47'),
(5, 'chinni', 'chinni@gmail.com', '147258', 0, 'team_leader', '2026-01-03 07:57:04'),
(6, 'bhavya', 'bhavya@gmail.com', '123456', 0, 'team_member', '2026-01-03 08:15:43'),
(7, 'manasa', 'manasa@gmail.com', '123456', 0, 'team_member', '2026-01-03 08:24:21'),
(8, 'kaveri', 'edalakaveri@gmail.com', '258036', 197468, 'team_member', '2026-01-03 08:32:32'),
(9, 'anu', 'anu@gmail.com', 'member123', 0, 'team_member', '2026-01-03 09:21:19'),
(10, 'mahesh', 'kommumaheshmahesh24@gmail.com', '258036', 599143, 'team_leader', '2026-01-06 09:33:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events_schedule`
--
ALTER TABLE `events_schedule`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_participants`
--
ALTER TABLE `event_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_participant` (`event_id`,`user_id`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `events_schedule`
--
ALTER TABLE `events_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `event_participants`
--
ALTER TABLE `event_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

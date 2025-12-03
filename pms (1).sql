-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2025 at 01:35 PM
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
-- Database: `pms`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_attendance`
--

CREATE TABLE `tbl_attendance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `check_in` datetime DEFAULT NULL,
  `check_out` datetime DEFAULT NULL,
  `total_minutes` int(11) DEFAULT 0,
  `status` varchar(15) DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_attendance`
--

INSERT INTO `tbl_attendance` (`id`, `user_id`, `date`, `check_in`, `check_out`, `total_minutes`, `status`, `created_date`, `updated_date`) VALUES
(8, 12, '2025-12-02', '2025-12-02 17:22:39', NULL, 0, '', '2025-12-02 11:52:39', '2025-12-02 11:52:39');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_company_information`
--

CREATE TABLE `tbl_company_information` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `lat` varchar(12) NOT NULL,
  `long` varchar(12) NOT NULL,
  `open_time` time NOT NULL,
  `close_time` time NOT NULL,
  `grace_period_mins` int(11) NOT NULL DEFAULT 0,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_company_information`
--

INSERT INTO `tbl_company_information` (`id`, `name`, `lat`, `long`, `open_time`, `close_time`, `grace_period_mins`, `created_date`) VALUES
(1, 'G-infosoft', '23.2360753', '77.4286886', '10:01:00', '19:00:00', 15, '2025-11-30 13:32:58');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_projects`
--

CREATE TABLE `tbl_projects` (
  `id` int(11) NOT NULL,
  `project_name` varchar(100) DEFAULT NULL,
  `color` varchar(10) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_projects`
--

INSERT INTO `tbl_projects` (`id`, `project_name`, `color`, `created_by`, `created_date`, `updated_date`) VALUES
(1, 'new', '#10b981', 12, '2025-12-01 05:31:52', '2025-12-01 05:31:52'),
(2, 'my', '#f59e0b', 12, '2025-12-01 11:14:35', '2025-12-01 11:14:35');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_projects_tracking`
--

CREATE TABLE `tbl_projects_tracking` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `duration` varchar(10) DEFAULT NULL,
  `desc` text DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_projects_tracking`
--

INSERT INTO `tbl_projects_tracking` (`id`, `project_id`, `user_id`, `start_time`, `end_time`, `duration`, `desc`, `created_date`, `updated_date`) VALUES
(4, 2, 12, '2025-12-02 10:00:00', '2025-12-02 12:30:00', '150', 'hii', '2025-12-02 11:53:49', '2025-12-02 11:53:49'),
(5, 2, 12, '2025-12-02 10:00:00', '2025-12-02 12:30:00', '150', '', '2025-12-02 12:01:29', '2025-12-02 12:01:29'),
(6, 1, 12, '2025-12-02 10:01:00', '2025-12-02 12:30:00', '149', 'NEW BUTTON ADD', '2025-12-02 12:13:37', '2025-12-02 12:13:37');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_role_master`
--

CREATE TABLE `tbl_role_master` (
  `id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_role_master`
--

INSERT INTO `tbl_role_master` (`id`, `role`, `created_date`, `updated_date`) VALUES
(1, 'SuperAdmin', '2025-11-25 07:15:34', '2025-11-25 07:15:34'),
(2, 'User', '2025-11-25 07:15:34', '2025-11-25 07:15:34');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL COMMENT 'tbl_role_master',
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` text DEFAULT NULL,
  `photo` text DEFAULT NULL,
  `mobile` varchar(12) DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`id`, `role_id`, `name`, `email`, `password`, `photo`, `mobile`, `created_date`, `updated_date`) VALUES
(3, 1, 'Admin', 'admin@gmail.com', '$2y$10$IHjwVGvc0wBDAdRS1Lwg9ecUd5dd19nx9ZOjloDIU6S2DKAKUmRyW', NULL, '6263294002', '2025-11-25 08:30:52', '2025-12-01 08:52:02'),
(12, 2, 'vishal ghorse', 'vishalghorse3@gmail.com', '$2y$10$YbnqbFxuzaUjjm57CohmmOhBmdEWUFlc8oNukjhXXXSEhM5iNwsVm', '', '6263294002', '2025-11-30 17:28:19', '2025-11-30 17:28:19'),
(13, 2, 'Shipra Daa', 'shipra@ginfosoft.com', '$2y$10$rVOJ2TaWTONq4S/KZBQfh.n7ezrPHe9o9fBi4WJPoRgO1nEuv.LyC', '', '', '2025-11-30 17:50:18', '2025-11-30 17:50:18'),
(14, 2, 'new user ', 'new@new.com', '$2y$10$57vV7Y8JBBKcB9cwlnZwxetpMDSfhedJiGxmp7OQYh0.vQSxCpw1.', '', '+91626329400', '2025-12-01 06:50:55', '2025-12-01 06:50:55'),
(15, 2, 'New User 1', 'new@new1.com', '$2y$10$ReUUt1fpGi/91hgA7kfa5OdTPJt4hPJVwLErvmwgy7sU34DtRI2r.', '', '+91626329400', '2025-12-01 06:54:16', '2025-12-01 06:54:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_attendance`
--
ALTER TABLE `tbl_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`date`),
  ADD UNIQUE KEY `user_id_2` (`user_id`,`date`);

--
-- Indexes for table `tbl_company_information`
--
ALTER TABLE `tbl_company_information`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_projects`
--
ALTER TABLE `tbl_projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_projects_tracking`
--
ALTER TABLE `tbl_projects_tracking`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_role_master`
--
ALTER TABLE `tbl_role_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_attendance`
--
ALTER TABLE `tbl_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_company_information`
--
ALTER TABLE `tbl_company_information`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_projects`
--
ALTER TABLE `tbl_projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_projects_tracking`
--
ALTER TABLE `tbl_projects_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_role_master`
--
ALTER TABLE `tbl_role_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

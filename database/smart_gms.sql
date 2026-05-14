-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2026 at 05:00 AM
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
-- Database: `smart_gms`
--

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `citizen_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `area` varchar(100) NOT NULL,
  `place` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('pending','in_progress','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `citizen_id`, `full_name`, `phone`, `area`, `place`, `description`, `image`, `status`, `created_at`) VALUES
(1, 2, 'citizen', '1234567890', 'Karur Town', 'Bus stand opposite', 'Garbage has not been collected in our street for the past three days, causing a bad smell and hygiene issues. Kindly arrange immediate cleaning.', NULL, 'resolved', '2026-02-25 07:44:37'),
(2, 3, 'citizen1', '0987654321', 'Vengamedu', 'vengamedu Bus Stop', 'The garbage bin near our area is overflowing and attracting stray animals and insects. Please clear the waste as soon as possible.', 'complaint_1772005713_874.jpg', 'pending', '2026-02-25 07:48:33'),
(3, 4, 'citizen2', '1234512345', 'Kulithalai', 'government hospital , kulithalai', 'Waste is being dumped regularly on the roadside without proper disposal. Requesting the concerned authorities to take action and maintain cleanliness.', 'complaint_1772008478_888.jpg', 'in_progress', '2026-02-25 08:34:38');

-- --------------------------------------------------------

--
-- Table structure for table `complaint_responses`
--

CREATE TABLE `complaint_responses` (
  `id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `response` text NOT NULL,
  `responded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaint_responses`
--

INSERT INTO `complaint_responses` (`id`, `complaint_id`, `admin_id`, `response`, `responded_at`) VALUES
(1, 3, 1, 'we will quickly take care of that. thanks for you report.', '2026-02-25 18:23:36'),
(2, 1, 1, 'we now cleared everything . thanks for your report.', '2026-02-26 16:36:58'),
(3, 1, 1, 'it takes some tome', '2026-03-16 01:32:24'),
(4, 1, 1, 'we solved the problem', '2026-03-16 01:32:49');

-- --------------------------------------------------------

--
-- Table structure for table `dustbins`
--

CREATE TABLE `dustbins` (
  `id` int(11) NOT NULL,
  `location_name` varchar(150) NOT NULL,
  `area` varchar(100) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `dustbin_type` enum('general','recyclable','organic') DEFAULT 'general',
  `status` enum('active','full','maintenance') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dustbins`
--

INSERT INTO `dustbins` (`id`, `location_name`, `area`, `latitude`, `longitude`, `dustbin_type`, `status`, `created_at`) VALUES
(7, 'K.K. Nagar Park', 'North Zone', 10.96500000, 78.07500000, 'recyclable', 'active', '2026-02-25 07:16:20'),
(8, 'Vengamedu Junction', 'North Zone', 10.97000000, 78.06900000, 'organic', 'active', '2026-02-25 07:16:20'),
(9, 'Manmangalam Market', 'South Zone', 10.95200000, 78.08000000, 'general', 'active', '2026-02-25 07:16:20'),
(10, 'Thanthonimalai Temple', 'South Zone', 10.94800000, 78.08500000, 'recyclable', 'active', '2026-02-25 07:16:20'),
(11, 'Jawahar Nagar School', 'South Zone', 10.95500000, 78.07200000, 'organic', 'active', '2026-02-25 07:16:20'),
(12, 'Kovilpalayam Main Road', 'East Zone', 10.96000000, 78.09000000, 'general', 'active', '2026-02-25 07:16:20'),
(13, 'Kadavur Town Center', 'East Zone', 10.94000000, 78.11000000, 'recyclable', 'active', '2026-02-25 07:16:20'),
(14, 'Aravakurichi Bus Stop', 'West Zone', 10.98000000, 78.05000000, 'general', 'active', '2026-02-25 07:16:20'),
(15, 'Kulithalai Market', 'West Zone', 10.93000000, 78.03000000, 'organic', 'active', '2026-02-25 07:16:20');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `area` varchar(100) NOT NULL,
  `place` varchar(100) NOT NULL,
  `collection_day` varchar(20) NOT NULL,
  `collection_time` time NOT NULL,
  `vehicle_number` varchar(30) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `area`, `place`, `collection_day`, `collection_time`, `vehicle_number`, `notes`, `created_at`) VALUES
(7, 'North Zone', 'Karur Town', 'Monday', '07:00:00', 'TN-23-AA-1111', 'Morning collection', '2026-02-25 07:16:07'),
(8, 'North Zone', 'K.K. Nagar', 'Wednesday', '07:30:00', 'TN-23-AA-1111', 'Morning collection', '2026-02-25 07:16:07'),
(9, 'North Zone', 'Vengamedu', 'Friday', '07:00:00', 'TN-23-AA-1111', 'Morning collection', '2026-02-25 07:16:07'),
(10, 'South Zone', 'Manmangalam', 'Tuesday', '06:30:00', 'TN-23-BB-2222', 'Early morning collection', '2026-02-25 07:16:07'),
(11, 'South Zone', 'Thanthonimalai', 'Thursday', '07:00:00', 'TN-23-BB-2222', 'Morning collection', '2026-02-25 07:16:07'),
(12, 'South Zone', 'Jawahar Nagar', 'Saturday', '07:30:00', 'TN-23-BB-2222', 'Morning collection', '2026-02-25 07:16:07'),
(13, 'East Zone', 'Kovilpalayam', 'Monday', '08:00:00', 'TN-23-CC-3333', 'Morning collection', '2026-02-25 07:16:07'),
(14, 'East Zone', 'Kadavur', 'Wednesday', '08:30:00', 'TN-23-CC-3333', 'Morning collection', '2026-02-25 07:16:07'),
(15, 'East Zone', 'Krishnarayapuram', 'Monday', '08:00:00', 'TN-23-CC-3333', 'Morning collection', '2026-02-25 07:16:07'),
(16, 'West Zone', 'Aravakurichi', 'Tuesday', '07:00:00', 'TN-23-DD-4444', 'Morning collection', '2026-02-25 07:16:07'),
(17, 'West Zone', 'Kulithalai', 'Thursday', '07:30:00', 'TN-23-DD-4444', 'Morning collection', '2026-02-25 07:16:07'),
(18, 'West Zone', 'Pallapatti', 'Saturday', '08:00:00', 'TN-23-DD-4444', 'Morning collection', '2026-02-25 07:16:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `area` varchar(100) DEFAULT NULL,
  `role` enum('admin','citizen') NOT NULL DEFAULT 'citizen',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `area`, `role`, `created_at`) VALUES
(1, 'System Administrator', 'admin@smartgms.com', '$2y$10$2iu7Q6Ra3BQeaCDuJZPrnejN9J/fdj1K7eJuY1S9.voNtIR46M..e', NULL, NULL, 'admin', '2026-02-24 15:00:43'),
(2, 'citizen', 'citizen@gmail.com', '$2y$10$flLJDbUGMm4qUsvI5Y.2leQHfIepr17jXtiaq7k4xHbN5GuYTl1OK', '1234567890', 'chennai', 'citizen', '2026-02-24 17:00:48'),
(3, 'citizen1', 'citizen1@gmail.com', '$2y$10$78xbppvPcSSQIFquX1QSu.oZPpUAoDWwKAtVejICteNa/0V5sMbMS', '0987654321', 'Anna Nagar', 'citizen', '2026-02-25 07:45:50'),
(4, 'citizen2', 'citizen2@gmail.com', '$2y$10$1X/nbZaiY9I07XaYLQkFH.bXAPsgisw8zKhhsxlZzvJGqTfaUkvRO', '1234512345', 'kulithali', 'citizen', '2026-02-25 08:28:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `citizen_id` (`citizen_id`);

--
-- Indexes for table `complaint_responses`
--
ALTER TABLE `complaint_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaint_id` (`complaint_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `dustbins`
--
ALTER TABLE `dustbins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
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
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `complaint_responses`
--
ALTER TABLE `complaint_responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `dustbins`
--
ALTER TABLE `dustbins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`citizen_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `complaint_responses`
--
ALTER TABLE `complaint_responses`
  ADD CONSTRAINT `complaint_responses_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaint_responses_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

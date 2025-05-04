-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 04, 2025 at 12:07 PM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tailor_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
CREATE TABLE IF NOT EXISTS `clients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `dob` date DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `name`, `dob`, `phone`, `email`, `address`) VALUES
(1, 'John Doe', '1990-05-15', '123-456-7890', 'john.doe@example.com', '123 Main St'),
(2, 'Jane Smith', '1985-10-20', '987-654-3210', 'jane.smith@example.com', '456 Oak Ave'),
(3, 'adarsh', '2025-03-03', '1111111111', 'g@gmail.com', 'vapi');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int DEFAULT NULL,
  `cloth_type` varchar(50) DEFAULT NULL,
  `material` varchar(50) DEFAULT NULL,
  `shirt_neck` varchar(50) DEFAULT NULL,
  `shirt_shoulder` varchar(50) DEFAULT NULL,
  `shirt_length` varchar(50) DEFAULT NULL,
  `shirt_arm` varchar(50) DEFAULT NULL,
  `shirt_wrist` varchar(50) DEFAULT NULL,
  `shirt_forearm` varchar(50) DEFAULT NULL,
  `pant_length` varchar(50) DEFAULT NULL,
  `pant_waist` varchar(50) DEFAULT NULL,
  `pant_hip` varchar(50) DEFAULT NULL,
  `pant_thai` varchar(50) DEFAULT NULL,
  `order_date` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `staff_assigned_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `staff_assigned_id` (`staff_assigned_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
CREATE TABLE IF NOT EXISTS `staff` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `dob` date DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text,
  `staff_id` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(10) DEFAULT 'staff',
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_id` (`staff_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `name`, `dob`, `phone`, `email`, `address`, `staff_id`, `password`, `role`) VALUES
(1, 'Admin User', '1980-01-01', '111-222-3333', 'admin@example.com', 'admin', 'admin', 'admin', 'admin'),
(2, 'Staff Member', '1995-03-10', '444-555-6666', 'staff@example.com', 'Staff', 'staff456', 'staff', 'staff');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 12, 2026 at 08:17 AM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ai_business_intelligence`
--

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
CREATE TABLE IF NOT EXISTS `branches` (
  `branch_id` int NOT NULL AUTO_INCREMENT,
  `branch_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`branch_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`branch_id`, `branch_name`, `location`) VALUES
(1, 'Tenkasi', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `customer_id` int NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`customer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `customer_name`, `phone`, `email`, `location`) VALUES
(1, 'Kumar', NULL, NULL, NULL),
(2, 'Ravi', '9876543210', 'ravi@gmail.com', 'Tenkasi'),
(3, 'Arun', NULL, NULL, NULL),
(4, 'Suresh', '9876543210', 'suresh@gmail.com', 'Tenkasi'),
(5, 'Test Customer', NULL, NULL, NULL),
(6, 'Veera', '8978660988', 'veerak@gmail.com', 'Tenkasi');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `product_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_price` decimal(12,2) DEFAULT '0.00',
  PRIMARY KEY (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `category`, `unit_price`) VALUES
(1, 'Laptop', NULL, 0.00),
(2, 'Mouse', 'Electronics', 500.00),
(3, 'laptops', NULL, 0.00),
(4, 'Keyboard', NULL, 0.00),
(5, 'Headphones', 'Electronics', 1500.00),
(6, 'Charger', 'Electronics', 500.00);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE IF NOT EXISTS `transactions` (
  `transaction_id` int NOT NULL AUTO_INCREMENT,
  `branch_id` int NOT NULL,
  `customer_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `transaction_type` enum('Sale','Purchase','Expense') COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_date` date NOT NULL,
  `quantity` int DEFAULT '1',
  `amount` decimal(12,2) NOT NULL,
  `payment_status` enum('Paid','Pending','Partial') COLLATE utf8mb4_unicode_ci DEFAULT 'Paid',
  `input_method` enum('Manual','Text','Voice','Document') COLLATE utf8mb4_unicode_ci DEFAULT 'Manual',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`transaction_id`),
  KEY `branch_id` (`branch_id`),
  KEY `customer_id` (`customer_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `branch_id`, `customer_id`, `product_id`, `transaction_type`, `transaction_date`, `quantity`, `amount`, `payment_status`, `input_method`, `description`) VALUES
(1, 1, 1, 1, 'Sale', '2026-09-03', 2, 80000.00, 'Paid', 'Manual', 'Website Sale'),
(2, 1, NULL, 1, 'Purchase', '2026-09-03', 5, 50000.00, 'Paid', 'Manual', 'Purchase from ABC Traders'),
(3, 1, NULL, NULL, 'Expense', '2026-09-03', 1, 5000.00, 'Paid', 'Manual', 'Electricity'),
(4, 1, 1, 3, 'Sale', '2026-09-04', 2, 80000.00, 'Paid', 'Text', 'AI Assistant Transaction'),
(5, 1, 1, 3, 'Sale', '2026-09-04', 2, 80000.00, 'Paid', 'Text', 'AI Assistant Transaction'),
(6, 1, NULL, 3, 'Purchase', '2026-09-04', 5, 50000.00, 'Paid', 'Text', 'AI Assistant Transaction'),
(7, 1, NULL, NULL, 'Expense', '2026-09-04', NULL, 5000.00, 'Paid', 'Text', 'AI Assistant Transaction'),
(8, 1, NULL, NULL, 'Expense', '2026-09-04', NULL, 5000.00, 'Paid', 'Text', 'AI Assistant Transaction'),
(9, 1, 1, 3, 'Sale', '2026-09-04', 2, 80000.00, 'Paid', 'Text', 'AI Assistant Transaction'),
(10, 1, 1, 1, 'Sale', '2026-09-04', 2, 80000.00, 'Paid', 'Document', 'AI Document Upload'),
(11, 1, 2, 3, 'Sale', '2026-09-05', 3, 120000.00, 'Paid', 'Text', 'AI Assistant Transaction'),
(12, 1, 3, 4, 'Sale', '2026-09-05', 2, 2000.00, 'Paid', 'Manual', 'Website Sale'),
(13, 1, NULL, 4, 'Purchase', '2026-09-05', 5, 5000.00, 'Paid', 'Manual', 'Purchase from XYZ Traders'),
(14, 1, NULL, NULL, 'Expense', '2026-09-05', 1, 1000.00, 'Paid', 'Manual', 'Internet Bill'),
(15, 1, 4, NULL, 'Sale', '2026-09-05', NULL, 3000.00, 'Paid', 'Text', 'AI Assistant Transaction'),
(16, 1, 2, 3, 'Sale', '2026-09-05', 3, 120000.00, 'Paid', 'Text', 'AI Assistant Transaction'),
(17, 1, 2, 1, 'Sale', '2026-09-07', NULL, 50000.00, 'Paid', 'Text', 'AI Assistant Transaction'),
(18, 1, 2, 1, 'Sale', '2026-09-07', 0, 50000.00, 'Paid', 'Text', 'AI Assistant Transaction'),
(19, 1, 2, 1, 'Sale', '2026-09-07', 1, 50000.00, 'Paid', 'Text', 'AI Assistant Transaction'),
(20, 1, 1, 1, 'Sale', '2026-09-07', 2, 80000.00, 'Paid', 'Document', 'AI Document Upload'),
(21, 1, 2, 1, 'Sale', '2026-09-07', 1, 50000.00, 'Paid', 'Voice', 'AI Assistant Transaction'),
(22, 1, 3, 5, 'Sale', '2026-09-08', 2, 3000.00, 'Paid', 'Manual', 'Website Sale'),
(23, 1, NULL, 4, 'Purchase', '2026-09-08', 3, 3000.00, 'Paid', 'Manual', 'Purchase from ABC Traders'),
(24, 1, NULL, NULL, 'Expense', '2026-09-08', 1, 1500.00, 'Paid', 'Manual', 'Electricity Bill'),
(25, 1, 3, 3, 'Sale', '2026-09-08', 2, 60000.00, 'Paid', 'Text', 'AI Assistant Transaction'),
(26, 1, 2, 1, 'Sale', '2026-09-08', 1, 50000.00, 'Paid', 'Voice', 'AI Assistant Transaction'),
(27, 1, 1, 1, 'Sale', '2026-09-08', 2, 80000.00, 'Paid', 'Document', 'AI Document Upload'),
(28, 1, 5, 1, 'Sale', '2026-09-09', 1, 75000.00, 'Paid', 'Manual', 'Website Sale'),
(29, 1, 3, 1, 'Sale', '2026-09-09', 1, 60000.00, 'Paid', 'Voice', 'AI Assistant Transaction'),
(30, 1, 1, 1, 'Sale', '2026-09-09', 2, 80000.00, 'Paid', 'Document', 'AI Document Upload'),
(31, 1, 3, 4, 'Sale', '2026-09-10', 1, 3000.00, 'Paid', 'Manual', 'Website Sale'),
(32, 1, 3, 4, 'Sale', '2026-09-10', 1, 3000.00, 'Paid', 'Manual', 'Website Sale'),
(33, 1, 3, 4, 'Sale', '2026-09-10', 1, 3000.00, 'Paid', 'Manual', 'Website Sale'),
(34, 1, NULL, 4, 'Purchase', '2026-09-10', 5, 12000.00, 'Paid', 'Manual', 'Purchase from XYZ Traders'),
(35, 1, NULL, NULL, 'Expense', '2026-09-10', 1, 2000.00, 'Paid', 'Manual', 'Internet Bill'),
(36, 1, 1, 3, 'Sale', '2026-09-10', 2, 80000.00, 'Paid', 'Text', 'AI Assistant Transaction'),
(37, 1, 1, 3, 'Sale', '2026-09-10', 2, 80000.00, 'Paid', 'Text', 'AI Assistant Transaction'),
(38, 1, 1, 1, 'Sale', '2026-09-10', 2, 80000.00, 'Paid', 'Document', 'AI Document Upload'),
(39, 1, 1, 1, 'Sale', '2026-09-10', 2, 80000.00, 'Paid', 'Document', 'AI Document Upload'),
(40, 1, NULL, 1, 'Sale', '2026-09-10', 1, 60000.00, 'Paid', 'Voice', 'AI Assistant Transaction');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

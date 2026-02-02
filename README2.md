-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: Jan 18, 2026 at 06:35 PM
-- Server version: 12.1.2-MariaDB-ubu2404
-- PHP Version: 8.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `webstore`
--

-- --------------------------------------------------------

--
-- Table structure for table `orderItems`
--

CREATE TABLE `orderItems` (
  `OrderItemId` int(11) NOT NULL,
  `OrderId` int(11) NOT NULL,
  `ProductId` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `PriceAtPurchase` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `orderItems`
--

INSERT INTO `orderItems` (`OrderItemId`, `OrderId`, `ProductId`, `Quantity`, `PriceAtPurchase`) VALUES
(1, 3, 13, 15, 24900),
(2, 3, 15, 6000, 11900),
(3, 3, 17, 2, 234234),
(4, 4, 1, 1, 199900),
(5, 4, 2, 2, 154950),
(6, 4, 3, 3, 45000),
(7, 5, 13, 2, 24900),
(8, 5, 14, 5, 64900),
(9, 5, 15, 7, 11900),
(10, 5, 17, 1, 234234),
(11, 6, 1, 7, 199900);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `OrderId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Address` text NOT NULL,
  `PaymentMethod` varchar(50) DEFAULT NULL,
  `TotalAmount` int(11) DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`OrderId`, `UserId`, `Address`, `PaymentMethod`, `TotalAmount`, `CreatedAt`) VALUES
(3, 6, 'Home 123, Amsterdam', 'monopoly_money', 72241968, '2026-01-13 15:10:28'),
(4, 6, 'Home 123, Amsterdam', 'monopoly_money', 644800, '2026-01-13 16:27:14'),
(5, 2, '1234, Main Street, Amsterdam, Netherlands', 'monopoly_money', 691834, '2026-01-16 13:59:35'),
(6, 6, 'ad ada sd', 'monopoly_money', 1399300, '2026-01-16 15:55:27');

-- --------------------------------------------------------

--
-- Table structure for table `productRating`
--

CREATE TABLE `productRating` (
  `ProductId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Rating` int(11) DEFAULT NULL CHECK (`Rating` >= 1 and `Rating` <= 5),
  `Review` text DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `productRating`
--

INSERT INTO `productRating` (`ProductId`, `UserId`, `Rating`, `Review`, `CreatedAt`) VALUES
(1, 1, 3, 'Great performance, handles my tasks easily.', '2026-01-04 14:41:27'),
(1, 2, 5, 'best', '2026-01-16 21:08:52'),
(1, 3, 4, 'Great performance, handles my tasks easily.', '2025-12-29 14:41:27'),
(1, 4, 3, 'Great performance, handles my tasks easily.', '2026-01-13 14:41:27'),
(1, 5, 3, 'Great performance, handles my tasks easily.', '2025-12-24 14:41:27'),
(1, 6, 5, 'Great performance, handles my tasks easily.', '2025-12-29 14:41:27'),
(1, 7, 4, 'Great performance, handles my tasks easily.', '2026-01-03 14:41:27'),
(1, 8, 3, 'Great performance, handles my tasks easily.', '2025-12-18 14:41:27'),
(2, 1, 3, 'Great performance, handles my tasks easily.', '2026-01-16 14:41:27'),
(2, 2, 4, 'Great performance, handles my tasks easily.', '2026-01-10 14:41:27'),
(2, 3, 5, 'Great performance, handles my tasks easily.', '2026-01-08 14:41:27'),
(2, 4, 3, 'Great performance, handles my tasks easily.', '2026-01-08 14:41:27'),
(2, 5, 3, 'Great performance, handles my tasks easily.', '2025-12-27 14:41:27'),
(2, 6, 4, 'Great performance, handles my tasks easily.', '2025-12-28 14:41:27'),
(2, 7, 4, 'Great performance, handles my tasks easily.', '2025-12-18 14:41:27'),
(2, 8, 3, 'Great performance, handles my tasks easily.', '2025-12-20 14:41:27'),
(3, 1, 3, 'Great performance, handles my tasks easily.', '2025-12-27 14:41:27'),
(3, 2, 3, 'Great performance, handles my tasks easily.', '2025-12-29 14:41:27'),
(3, 3, 5, 'Great performance, handles my tasks easily.', '2025-12-27 14:41:27'),
(3, 4, 3, 'Great performance, handles my tasks easily.', '2026-01-12 14:41:27'),
(3, 5, 3, 'Great performance, handles my tasks easily.', '2025-12-29 14:41:27'),
(3, 6, 3, 'Great performance, handles my tasks easily.', '2026-01-04 14:41:27'),
(3, 7, 5, 'Great performance, handles my tasks easily.', '2026-01-02 14:41:27'),
(3, 8, 4, 'Great performance, handles my tasks easily.', '2026-01-11 14:41:27'),
(4, 1, 3, 'Great performance, handles my tasks easily.', '2026-01-13 14:41:27'),
(4, 2, 4, 'Great performance, handles my tasks easily.', '2025-12-21 14:41:27'),
(4, 3, 4, 'Great performance, handles my tasks easily.', '2025-12-23 14:41:27'),
(4, 4, 4, 'Great performance, handles my tasks easily.', '2026-01-04 14:41:27'),
(4, 5, 4, 'Great performance, handles my tasks easily.', '2025-12-27 14:41:27'),
(4, 6, 3, 'Great performance, handles my tasks easily.', '2026-01-02 14:41:27'),
(4, 7, 4, 'Great performance, handles my tasks easily.', '2025-12-28 14:41:27'),
(4, 8, 5, 'Great performance, handles my tasks easily.', '2025-12-20 14:41:27'),
(5, 1, 4, 'Amazing picture and sound quality.', '2026-01-04 14:41:27'),
(5, 2, 3, 'Amazing picture and sound quality.', '2025-12-23 14:41:27'),
(5, 3, 4, 'Amazing picture and sound quality.', '2025-12-22 14:41:27'),
(5, 4, 5, 'Amazing picture and sound quality.', '2025-12-25 14:41:27'),
(5, 5, 3, 'Amazing picture and sound quality.', '2026-01-05 14:41:27'),
(5, 6, 4, 'Amazing picture and sound quality.', '2025-12-31 14:41:27'),
(5, 7, 3, 'Amazing picture and sound quality.', '2026-01-10 14:41:27'),
(5, 8, 4, 'Amazing picture and sound quality.', '2025-12-18 14:41:27'),
(6, 1, 4, 'Amazing picture and sound quality.', '2025-12-19 14:41:27'),
(6, 2, 4, 'Amazing picture and sound quality.', '2026-01-05 14:41:27'),
(6, 3, 5, 'Amazing picture and sound quality.', '2025-12-29 14:41:27'),
(6, 4, 3, 'Amazing picture and sound quality.', '2026-01-01 14:41:27'),
(6, 5, 5, 'Amazing picture and sound quality.', '2026-01-11 14:41:27'),
(6, 6, 5, 'Amazing picture and sound quality.', '2025-12-20 14:41:27'),
(6, 7, 4, 'Amazing picture and sound quality.', '2026-01-03 14:41:27'),
(6, 8, 5, 'Amazing picture and sound quality.', '2026-01-13 14:41:27'),
(7, 1, 5, 'Amazing picture and sound quality.', '2025-12-31 14:41:27'),
(7, 2, 5, 'Amazing picture and sound quality.', '2026-01-13 14:41:27'),
(7, 3, 4, 'Amazing picture and sound quality.', '2025-12-31 14:41:27'),
(7, 4, 4, 'Amazing picture and sound quality.', '2025-12-31 14:41:27'),
(7, 5, 5, 'Amazing picture and sound quality.', '2026-01-09 14:41:27'),
(7, 6, 5, 'Amazing picture and sound quality.', '2025-12-25 14:41:27'),
(7, 7, 3, 'Amazing picture and sound quality.', '2026-01-12 14:41:27'),
(7, 8, 4, 'Amazing picture and sound quality.', '2025-12-23 14:41:27'),
(8, 1, 5, 'Amazing picture and sound quality.', '2026-01-15 14:41:27'),
(8, 2, 3, 'Amazing picture and sound quality.', '2025-12-23 14:41:27'),
(8, 3, 4, 'Amazing picture and sound quality.', '2025-12-20 14:41:27'),
(8, 4, 3, 'Amazing picture and sound quality.', '2025-12-23 14:41:27'),
(8, 5, 5, 'Amazing picture and sound quality.', '2026-01-08 14:41:27'),
(8, 6, 3, 'Amazing picture and sound quality.', '2026-01-08 14:41:27'),
(8, 7, 4, 'Amazing picture and sound quality.', '2026-01-02 14:41:27'),
(8, 8, 4, 'Amazing picture and sound quality.', '2025-12-25 14:41:27'),
(9, 1, 4, 'Stylish design and very comfortable to wear.', '2026-01-16 14:41:27'),
(9, 2, 5, 'Stylish design and very comfortable to wear.', '2026-01-03 14:41:27'),
(9, 3, 3, 'Stylish design and very comfortable to wear.', '2026-01-07 14:41:27'),
(9, 4, 3, 'Stylish design and very comfortable to wear.', '2025-12-23 14:41:27'),
(9, 5, 5, 'Stylish design and very comfortable to wear.', '2025-12-20 14:41:27'),
(9, 6, 4, 'Stylish design and very comfortable to wear.', '2026-01-11 14:41:27'),
(9, 7, 3, 'Stylish design and very comfortable to wear.', '2025-12-21 14:41:27'),
(9, 8, 3, 'Stylish design and very comfortable to wear.', '2025-12-31 14:41:27'),
(10, 1, 5, 'Stylish design and very comfortable to wear.', '2026-01-07 14:41:27'),
(10, 2, 4, 'Stylish design and very comfortable to wear.', '2026-01-11 14:41:27'),
(10, 3, 3, 'Stylish design and very comfortable to wear.', '2025-12-31 14:41:27'),
(10, 4, 4, 'Stylish design and very comfortable to wear.', '2025-12-27 14:41:27'),
(10, 5, 4, 'Stylish design and very comfortable to wear.', '2025-12-22 14:41:27'),
(10, 6, 3, 'Stylish design and very comfortable to wear.', '2025-12-19 14:41:27'),
(10, 7, 4, 'Stylish design and very comfortable to wear.', '2026-01-05 14:41:27'),
(10, 8, 4, 'Stylish design and very comfortable to wear.', '2025-12-27 14:41:27'),
(11, 1, 5, 'Stylish design and very comfortable to wear.', '2025-12-21 14:41:27'),
(11, 2, 3, 'Stylish design and very comfortable to wear.', '2025-12-29 14:41:27'),
(11, 3, 5, 'Stylish design and very comfortable to wear.', '2026-01-03 14:41:27'),
(11, 4, 5, 'Stylish design and very comfortable to wear.', '2026-01-12 14:41:27'),
(11, 5, 4, 'Stylish design and very comfortable to wear.', '2025-12-29 14:41:27'),
(11, 6, 3, 'Stylish design and very comfortable to wear.', '2026-01-02 14:41:27'),
(11, 7, 4, 'Stylish design and very comfortable to wear.', '2025-12-27 14:41:27'),
(11, 8, 4, 'Stylish design and very comfortable to wear.', '2025-12-28 14:41:27'),
(12, 1, 4, 'Stylish design and very comfortable to wear.', '2025-12-19 14:41:27'),
(12, 2, 5, 'Stylish design and very comfortable to wear.', '2026-01-14 14:41:27'),
(12, 3, 4, 'Stylish design and very comfortable to wear.', '2026-01-10 14:41:27'),
(12, 4, 4, 'Stylish design and very comfortable to wear.', '2026-01-08 14:41:27'),
(12, 5, 4, 'Stylish design and very comfortable to wear.', '2026-01-12 14:41:27'),
(12, 6, 3, 'Stylish design and very comfortable to wear.', '2025-12-26 14:41:27'),
(12, 7, 4, 'Stylish design and very comfortable to wear.', '2026-01-09 14:41:27'),
(12, 8, 5, 'Stylish design and very comfortable to wear.', '2026-01-07 14:41:27'),
(13, 1, 3, 'Very efficient and fits perfectly in my home.', '2026-01-01 14:41:27'),
(13, 2, 4, 'Very efficient and fits perfectly in my home.', '2026-01-12 14:41:27'),
(13, 3, 5, 'Very efficient and fits perfectly in my home.', '2026-01-06 14:41:27'),
(13, 4, 4, 'Very efficient and fits perfectly in my home.', '2026-01-06 14:41:27'),
(13, 5, 4, 'Very efficient and fits perfectly in my home.', '2025-12-26 14:41:27'),
(13, 6, 4, 'Very efficient and fits perfectly in my home.', '2026-01-01 14:41:27'),
(13, 7, 5, 'Very efficient and fits perfectly in my home.', '2026-01-14 14:41:27'),
(13, 8, 5, 'Very efficient and fits perfectly in my home.', '2026-01-11 14:41:27'),
(14, 1, 5, 'Very efficient and fits perfectly in my home.', '2026-01-01 14:41:27'),
(14, 2, 3, 'Very efficient and fits perfectly in my home.', '2026-01-04 14:41:27'),
(14, 3, 4, 'Very efficient and fits perfectly in my home.', '2025-12-21 14:41:27'),
(14, 4, 3, 'Very efficient and fits perfectly in my home.', '2026-01-05 14:41:27'),
(14, 5, 3, 'Very efficient and fits perfectly in my home.', '2026-01-11 14:41:27'),
(14, 6, 3, 'Very efficient and fits perfectly in my home.', '2025-12-24 14:41:27'),
(14, 7, 5, 'Very efficient and fits perfectly in my home.', '2026-01-09 14:41:27'),
(14, 8, 3, 'Very efficient and fits perfectly in my home.', '2025-12-19 14:41:27'),
(15, 1, 3, 'Very efficient and fits perfectly in my home.', '2025-12-24 14:41:27'),
(15, 2, 5, 'Very efficient and fits perfectly in my home.', '2026-01-13 14:41:27'),
(15, 3, 5, 'Very efficient and fits perfectly in my home.', '2026-01-12 14:41:27'),
(15, 4, 5, 'Very efficient and fits perfectly in my home.', '2025-12-31 14:41:27'),
(15, 5, 5, 'Very efficient and fits perfectly in my home.', '2026-01-02 14:41:27'),
(15, 6, 5, 'Very efficient and fits perfectly in my home.', '2025-12-23 14:41:27'),
(15, 7, 4, 'Very efficient and fits perfectly in my home.', '2026-01-04 14:41:27'),
(15, 8, 3, 'Very efficient and fits perfectly in my home.', '2026-01-07 14:41:27'),
(16, 1, 5, 'Very efficient and fits perfectly in my home.', '2026-01-01 14:41:27'),
(16, 2, 4, 'Very efficient and fits perfectly in my home.', '2025-12-24 14:41:27'),
(16, 3, 4, 'Very efficient and fits perfectly in my home.', '2026-01-06 14:41:27'),
(16, 4, 3, 'Very efficient and fits perfectly in my home.', '2025-12-26 14:41:27'),
(16, 5, 3, 'Very efficient and fits perfectly in my home.', '2026-01-03 14:41:27'),
(16, 6, 5, 'Very efficient and fits perfectly in my home.', '2026-01-16 14:41:27'),
(16, 7, 4, 'Very efficient and fits perfectly in my home.', '2025-12-30 14:41:27'),
(16, 8, 3, 'Very efficient and fits perfectly in my home.', '2025-12-22 14:41:27'),
(17, 1, 3, 'Stylish design and very comfortable to wear.', '2025-12-27 14:41:27'),
(17, 2, 5, 'Stylish design and very comfortable to wear.', '2025-12-26 14:41:27'),
(17, 3, 4, 'Stylish design and very comfortable to wear.', '2026-01-02 14:41:27'),
(17, 4, 4, 'Stylish design and very comfortable to wear.', '2025-12-22 14:41:27'),
(17, 5, 5, 'Stylish design and very comfortable to wear.', '2026-01-04 14:41:27'),
(17, 6, 5, 'Stylish design and very comfortable to wear.', '2026-01-08 14:41:27'),
(17, 7, 3, 'Stylish design and very comfortable to wear.', '2025-12-31 14:41:27'),
(17, 8, 5, 'Stylish design and very comfortable to wear.', '2025-12-23 14:41:27'),
(18, 1, 4, 'Great performance, handles my tasks easily.', '2025-12-26 14:41:27'),
(18, 2, 3, 'Great performance, handles my tasks easily.', '2026-01-06 14:41:27'),
(18, 3, 5, 'Great performance, handles my tasks easily.', '2025-12-20 14:41:27'),
(18, 4, 3, 'Great performance, handles my tasks easily.', '2026-01-08 14:41:27'),
(18, 5, 5, 'Great performance, handles my tasks easily.', '2026-01-12 14:41:27'),
(18, 6, 3, 'Great performance, handles my tasks easily.', '2026-01-11 14:41:27'),
(18, 7, 5, 'Great performance, handles my tasks easily.', '2026-01-06 14:41:27'),
(18, 8, 5, 'Great performance, handles my tasks easily.', '2025-12-18 14:41:27');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `ProductId` int(11) NOT NULL,
  `ProductName` varchar(255) NOT NULL,
  `Description` varchar(512) NOT NULL,
  `Category` varchar(100) NOT NULL,
  `Type` varchar(100) NOT NULL,
  `Price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`ProductId`, `ProductName`, `Description`, `Category`, `Type`, `Price`) VALUES
(1, 'MacBook Pro 14\"', 'M3 Chip, 16GB RAM, 512GB SSD Space Gray.', 'computers', 'laptops', 199900),
(2, 'Gaming Desktop PC', 'RTX 4070, Intel i7-13700K, 32GB DDR5.', 'computers', 'desktops', 154950),
(3, '4K UltraWide Monitor', '34-inch curved display with 144Hz refresh rate.', 'computers', 'monitors', 45000),
(4, 'Mechanical Keyboard', 'RGB Backlit with Cherry MX Blue switches.', 'computers', 'keyboards', 12999),
(5, 'OLED 65\" Smart TV', '4K HDR, Dolby Vision, Alexa Built-in.', 'home_entertainment', 'smart_tvs', 219900),
(6, '7.1 Surround Sound', 'Wireless rear speakers and massive subwoofer.', 'home_entertainment', 'sound_systems', 89900),
(7, '4K Streaming Stick', 'Support for all major streaming apps in UHD.', 'home_entertainment', 'streaming_devices', 49999),
(8, 'Next-Gen Console', '1TB SSD, 4K Gaming, Blu-ray Drive.', 'home_entertainment', 'gaming_consoles', 49900),
(9, 'Series 9 Smartwatch', 'Always-on Retina display and health tracking.', 'wearables', 'smartwatches', 39900),
(10, 'Flagship Smartphone', '6.7-inch display, 256GB, Pro Camera system.', 'wearables', 'smartphones', 109900),
(11, 'Slim Fitness Tracker', 'Heart rate monitor and 10-day battery life.', 'wearables', 'fitness_trackers', 7950),
(12, 'Standalone VR Headset', 'High-resolution display with touch controllers.', 'wearables', 'vr_headsets', 29999),
(13, 'French Door Fridge', 'Stainless steel with water and ice dispenser.', 'appliances', 'refrigerators', 24900),
(14, 'Front Load Washer', '10kg capacity with steam cleaning technology.', 'appliances', 'washing_machines', 64900),
(15, 'Digital Microwave', '1000W output with sensor cook technology.', 'appliances', 'microwaves', 11900),
(16, 'Smart Air Conditioner', '12,000 BTU with Wi-Fi control and eco-mode.', 'appliances', 'air_conditioners', 52999),
(17, 'Bomb', 'asdfadsf', 'wearables', 'laptops', 234234),
(18, 'Acer Aspire Go 14', 'Super machine with AMD Ryzen 7000 series 5th generation and AMD Radeon graphics', 'computers', 'laptops', 55000);

-- --------------------------------------------------------

--
-- Table structure for table `shoppingCart`
--

CREATE TABLE `shoppingCart` (
  `UserId` int(11) NOT NULL,
  `ProductId` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `shoppingCart`
--

INSERT INTO `shoppingCart` (`UserId`, `ProductId`, `Quantity`) VALUES
(7, 15, 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserId` int(11) NOT NULL,
  `FirstName` varchar(100) NOT NULL,
  `LastName` varchar(100) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Role` varchar(50) NOT NULL,
  `Address` varchar(50) NOT NULL,
  `isVerified` tinyint(1) DEFAULT 0,
  `isActive` tinyint(1) DEFAULT 1,
  `Password` varchar(255) NOT NULL,
  `reset_token_hash` varchar(64) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  `verification_token` varchar(64) DEFAULT NULL,
  `verification_token_expires_at` datetime DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserId`, `FirstName`, `LastName`, `Email`, `Role`, `Address`, `isVerified`, `isActive`, `Password`, `reset_token_hash`, `reset_token_expires_at`, `verification_token`, `verification_token_expires_at`, `verified_at`) VALUES
(1, 'Peter', 'Johnson', 'Peter?john5@email.com', 'customer', 'theHague', 0, 1, '$2y$12$qje9kS163jRpO6kSEqtop.LSyMTvrbXTiKORVSR3Qai2qNyPB0pWC', NULL, NULL, NULL, NULL, NULL),
(2, 'David', 'Kutěj', 'davidkutejx21@gmail.com', 'admin', 'amsterdam', 1, 1, '$2y$12$dtqo4EozE9Ba8l1NV4taAOscsQE.XlQawEObWBOz/lAZAkeRcz3tW', '9b4b701ef3a619650b7ef172b9d0119becc73c82d0bcd0b6dd4e195f51ebaa06', '2026-01-13 11:41:58', NULL, NULL, NULL),
(3, 'Robin', 'Master', 'robmaster@mail.com', 'employee', 'rotterdam', 1, 1, '$2y$12$IZbbor7gfyVipI296Oi8G.ssiR80bjRQiHfxySBYYExLsl5XIegJy', NULL, NULL, NULL, NULL, NULL),
(4, 'Adam', 'Martak', '728353@student.inholland.com', 'customer', 'theHague', 0, 1, '$2y$12$ma2M0N4SWn8Q/pxdqDwpE.Z0L3YT8wPQ8biF9wUy9RYaRFkpGPpna', '6169dae022c09c0d1542bc7ed2e9505050c25533352f87cfa913a6978d0ff0ea', '2026-01-05 20:12:27', NULL, NULL, NULL),
(5, 'Bempah', 'Goka', 'bempahnana9@gmail.com', 'customer', 'amsterdam', 0, 1, '$2y$12$deGCB4blPFjM.uNjubh05.X4tjHPS9qSn6n2k7uR5oBHU2ahqiCL.', '449b8f0bd50553d9e2a940a3d75ae114c8abfd71d78b3acd27823b9706ebb9c5', '2026-01-05 20:15:10', NULL, NULL, NULL),
(6, 'David', 'Volitelne', 'davidvolitelne@gmail.com', 'customer', 'rotterdam', 1, 1, '$2y$12$xTmobD2oQ2dY7B3mP7SdGekr72NDS84mxAK3f/KFx6Ey57B0S/x5q', '55fe4e925f47da3ad167cdbb9aced2a25aa7e60ea9b6f8fac29877f2bc7ba73d', '2026-01-16 17:24:44', NULL, NULL, NULL),
(7, 'Vinc', 'Discple', 'tincezz123@gmail.com', 'customer', 'amsterdam', 0, 1, '$2y$12$i3PSmbVoj292TSa2UwYgIOshBiOnpJO0muC1iG.PaGPUxLkfgO6F2', '1324fad19a006735c218164ba1bf288db109c13985600052592484bbe9e518ad', '2026-01-08 10:39:37', '41d6313393b18cadfaed83964d5beb909cdf4655dfb5613a067aacd9ca8c39b4', '2026-01-09 10:10:51', NULL),
(8, 'Soung', 'Soung', 'soungkkr@gmail.com', 'customer', 'amsterdam', 0, 1, '$2y$12$/15UN271HmXUc01AKxPcP.9uSK9EwJJxCtlswVS8Ml2SJSJofPhte', '635c8021d28d7316f5dfba864c5615df50fe5382ccfdfb166b2f82cae33e37a5', '2026-01-08 22:56:13', '71b3e84600cd6328332d7c4864f4c253e09ec61dea0c2fa30dd9fbc6f5b3a79f', '2026-01-09 22:25:45', NULL),
(9, 'Tomas', 'Edison', 'tahleadresajeneplatna@emai.world', 'customer', 'rotterdam', 0, 0, '$2y$12$XjWujlDTqMoD/GJXB4SMf.Ylv/tFP7qIPzyCA5o7L34xC6l.FHlWy', NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orderItems`
--
ALTER TABLE `orderItems`
  ADD PRIMARY KEY (`OrderItemId`),
  ADD KEY `OrderId` (`OrderId`),
  ADD KEY `ProductId` (`ProductId`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`OrderId`),
  ADD KEY `fk_orders_user` (`UserId`);

--
-- Indexes for table `productRating`
--
ALTER TABLE `productRating`
  ADD PRIMARY KEY (`ProductId`,`UserId`),
  ADD KEY `UserId` (`UserId`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ProductId`);

--
-- Indexes for table `shoppingCart`
--
ALTER TABLE `shoppingCart`
  ADD PRIMARY KEY (`UserId`,`ProductId`),
  ADD KEY `ProductId` (`ProductId`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserId`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD UNIQUE KEY `reset_token_hash` (`reset_token_hash`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orderItems`
--
ALTER TABLE `orderItems`
  MODIFY `OrderItemId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `OrderId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `ProductId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orderItems`
--
ALTER TABLE `orderItems`
  ADD CONSTRAINT `1` FOREIGN KEY (`OrderId`) REFERENCES `orders` (`OrderId`) ON DELETE CASCADE,
  ADD CONSTRAINT `2` FOREIGN KEY (`ProductId`) REFERENCES `products` (`ProductId`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`UserId`) REFERENCES `users` (`UserId`) ON DELETE CASCADE;

--
-- Constraints for table `productRating`
--
ALTER TABLE `productRating`
  ADD CONSTRAINT `1` FOREIGN KEY (`ProductId`) REFERENCES `products` (`ProductId`) ON DELETE CASCADE,
  ADD CONSTRAINT `2` FOREIGN KEY (`UserId`) REFERENCES `users` (`UserId`) ON DELETE CASCADE;

--
-- Constraints for table `shoppingCart`
--
ALTER TABLE `shoppingCart`
  ADD CONSTRAINT `1` FOREIGN KEY (`UserId`) REFERENCES `users` (`UserId`) ON DELETE CASCADE,
  ADD CONSTRAINT `2` FOREIGN KEY (`ProductId`) REFERENCES `products` (`ProductId`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

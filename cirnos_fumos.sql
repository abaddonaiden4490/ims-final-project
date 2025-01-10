-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 26, 2024 at 01:08 PM
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
-- Database: `cirnos_fumos`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `photo` varchar(255) DEFAULT 'photos/default_product_image.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `price`, `created_at`, `updated_at`, `photo`) VALUES
(1, 'Cirno Fumo', 'A cute plush of Cirno the ice fairy.', 29.99, '2024-11-15 02:37:11', '2024-11-20 02:41:59', '/photos/6736d5d19c1412.65845752.png'),
(2, 'Reimu Fumo', 'A soft and huggable Reimu Hakurei plush.', 34.99, '2024-11-15 02:37:11', '2024-11-20 04:12:13', '/photos/673d619da5c292.94147588.jpg'),
(3, 'Marisa Fumo', 'A magical Marisa Kirisame plush toy.', 32.99, '2024-11-15 02:37:11', '2024-11-20 04:07:59', '/photos/673d609f0d2814.10960166.jpg'),
(16, 'Koishi Komeiji', 'billows', 59.99, '2024-11-15 04:50:04', '2024-11-20 02:41:59', '/photos/6736d2fc87b301.22914353.jpg'),
(17, 'Flandre', 'Plunder', 59.99, '2024-11-15 04:56:17', '2024-11-20 02:41:59', '/photos/6736d4719ff8f4.58574799.png'),
(26, 'Ayaka', 'e', 59.99, '2024-11-20 04:01:11', '2024-11-20 04:01:11', 'photos/673d5f0794ab60.89315287.png'),
(28, 'Gawr Gura', 'a', 40.00, '2024-11-22 04:17:55', '2024-11-22 04:17:55', 'photos/674005f3e9ea76.90117451.jpg'),
(29, 'Nakiri Ayame', 'FUMO', 30.00, '2024-11-24 11:34:38', '2024-11-24 11:34:38', 'photos/67430f4e1ef869.39645356.jpg'),
(30, 'Sparkle Fumo', 'Honkai Star Rail', 39.99, '2024-11-24 11:35:02', '2024-11-24 11:40:55', '/photos/674310c775cd02.66327410.jpg'),
(32, 'Fumo service', 'broken fumo? fix me', 5.00, '2024-11-24 12:25:38', '2024-11-24 12:25:38', 'photos/default_product_image.jpg'),
(33, 'Emu Otori Fumo', 'wonderhoy', 99.99, '2024-11-25 12:10:51', '2024-11-25 12:10:51', 'photos/6744694bd291a1.90199873.png');

-- --------------------------------------------------------

--
-- Table structure for table `purchased`
--

CREATE TABLE `purchased` (
  `purchase_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `purchase_date` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchased`
--

INSERT INTO `purchased` (`purchase_id`, `user_id`, `product_id`, `quantity`, `price`, `total_price`, `purchase_date`, `status`) VALUES
(19, 18, 30, 1, 39.99, 39.99, '2024-11-24 21:34:25', 1),
(20, 18, 1, 1, 29.99, 29.99, '2024-11-24 21:35:56', 1),
(21, 19, 16, 1, 59.99, 59.99, '2024-11-24 23:03:40', 1),
(22, 18, 30, 2, 39.99, 79.98, '2024-11-25 15:27:39', 1),
(23, 18, 29, 1, 30.00, 30.00, '2024-11-25 15:27:39', 1),
(24, 18, 30, 1, 39.99, 39.99, '2024-11-25 15:30:57', 1),
(25, 18, 30, 1, 39.99, 39.99, '2024-11-25 15:38:14', 1),
(26, 18, 30, 1, 39.99, 39.99, '2024-11-25 16:20:01', 1),
(27, 18, 26, 1, 59.99, 59.99, '2024-11-25 16:21:19', 1),
(28, 18, 30, 1, 39.99, 39.99, '2024-11-25 17:06:09', 1),
(29, 18, 30, 1, 39.99, 39.99, '2024-11-25 17:12:37', 1),
(30, 18, 30, 1, 39.99, 39.99, '2024-11-25 17:50:48', 1),
(31, 18, 30, 1, 39.99, 39.99, '2024-11-25 17:51:05', 1),
(32, 18, 30, 1, 39.99, 39.99, '2024-11-25 18:13:24', 1),
(33, 18, 30, 1, 39.99, 39.99, '2024-11-25 18:27:10', 1),
(34, 18, 30, 1, 39.99, 39.99, '2024-11-25 18:57:41', 1),
(35, 18, 30, 1, 39.99, 39.99, '2024-11-25 19:03:24', 1),
(36, 18, 30, 1, 39.99, 39.99, '2024-11-25 19:16:51', 1),
(37, 18, 30, 1, 39.99, 39.99, '2024-11-25 19:59:42', 1),
(38, 18, 30, 1, 39.99, 39.99, '2024-11-25 20:03:46', 1),
(39, 18, 30, 1, 39.99, 39.99, '2024-11-25 20:06:48', 1),
(40, 18, 29, 6, 30.00, 180.00, '2024-11-25 20:06:48', 1),
(41, 18, 26, 3, 59.99, 179.97, '2024-11-25 20:06:48', 1),
(42, 18, 33, 100, 99.99, 9999.00, '2024-11-25 20:12:44', 1),
(43, 18, 16, 69, 59.99, 4139.31, '2024-11-25 20:12:44', 1),
(44, 18, 32, 1, 5.00, 5.00, '2024-11-25 20:12:44', 1),
(45, 18, 33, 2, 99.99, 199.98, '2024-11-26 20:06:52', 1);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `product_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(10, 1, 18, 8, '****', '2024-11-24 13:36:04'),
(11, 30, 18, 9, '****', '2024-11-24 13:36:08'),
(12, 16, 19, 10, 'Koishi Komeiji **** in Touhou 11', '2024-11-24 15:03:57');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'admin'),
(2, 'customer');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `role_id`, `is_active`, `created_at`, `updated_at`, `profile_picture`) VALUES
(1, 'ADMINHaruka', 'johndoughpractice@gmail.com', 'Cirn0sFuMo$', 1, 1, '2024-11-15 02:37:11', '2024-11-24 11:59:08', 'photos/Haruka.jpg'),
(18, 'RanMitake', 'joshbernabe0829@gmail.com', 'Cirn0sFuMo$', 2, 1, '2024-11-24 11:59:35', '2024-11-24 12:11:18', 'photos/d823d3319abdb1331347ee95fc7f7d88.jpg'),
(19, 'EmuOtori', 'emuotori@taguig.com', 'Cirn0sFuMo$', 2, 1, '2024-11-24 14:34:01', '2024-11-24 14:34:01', 'photos/Emu otori.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `purchased`
--
ALTER TABLE `purchased`
  ADD PRIMARY KEY (`purchase_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `purchased`
--
ALTER TABLE `purchased`
  MODIFY `purchase_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `purchased`
--
ALTER TABLE `purchased`
  ADD CONSTRAINT `purchased_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `purchased_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

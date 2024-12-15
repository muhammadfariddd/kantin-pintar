-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 14, 2024 at 11:40 PM
-- Server version: 8.0.30
-- PHP Version: 8.3.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kantin_pintar`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-12-14 16:51:27');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kategori` enum('makanan','minuman','snack') NOT NULL DEFAULT 'makanan',
  `gambar` varchar(255) DEFAULT NULL,
  `deskripsi` text,
  `harga` decimal(10,2) NOT NULL,
  `stok` int DEFAULT '0',
  `available` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `nama`, `kategori`, `gambar`, `deskripsi`, `harga`, `stok`, `available`, `created_at`) VALUES
(1, 'Nasi Ayam Geprek', 'makanan', '674c0dd22bbe4.jpg', 'Nasi dengan lauk Ayam Goreng Tepung dengan sambal cabai rawit merah (caplak)', '10000.00', 5, 1, '2024-12-01 07:18:42'),
(25, 'Nasi Rames', 'makanan', '67530b7e0d186.jpg', 'Nasi yang bercampur dengan beraneka ragam lauk sebagai pelengkap.', '7000.00', 6, 1, '2024-12-06 14:34:38'),
(26, 'Es Teh Jumbo', 'minuman', '675434d28bff7.jpg', 'Minuman teh yang disajikan dalam kemasan botol plastik berukuran besar. Minuman populer yang menyegarkan, terutama saat cuaca panas.', '3000.00', 12, 1, '2024-12-07 11:43:14'),
(27, 'Nasi Pecel', 'makanan', '675436461a93a.jpg', 'Makanan khas Indonesia yang terdiri dari nasi, sayuran yang direbus, dan sambal kacang. ', '7000.00', 9, 1, '2024-12-07 11:49:26'),
(28, 'Keripik Singkong', 'snack', '675437641b18d.jpg', 'Camilan renyah yang terbuat dari irisan tipis singkong yang digoreng hingga kering. Singkong, atau dikenal juga sebagai ubi kayu, dipotong tipis-tipis kemudian direndam dalam air garam sebelum digoreng dalam minyak panas hingga garing dan berwarna keemasan.', '10000.00', 4, 1, '2024-12-07 11:54:12'),
(29, 'Brownies Kukus', 'snack', '675439a222d13.jpg', 'Brownies memiliki cita rasa manis yang khas dan dapat dihidangkan dengan beberapa jenis topping seperti kacang, cokelat chip, atau saus cokelat.', '16000.00', 5, 1, '2024-12-07 12:03:46'),
(30, 'Es Jeruk', 'minuman', '67545415a3c5d.jpg', 'Es jeruk terbuat dari perasan jeruk, air, dan gula yang disajikan dengan es batu. Minuman ini kaya akan vitamin C dan antioksidan yang baik untuk kesehatan tubuh.', '5000.00', 10, 1, '2024-12-07 13:56:37');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `pesan` text NOT NULL,
  `status` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `pesan`, `status`, `created_at`) VALUES
(16, 1, 'Pesanan #20 Anda sudah siap untuk diambil!', 1, '2024-12-14 23:09:02');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `waktu_pengambilan` datetime NOT NULL,
  `status` enum('Diproses','Siap','Diambil') DEFAULT 'Diproses',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_archived` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_harga`, `waktu_pengambilan`, `status`, `created_at`, `is_archived`) VALUES
(1, 1, '42000.00', '2024-12-09 09:00:00', 'Diambil', '2024-12-08 13:14:17', 1),
(2, 1, '15000.00', '2024-12-09 10:00:00', 'Diambil', '2024-12-08 13:18:11', 0),
(3, 1, '7000.00', '2024-12-09 10:00:00', 'Diambil', '2024-12-08 13:24:21', 0),
(4, 1, '80000.00', '2024-12-09 12:00:00', 'Diambil', '2024-12-08 13:24:47', 0),
(5, 1, '9000.00', '2024-12-09 09:00:00', 'Diambil', '2024-12-08 13:29:53', 0),
(6, 1, '20000.00', '2024-12-09 14:00:00', 'Diambil', '2024-12-08 13:35:36', 0),
(7, 1, '16000.00', '2024-12-09 08:00:00', 'Diambil', '2024-12-08 13:51:29', 0),
(8, 1, '20000.00', '2024-12-09 08:00:00', 'Diambil', '2024-12-08 14:14:20', 0),
(9, 1, '10000.00', '2024-12-09 09:00:00', 'Diambil', '2024-12-08 14:17:54', 0),
(10, 1, '20000.00', '2024-12-09 09:00:00', 'Diambil', '2024-12-08 14:18:37', 0),
(11, 1, '6000.00', '2024-12-09 15:00:00', 'Diambil', '2024-12-08 14:21:19', 0),
(12, 1, '9000.00', '2024-12-09 08:00:00', 'Diambil', '2024-12-08 14:31:45', 0),
(13, 1, '3000.00', '2024-12-09 10:00:00', 'Diambil', '2024-12-08 14:32:26', 0),
(14, 1, '3000.00', '2024-12-09 08:00:00', 'Diambil', '2024-12-08 14:38:14', 0),
(15, 1, '3000.00', '2024-12-09 08:00:00', 'Diambil', '2024-12-08 14:49:47', 0),
(16, 1, '10000.00', '2024-12-09 09:00:00', 'Diambil', '2024-12-08 15:14:22', 1),
(17, 1, '3000.00', '2024-12-09 09:00:00', 'Diambil', '2024-12-08 15:26:59', 1),
(18, 1, '27000.00', '2024-12-09 17:00:00', 'Diambil', '2024-12-09 05:02:43', 1),
(19, 1, '16000.00', '2024-12-10 10:00:00', 'Diambil', '2024-12-09 16:41:55', 1),
(20, 1, '6000.00', '2024-12-15 11:00:00', 'Diambil', '2024-12-14 22:03:09', 0),
(21, 1, '3000.00', '2024-12-15 15:00:00', 'Diproses', '2024-12-14 23:11:32', 0),
(22, 1, '16000.00', '2024-12-15 15:00:00', 'Diproses', '2024-12-14 23:13:04', 0),
(23, 1, '16000.00', '2024-12-15 09:00:00', 'Diproses', '2024-12-14 23:21:38', 0),
(24, 1, '16000.00', '2024-12-15 09:00:00', 'Diproses', '2024-12-14 23:21:57', 0),
(25, 1, '3000.00', '2024-12-15 17:00:00', 'Diproses', '2024-12-14 23:23:52', 0),
(26, 1, '19000.00', '2024-12-15 10:00:00', 'Diproses', '2024-12-14 23:32:19', 0),
(27, 1, '3000.00', '2024-12-15 14:00:00', 'Diproses', '2024-12-14 23:34:40', 0);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `menu_id` int NOT NULL,
  `jumlah` int NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_id`, `jumlah`, `subtotal`) VALUES
(1, 1, 29, 2, '32000.00'),
(2, 1, 30, 2, '10000.00'),
(3, 2, 30, 3, '15000.00'),
(4, 3, 25, 1, '7000.00'),
(5, 4, 29, 5, '80000.00'),
(6, 5, 26, 3, '9000.00'),
(7, 6, 28, 2, '20000.00'),
(8, 7, 29, 1, '16000.00'),
(9, 8, 1, 2, '20000.00'),
(10, 9, 30, 2, '10000.00'),
(11, 10, 30, 2, '10000.00'),
(12, 10, 28, 1, '10000.00'),
(13, 11, 26, 2, '6000.00'),
(14, 12, 26, 3, '9000.00'),
(15, 13, 26, 1, '3000.00'),
(16, 14, 26, 1, '3000.00'),
(17, 15, 26, 1, '3000.00'),
(18, 16, 1, 1, '10000.00'),
(19, 17, 26, 1, '3000.00'),
(20, 18, 1, 2, '20000.00'),
(21, 18, 27, 1, '7000.00'),
(22, 19, 29, 1, '16000.00'),
(23, 20, 26, 2, '6000.00'),
(24, 21, 26, 1, '3000.00'),
(25, 22, 29, 1, '16000.00'),
(26, 23, 29, 1, '16000.00'),
(27, 24, 29, 1, '16000.00'),
(28, 25, 26, 1, '3000.00'),
(29, 26, 29, 1, '16000.00'),
(30, 26, 26, 1, '3000.00'),
(31, 27, 26, 1, '3000.00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nim` varchar(15) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','mahasiswa') DEFAULT 'mahasiswa',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nim`, `nama`, `email`, `password`, `role`, `created_at`) VALUES
(1, '231240001383', 'Muhammad Farid', 'ilhamfaridjepara@gmail.com', '$2y$10$xaESdT3iE0rGUfJq3eto8eEdB6Dy2b2HOVI2UAGeFpp/rINi1W00O', 'mahasiswa', '2024-11-30 14:10:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nim` (`nim`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

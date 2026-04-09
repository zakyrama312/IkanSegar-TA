-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 09, 2026 at 08:29 AM
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
-- Database: `db_ikan`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id` int(11) NOT NULL,
  `transaksi_id` int(11) NOT NULL,
  `ikan_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `harga_satuan` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id`, `transaksi_id`, `ikan_id`, `qty`, `harga_satuan`, `subtotal`) VALUES
(1, 1, 4, 1, 65000, 65000),
(2, 1, 3, 1, 140000, 140000),
(3, 2, 2, 2, 210000, 420000),
(4, 3, 4, 11, 65000, 715000);

-- --------------------------------------------------------

--
-- Table structure for table `ikan`
--

CREATE TABLE `ikan` (
  `id` int(11) NOT NULL,
  `nama_ikan` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` int(11) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `satuan` varchar(20) NOT NULL DEFAULT 'kg',
  `gambar` varchar(255) DEFAULT 'default.jpg',
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ikan`
--

INSERT INTO `ikan` (`id`, `nama_ikan`, `deskripsi`, `harga`, `stok`, `satuan`, `gambar`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, 'Ikan Kakap Merah Segar', 'Tangkapan laut dalam, daging tebal dan manis.', 85000, 20, 'kg', 'https://images.unsplash.com/photo-1524683745036-b46f52b8505a?auto=format&fit=crop&q=80&w=600&h=400', 1, '2026-04-08 05:57:57', '2026-04-08 05:57:57'),
(2, 'Salmon Fillet Premium', 'Kualitas sashimi grade, kaya Omega-3.', 210000, 13, 'kg', 'https://images.unsplash.com/photo-1599084993091-1cb5c0721cc6?auto=format&fit=crop&q=80&w=600&h=400', 1, '2026-04-08 05:57:57', '2026-04-09 00:52:57'),
(3, 'Udang Tiger Super', 'Ukuran besar, sangat cocok untuk dibakar.', 140000, 34, 'kg', 'https://images.unsplash.com/photo-1565680018434-b513d5e5fd47?auto=format&fit=crop&q=80&w=600&h=400', 1, '2026-04-08 05:57:57', '2026-04-09 00:43:36'),
(4, 'Ikan Gurame Hidup', 'Diambil langsung dari kolam saat dipesan.', 65000, 0, 'kg', 'https://images.unsplash.com/photo-1511556820780-d912e42b4980?auto=format&fit=crop&q=80&w=600&h=400', 1, '2026-04-08 05:57:57', '2026-04-09 00:53:12'),
(5, 'Cumi-cumi Ring Segar', 'Sudah dibersihkan, siap dimasak.', 75000, 0, 'kg', 'https://images.unsplash.com/photo-1615141982883-c7ad0e69fd62?auto=format&fit=crop&q=80&w=600&h=400', 1, '2026-04-08 05:57:57', '2026-04-08 05:57:57'),
(6, 'Ikan Nila Merah', 'Cocok untuk digoreng kering atau dibakar.', 35000, 85, 'kg', 'https://images.unsplash.com/photo-1580414057403-c5f451f30e1c?auto=format&fit=crop&q=80&w=600&h=400', 1, '2026-04-08 05:57:57', '2026-04-09 02:27:31'),
(13, 'Lele', NULL, 200, 150, 'ekor', '1775697939_69d700130d786.jpg', 1, '2026-04-09 01:25:39', '2026-04-09 01:25:39');

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_stok`
--

CREATE TABLE `riwayat_stok` (
  `id` int(11) NOT NULL,
  `ikan_id` int(11) NOT NULL,
  `jumlah_tambah` int(11) NOT NULL,
  `tanggal_tambah` datetime NOT NULL DEFAULT current_timestamp(),
  `keterangan` varchar(255) DEFAULT 'Restock (Tambah Stok)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `riwayat_stok`
--

INSERT INTO `riwayat_stok` (`id`, `ikan_id`, `jumlah_tambah`, `tanggal_tambah`, `keterangan`) VALUES
(1, 6, 12, '2026-04-09 08:05:29', ''),
(5, 13, 150, '2026-04-09 08:25:39', 'Stok Awal (Input Data Baru)'),
(6, 6, 23, '2026-04-09 09:27:31', '');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `kode_transaksi` varchar(20) NOT NULL,
  `tanggal_waktu` datetime NOT NULL DEFAULT current_timestamp(),
  `total_belanja` int(11) NOT NULL,
  `jumlah_bayar` int(11) NOT NULL,
  `kembalian` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `kode_transaksi`, `tanggal_waktu`, `total_belanja`, `jumlah_bayar`, `kembalian`, `user_id`) VALUES
(1, 'TRX-20260409-024336', '2026-04-09 07:43:36', 205000, 300000, 95000, 1),
(2, 'TRX-20260409-025257', '2026-04-09 07:52:57', 420000, 420000, 0, 1),
(3, 'TRX-20260409-025312', '2026-04-09 07:53:12', 715000, 715000, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','kasir') NOT NULL DEFAULT 'kasir',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$AiQkYdJVOXSQDWHH61Ay6edYz.m8PmgxLIYsCuZBEDWyr/uqDDEZe', 'Administrator Utama', 'admin', '2026-04-08 05:57:57'),
(2, 'kasir1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Kasir', 'kasir', '2026-04-08 05:57:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaksi_id` (`transaksi_id`),
  ADD KEY `ikan_id` (`ikan_id`);

--
-- Indexes for table `ikan`
--
ALTER TABLE `ikan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `riwayat_stok`
--
ALTER TABLE `riwayat_stok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ikan_id` (`ikan_id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ikan`
--
ALTER TABLE `ikan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `riwayat_stok`
--
ALTER TABLE `riwayat_stok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `fk_detail_ikan` FOREIGN KEY (`ikan_id`) REFERENCES `ikan` (`id`),
  ADD CONSTRAINT `fk_detail_transaksi` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `riwayat_stok`
--
ALTER TABLE `riwayat_stok`
  ADD CONSTRAINT `fk_riwayat_ikan` FOREIGN KEY (`ikan_id`) REFERENCES `ikan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_transaksi_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

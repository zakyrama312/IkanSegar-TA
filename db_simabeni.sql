-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql312.infinityfree.com
-- Generation Time: Jul 21, 2026 at 08:33 PM
-- Server version: 11.4.7-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_37186379_db_ikan`
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
(1, 7, 19, 150, 250, 37500),
(2, 8, 20, 1500, 100, 150000),
(3, 9, 17, 200, 500, 100000),
(4, 10, 20, 1000, 100, 100000),
(5, 11, 22, 1000, 200, 200000),
(6, 12, 15, 100, 1000, 100000),
(7, 13, 18, 500, 200, 100000),
(8, 14, 20, 1000, 100, 100000),
(9, 15, 20, 2500, 100, 250000),
(10, 16, 19, 400, 250, 100000),
(11, 17, 22, 375, 200, 75000),
(12, 18, 22, 150, 200, 30000),
(13, 19, 19, 400, 250, 100000),
(14, 20, 22, 500, 200, 100000),
(15, 21, 22, 250, 200, 50000),
(16, 22, 22, 400, 200, 80000),
(17, 23, 19, 200, 250, 50000),
(18, 25, 18, 750, 200, 150000),
(19, 26, 19, 1080, 250, 270000),
(20, 27, 22, 100, 200, 20000),
(21, 28, 18, 220, 200, 44000),
(22, 29, 22, 100, 200, 20000),
(23, 30, 22, 200, 200, 40000),
(24, 31, 18, 4850, 200, 970000),
(25, 32, 20, 5920, 100, 592000),
(26, 33, 22, 250, 200, 50000),
(27, 34, 22, 7250, 200, 1450000),
(28, 35, 20, 300, 100, 30000),
(29, 36, 19, 468, 250, 117000),
(30, 37, 20, 500, 100, 50000),
(31, 38, 20, 500, 100, 50000),
(32, 39, 22, 250, 200, 50000),
(33, 40, 17, 140, 500, 70000),
(34, 40, 16, 350, 300, 105000),
(35, 41, 22, 500, 200, 100000),
(36, 42, 20, 200, 100, 20000),
(37, 43, 14, 100, 2000, 200000),
(38, 44, 20, 9000, 100, 900000),
(39, 44, 22, 300, 200, 60000),
(40, 45, 22, 300, 200, 60000),
(41, 46, 20, 4000, 100, 400000),
(42, 47, 22, 3700, 200, 740000),
(43, 48, 16, 1500, 300, 450000),
(44, 48, 17, 180, 500, 90000),
(45, 49, 14, 150, 2000, 300000),
(46, 50, 19, 200, 250, 50000),
(47, 51, 20, 1000, 100, 100000),
(48, 52, 22, 3000, 200, 600000),
(49, 52, 20, 1360, 100, 136000),
(50, 53, 17, 500, 500, 250000),
(51, 54, 17, 350, 500, 175000),
(52, 55, 20, 200, 100, 20000),
(53, 56, 18, 500, 200, 100000),
(54, 57, 17, 200, 500, 100000),
(55, 58, 16, 200, 300, 60000),
(56, 59, 20, 7000, 100, 700000),
(57, 60, 22, 2500, 200, 500000),
(58, 61, 22, 300, 200, 60000),
(59, 62, 19, 500, 250, 125000),
(60, 63, 15, 10, 1000, 10000),
(61, 64, 16, 100, 300, 30000);

-- --------------------------------------------------------

--
-- Table structure for table `ikan`
--

CREATE TABLE `ikan` (
  `id` int(11) NOT NULL,
  `nama_ikan` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` int(11) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `satuan` varchar(10) NOT NULL DEFAULT 'kg',
  `gambar` varchar(255) DEFAULT 'default.jpg',
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ikan`
--

INSERT INTO `ikan` (`id`, `nama_ikan`, `deskripsi`, `harga`, `stok`, `satuan`, `gambar`, `status_aktif`, `created_at`, `updated_at`) VALUES
(14, 'Ikan Gurame uk 57', NULL, 2000, 1450, 'ekor', '1776068087_69dca5f75f816.jpeg', 1, '2026-04-13 08:14:47', '2026-07-20 06:06:09'),
(15, 'Ikan Gurame uk 46', NULL, 1000, 890, 'ekor', '1781618381_edit_6a3156cdee55c.jpg', 1, '2026-04-13 08:15:00', '2026-07-20 06:04:34'),
(16, 'Ikan Bawal uk 7-9', NULL, 300, 350, 'ekor', '1781618716_edit_6a31581ca91b1.jpg', 1, '2026-04-13 08:15:00', '2026-07-20 06:14:33'),
(17, 'Ikan Bawal uk 9-12', NULL, 500, 430, 'ekor', '1781618867_edit_6a3158b346a7d.png', 1, '2026-04-13 08:16:00', '2026-06-17 03:25:15'),
(18, 'Ikan Lele  uk 46', NULL, 200, 180, 'ekor', '1781619075_edit_6a315983534a9.jpg', 1, '2026-04-13 08:16:00', '2026-06-16 14:11:14'),
(19, 'Ikan Lele  uk 57', NULL, 250, 1602, 'ekor', '1781619248_edit_6a315a30bf94e.jpg', 1, '2026-04-13 08:17:00', '2026-06-17 03:38:06'),
(20, 'Ikan Nila uk 35', NULL, 100, 1520, 'ekor', '1781619398_edit_6a315ac6a315d.jpg', 1, '2026-04-13 08:17:00', '2026-06-17 03:30:02'),
(22, 'Ikan Nila Uk 46', NULL, 200, 2075, 'ekor', '1781619485_edit_6a315b1d35129.jpg', 1, '2026-04-19 06:15:00', '2026-06-17 03:36:14');

-- --------------------------------------------------------

--
-- Table structure for table `pembeli`
--

CREATE TABLE `pembeli` (
  `id` int(11) NOT NULL,
  `nama_pembeli` varchar(50) NOT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembeli`
--

INSERT INTO `pembeli` (`id`, `nama_pembeli`, `no_hp`, `alamat`, `created_at`) VALUES
(1, 'Ardiansyah', NULL, NULL, '2026-04-17 08:59:25'),
(2, 'bapak sobari', NULL, NULL, '2026-04-16 19:37:42'),
(3, 'haji samsuri', NULL, NULL, '2026-04-16 19:37:42'),
(4, 'bapak muin', NULL, NULL, '2026-04-18 18:10:13'),
(5, 'bapak sultoni', '', 'tegalsari', '2026-04-27 21:28:30'),
(6, 'bapak narto', '085227981099', 'suradadi', '2026-04-28 05:54:08'),
(7, 'bapak agung', '081802540531', 'desa cacaban', '2026-05-15 06:22:03'),
(8, 'bapak dartum', '', 'desa cacaban', '2026-05-15 06:24:18'),
(9, 'bapak agus', '085201097387', 'jatinegara', '2026-05-15 07:57:10'),
(10, 'bapak anto', '085225567688', 'kudaile', '2026-05-15 07:59:05'),
(11, 'bapak andri', '082324661787', 'desa pangkah', '2026-05-15 08:04:32'),
(12, 'bapak hermanto', '', 'lebaksiu', '2026-05-19 07:51:22'),
(13, 'bapak sutikno', '085552280735', 'suradadi', '2026-05-20 05:47:14'),
(14, 'bapak hasan', '', 'desa kalibakung', '2026-06-16 20:25:15'),
(15, 'haji munasir', '', 'bangsri (brebes)', '2026-06-16 20:30:02'),
(16, 'bapak sawal', '082138970181', 'kedung kelor', '2026-06-16 20:33:09'),
(17, 'bapak muali', '081371480474', 'pemalang', '2026-06-16 20:36:14');

-- --------------------------------------------------------

--
-- Table structure for table `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `id` int(11) NOT NULL,
  `nama_pengeluaran` varchar(100) NOT NULL,
  `total` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengeluaran`
--

INSERT INTO `pengeluaran` (`id`, `nama_pengeluaran`, `total`, `tanggal`, `keterangan`, `user_id`, `created_at`) VALUES
(2, 'plastik packing', 50000, '2026-04-14', '2 kg', 1, '2026-04-13 23:25:14'),
(4, 'setor PAD', 960000, '2026-04-24', 'PAD (pendapatan asli daerah)', 1, '2026-04-28 06:06:08'),
(5, 'makan siang ', 82000, '2026-01-03', 'untuk tamu dan karyawan', 1, '2026-05-15 08:14:53'),
(6, 'plastik packing', 167000, '2026-01-03', '3 kg', 1, '2026-05-15 08:15:40'),
(7, 'galon,gula dan kopi', 75000, '2026-01-07', 'operasional', 1, '2026-05-15 08:16:54'),
(8, 'es batu', 19000, '2026-01-09', 'untuk packing', 1, '2026-05-15 08:18:21'),
(9, 'tisu,minuman,jajanan dan ikan konsumsi', 402000, '2026-01-12', 'menjamu tamu dari kantor pusat', 1, '2026-05-15 08:20:55'),
(10, 'makan siang dan gula pasir', 30000, '2026-01-13', '', 1, '2026-05-15 08:22:06'),
(11, 'isi ulang tabung oksigen', 370000, '2026-01-20', 'untuk packing', 1, '2026-05-15 08:23:37'),
(12, 'air mineral 1 dus ,piting lampu,solasi', 104000, '2026-01-21', 'operasional', 1, '2026-05-15 08:27:06'),
(13, 'isi ulang galon,kopi,gula,teh', 83000, '2026-01-26', 'operasional', 1, '2026-05-15 08:28:25'),
(14, 'beli makanan dan minuman', 40000, '2026-01-27', '', 1, '2026-05-15 08:29:34'),
(15, 'beli makanan dan minuman', 70000, '2026-01-29', '', 1, '2026-05-15 08:30:07'),
(16, 'setor PAD', 736000, '2026-01-22', 'PAD (pendapatan asli daerah)', 1, '2026-05-15 08:58:57'),
(17, 'plastik packing', 200000, '2026-02-02', '', 1, '2026-05-15 17:24:03'),
(18, 'iuran kantor', 220000, '2026-02-19', '', 1, '2026-05-15 17:25:43'),
(19, 'operasional dapur', 154000, '2026-02-10', '', 1, '2026-05-15 17:27:26'),
(20, 'pesan baner dan operasional', 247000, '2026-02-19', '', 1, '2026-05-15 17:41:32'),
(21, 'setor PAD', 592000, '2026-02-26', '', 1, '2026-05-15 17:42:55'),
(22, 'besin mobil', 150000, '2026-02-27', 'untuk ambil pakan', 1, '2026-05-15 17:44:01'),
(23, 'bayar wi-fi', 167500, '2026-03-02', '', 1, '2026-05-15 18:09:38'),
(24, 'servis dongkrak', 100000, '2026-03-10', '', 1, '2026-05-15 18:10:23'),
(25, 'setor PAD', 1450000, '2026-03-11', '', 1, '2026-05-15 18:11:04'),
(26, 'karet,detol,kopi,gula', 165000, '2026-03-27', '', 1, '2026-05-15 18:13:14'),
(27, 'plastik packing', 65000, '2026-03-30', '', 1, '2026-05-15 18:13:57'),
(28, 'bayar wi-fi', 167500, '2026-03-30', '', 1, '2026-05-15 18:14:43'),
(29, 'isi ulang galon,makan', 123000, '2026-04-09', '', 1, '2026-05-15 18:25:05'),
(30, 'beli kopi,bensin,jajanan', 87000, '2026-04-13', '', 1, '2026-05-15 18:27:08'),
(31, 'beli plastik packing,gas elpii,gula,teh', 276000, '2026-04-14', '', 1, '2026-05-15 18:31:05'),
(33, 'isi ulang galon,snak,rokok', 62000, '2026-04-23', '', 1, '2026-05-15 18:33:42'),
(34, 'beli kopi', 55000, '2026-04-27', '', 1, '2026-05-15 18:34:30'),
(35, 'bayar wi-fi', 167000, '2026-04-30', '', 1, '2026-05-15 18:35:00'),
(36, 'lampu tembak,seser', 250000, '2026-05-07', '', 1, '2026-05-15 18:37:32'),
(37, 'plastik packing', 115000, '2026-05-07', '', 1, '2026-05-15 18:38:02'),
(38, 'isi ulang galon,kopi,gula,teh', 154000, '2026-05-11', '', 1, '2026-05-15 18:39:20'),
(39, 'beli makanan dan minuman', 50000, '2026-05-12', '', 1, '2026-05-15 18:39:59'),
(40, 'beli snak untuk tamu', 20000, '2026-05-18', '', 1, '2026-06-16 20:08:46'),
(41, 'beli bensin untuk disel, roundap (obat rumput)', 140000, '2026-05-19', '', 1, '2026-06-16 20:11:10'),
(42, 'beli makan dan snack siang untuk tamu dari kementrian', 270000, '2026-05-21', '', 1, '2026-06-16 20:12:35'),
(43, 'setor PAD bulan Mei', 960000, '2026-05-26', '', 1, '2026-06-16 20:13:35');

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
(16, 20, 10000, '2026-04-13 23:36:20', 'nila uk 35'),
(17, 14, 200, '2026-04-13 23:37:39', 'gurame uk 57'),
(18, 22, 5000, '2026-04-18 23:15:00', 'Stok Awal (Input Data Baru)'),
(19, 18, 2000, '2026-02-02 10:08:00', 'ikan lele uk 46'),
(20, 22, 7000, '2026-03-05 10:16:00', 'nila uk 46'),
(21, 20, 8000, '2026-04-07 10:37:00', 'nila uk 35'),
(22, 20, 5000, '2026-05-02 10:53:00', 'nila uk 35'),
(23, 22, 4500, '2026-05-02 10:53:00', 'nila uk 46'),
(24, 20, 1500, '2026-01-09 11:53:00', 'nila uk 35'),
(25, 22, 3000, '2026-01-09 11:54:00', 'nila uk 46'),
(26, 20, 8000, '2026-01-06 23:15:00', 'nila uk 35'),
(27, 22, 4000, '2026-02-06 23:20:00', 'nila uk 46'),
(28, 16, 500, '2026-05-20 23:21:00', 'bawal uk 79'),
(29, 14, 500, '2026-07-20 02:05:00', 'gurame uk 57');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `kode_transaksi` varchar(20) NOT NULL,
  `nama_pembeli` varchar(50) DEFAULT 'Pelanggan Umum',
  `tanggal_waktu` datetime NOT NULL DEFAULT current_timestamp(),
  `total_belanja` int(11) NOT NULL,
  `jumlah_bayar` int(11) NOT NULL,
  `kembalian` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `kode_transaksi`, `nama_pembeli`, `tanggal_waktu`, `total_belanja`, `jumlah_bayar`, `kembalian`, `user_id`) VALUES
(7, 'TRX-20260414-024244', 'Ardiansyah', '2026-04-14 07:42:44', 37500, 50000, 12500, 1),
(8, 'TRX-20260414-022354', 'bapak sobari', '2026-04-13 23:23:54', 150000, 150000, 0, 1),
(9, 'TRX-20260416-121512', 'haji samsuri', '2026-04-16 09:15:12', 100000, 100000, 0, 2),
(10, 'TRX-20260418-211013', 'bapak muin', '2026-04-18 18:10:13', 100000, 100000, 0, 1),
(11, 'TRX-20260428-002639', 'Pelanggan Umum', '2026-04-27 21:26:39', 200000, 200000, 0, 1),
(12, 'TRX-20260428-002830', 'bapak sultoni', '2026-04-27 21:28:30', 100000, 100000, 0, 1),
(13, 'TRX-20260428-085407', 'bapak narto', '2026-04-28 05:54:08', 100000, 100000, 0, 1),
(14, 'TRX-20260103-091800', 'bapak agung', '2026-01-03 09:18:00', 100000, 100000, 0, 1),
(15, 'TRX-20260108-092200', 'bapak dartum', '2026-01-08 09:22:00', 250000, 250000, 0, 1),
(16, 'TRX-20260109-092400', 'Pelanggan Umum', '2026-01-09 09:24:00', 100000, 100000, 0, 1),
(17, 'TRX-20260112-092500', 'Pelanggan Umum', '2026-01-12 09:25:00', 75000, 100000, 25000, 1),
(18, 'TRX-20260112-092800', 'Pelanggan Umum', '2026-01-12 09:28:00', 30000, 30000, 0, 1),
(19, 'TRX-20260112-092900', 'Pelanggan Umum', '2026-01-12 09:29:00', 100000, 100000, 0, 1),
(20, 'TRX-20260113-093000', 'Pelanggan Umum', '2026-01-13 09:30:00', 100000, 100000, 0, 1),
(21, 'TRX-20260115-093100', 'Pelanggan Umum', '2026-01-15 09:31:00', 50000, 50000, 0, 1),
(22, 'TRX-20260121-093100', 'Pelanggan Umum', '2026-01-21 09:31:00', 80000, 100000, 20000, 1),
(23, 'TRX-20260123-093300', 'Pelanggan Umum', '2026-01-23 09:33:00', 50000, 50000, 0, 1),
(25, 'TRX-20260123-093900', 'Pelanggan Umum', '2026-01-23 09:39:00', 150000, 150000, 0, 1),
(26, 'TRX-20260127-094000', 'Pelanggan Umum', '2026-01-27 09:40:00', 270000, 300000, 30000, 1),
(27, 'TRX-20260129-094200', 'Pelanggan Umum', '2026-01-29 09:42:00', 20000, 20000, 0, 1),
(28, 'TRX-20260130-094300', 'Pelanggan Umum', '2026-01-30 09:43:00', 44000, 50000, 6000, 1),
(29, 'TRX-20260202-095100', 'Pelanggan Umum', '2026-02-02 09:51:00', 20000, 20000, 0, 1),
(30, 'TRX-20260204-095200', 'Pelanggan Umum', '2026-02-04 09:52:00', 40000, 40000, 0, 1),
(31, 'TRX-20260218-101100', 'Pelanggan Umum', '2026-02-18 10:11:00', 970000, 970000, 0, 1),
(32, 'TRX-20260226-101200', 'Pelanggan Umum', '2026-02-26 10:12:00', 592000, 592000, 0, 1),
(33, 'TRX-20260227-101300', 'Pelanggan Umum', '2026-02-27 10:13:00', 50000, 50000, 0, 1),
(34, 'TRX-20260210-101700', 'Pelanggan Umum', '2026-03-10 10:17:00', 1450000, 1450000, 0, 1),
(35, 'TRX-20260213-101800', 'Pelanggan Umum', '2026-03-13 10:18:00', 30000, 30000, 0, 1),
(36, 'TRX-20260227-101900', 'Pelanggan Umum', '2026-03-27 10:19:00', 117000, 120000, 3000, 1),
(37, 'TRX-20260330-102000', 'Pelanggan Umum', '2026-03-30 10:20:00', 50000, 50000, 0, 1),
(38, 'TRX-20260401-103900', 'Pelanggan Umum', '2026-04-01 10:39:00', 50000, 50000, 0, 1),
(39, 'TRX-20260406-104000', 'Pelanggan Umum', '2026-04-06 10:40:00', 50000, 50000, 0, 1),
(40, 'TRX-20260408-104000', 'Pelanggan Umum', '2026-04-08 10:40:00', 175000, 180000, 5000, 1),
(41, 'TRX-20260409-104300', 'Pelanggan Umum', '2026-04-09 10:43:00', 100000, 100000, 0, 1),
(42, 'TRX-20260415-104300', 'Pelanggan Umum', '2026-04-15 10:43:00', 20000, 20000, 0, 1),
(43, 'TRX-20260416-104400', 'Pelanggan Umum', '2026-04-16 10:44:00', 200000, 200000, 0, 1),
(44, 'TRX-20260422-104500', 'Pelanggan Umum', '2026-04-22 10:45:00', 960000, 960000, 0, 1),
(45, 'TRX-20260424-104700', 'Pelanggan Umum', '2026-04-24 10:47:00', 60000, 60000, 0, 1),
(46, 'TRX-20260505-105400', 'bapak agus', '2026-05-05 10:54:00', 400000, 400000, 0, 1),
(47, 'TRX-20260508-105700', 'bapak anto', '2026-05-08 10:57:00', 740000, 750000, 10000, 1),
(48, 'TRX-20260511-105900', 'bapak agung', '2026-05-11 10:59:00', 540000, 550000, 10000, 1),
(49, 'TRX-20260512-110200', 'bapak andri', '2026-05-12 11:02:00', 300000, 300000, 0, 1),
(50, 'TRX-20260101-110400', 'Pelanggan Umum', '2026-01-01 11:04:00', 50000, 50000, 0, 1),
(51, 'TRX-20260103-114000', 'Pelanggan Umum', '2026-01-03 11:40:00', 100000, 100000, 0, 1),
(52, 'TRX-20260121-115500', 'Pelanggan Umum', '2026-01-21 11:55:00', 736000, 736000, 0, 1),
(53, 'TRX-20260108-203200', 'Pelanggan Umum', '2026-01-08 20:32:00', 250000, 250000, 0, 1),
(54, 'TRX-20260519-104800', 'bapak hermanto', '2026-05-19 10:48:00', 175000, 175000, 0, 1),
(55, 'TRX-20260520-084500', 'bapak sutikno', '2026-05-20 08:45:00', 20000, 20000, 0, 1),
(56, 'TRX-20260525-224600', 'bapak agung', '2026-05-25 22:46:00', 100000, 100000, 0, 1),
(57, 'TRX-20260602-232300', 'bapak hasan', '2026-06-02 23:23:00', 100000, 100000, 0, 1),
(58, 'TRX-20260603-232500', 'Pelanggan Umum', '2026-06-03 23:25:00', 60000, 60000, 0, 1),
(59, 'TRX-20260603-232700', 'haji munasir', '2026-06-03 23:27:00', 700000, 700000, 0, 1),
(60, 'TRX-20260604-233000', 'bapak sawal', '2026-06-04 23:30:00', 500000, 500000, 0, 1),
(61, 'TRX-20260610-233300', 'bapak muali', '2026-06-10 23:33:00', 60000, 60000, 0, 1),
(62, 'TRX-20260615-233600', 'Pelanggan Umum', '2026-06-15 23:36:00', 125000, 125000, 0, 1),
(63, 'TRX-20260720-020300', 'bapak anto', '2026-07-20 02:03:00', 10000, 10000, 0, 1),
(64, 'TRX-20260720-021300', 'bapak andri', '2026-07-20 02:13:00', 30000, 30000, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(50) NOT NULL,
  `role` enum('admin','kasir','owner') NOT NULL DEFAULT 'kasir',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$JS1P4FjRgIiq8XKUbt9z1.4U.y5ETXKKKMwe3hfg3RX5i0KnfFyeO', 'Administrator Utama', 'admin', '2026-04-08 05:57:57'),
(2, 'kasir1', '$2y$10$WuHSH6z6sz0WXsfNIe7O4uoQbrDAw2QzG5.uwZRpLo9X74Pa79UCu', 'Budi Kasir', 'kasir', '2026-04-08 05:57:57'),
(3, 'uptd', '$2y$10$6iaOokNnTz8Ee7yoerw8puAMVbttYWh7MreEL.2vAwhmDc8VY2omS', 'UPTD', 'owner', '2026-04-10 00:46:18');

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
-- Indexes for table `pembeli`
--
ALTER TABLE `pembeli`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pengeluaran_user` (`user_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `ikan`
--
ALTER TABLE `ikan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `pembeli`
--
ALTER TABLE `pembeli`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `riwayat_stok`
--
ALTER TABLE `riwayat_stok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- Constraints for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD CONSTRAINT `fk_pengeluaran_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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

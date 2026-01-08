-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 08, 2026 at 11:21 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_banksampah`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `last_active` datetime DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `nama`, `email`, `password`, `last_active`, `foto`) VALUES
(1, 'rahma fauziah', 'rahmazia9@gmail.com', '$2y$10$FxrGBUfBXYoHX8o6m7NTM.wLp/mpEUhJWUec7piS1vnjun01C8SEe', '2026-01-08 12:13:52', 'admin_1.png');

-- --------------------------------------------------------

--
-- Table structure for table `banksampah`
--

CREATE TABLE `banksampah` (
  `id_bank` int NOT NULL,
  `nama_bank` varchar(100) DEFAULT NULL,
  `alamat` text,
  `kota_kabupaten` varchar(100) DEFAULT NULL,
  `kontak` varchar(20) DEFAULT NULL,
  `latitude` decimal(12,8) DEFAULT NULL,
  `longitude` decimal(12,8) DEFAULT NULL,
  `status` enum('Aktif','Nonaktif') NOT NULL DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `banksampah`
--

INSERT INTO `banksampah` (`id_bank`, `nama_bank`, `alamat`, `kota_kabupaten`, `kontak`, `latitude`, `longitude`, `status`) VALUES
(1, 'Bank Sampah Induk Bali Bersih', 'Gg. Agastia No. 3, Padangsambian, Denpasar Barat', 'Denpasar', '0818559441', -8.64587800, 115.18526300, 'Aktif'),
(2, 'Bank Sampah Prema Bali Lestari', 'Jl. Gili Biaha No. 6, Dauh Puri Kelod, Denpasar Barat', 'Denpasar', '-', -8.67371500, 115.20965100, 'Aktif'),
(3, 'Bank Sampah Mekar Sari Lestari', 'Padangsambian Klod, Denpasar Barat', 'Denpasar', '-', -8.66306200, 115.18311700, 'Aktif'),
(4, 'Bank Sampah Kelurahan Dauh Puri', 'Kantor Kelurahan Dauh Puri, Denpasar Barat', 'Denpasar', '0361226612', -8.66220600, 115.21275700, 'Aktif'),
(5, 'Bank Sampah Lotus', 'Jl. Teratai No. 21, Dangin Puri Kangin, Denpasar Utara', 'Denpasar', '087862098999', -8.65194400, 115.21972200, 'Aktif');

-- --------------------------------------------------------

--
-- Table structure for table `req_reward`
--

CREATE TABLE `req_reward` (
  `id_req` int NOT NULL,
  `id_user` int NOT NULL,
  `id_reward` int NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `poin_digunakan` int NOT NULL,
  `status` enum('menunggu','disetujui','ditolak','berhasil') NOT NULL DEFAULT 'menunggu',
  `nomor` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `req_reward`
--

INSERT INTO `req_reward` (`id_req`, `id_user`, `id_reward`, `tanggal`, `poin_digunakan`, `status`, `nomor`) VALUES
(7, 3, 5, '2025-12-18 09:50:48', 200, 'berhasil', ''),
(8, 3, 1, '2025-12-18 11:25:22', 100, 'berhasil', '089635180805');

-- --------------------------------------------------------

--
-- Table structure for table `reward`
--

CREATE TABLE `reward` (
  `id_reward` int NOT NULL,
  `nama_reward` varchar(100) DEFAULT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `deskripsi` text,
  `poin_diperlukan` int DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `reward` enum('fisik','digital') DEFAULT NULL,
  `stok` int NOT NULL DEFAULT '0',
  `ditukar` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `reward`
--

INSERT INTO `reward` (`id_reward`, `nama_reward`, `kategori`, `deskripsi`, `poin_diperlukan`, `status`, `gambar`, `reward`, `stok`, `ditukar`) VALUES
(1, 'Saldo DANA Rp10.000', 'E-Money', 'Tukarkan poinmu dan dapatkan Saldo DANA Rp10.000.', 100, 'aktif', 'reward_1765178496.jpg', NULL, 46, 4),
(2, 'Penanaman 1 Pohon', 'Green Impact', 'Tukarkan poinmu untuk menanam 1 pohon dan dukung lingkungan hijau.', 800, 'aktif', 'reward_1765178663.jpg', NULL, 30, 0),
(3, 'Voucher Belanja Rp50.000', 'Voucher', 'Tukarkan poinmu dengan voucher belanja senilai Rp50.000', 500, 'aktif', 'reward_1765944204.png', NULL, 40, 0),
(4, 'Voucher Belanja Supermart', 'Voucher', 'Dapatkan voucher Rp50.000 untuk semua produk favoritmu', 500, 'aktif', 'reward_1765949512.png', NULL, 10, 0),
(5, 'Donasi Penanaman Pohon', 'Green Impact', 'Donasi untuk menanam pohon tambahan di lahan kritis atau hutan kota', 200, 'aktif', 'reward_1765950191.jpg', NULL, 18, 2),
(6, 'Saldo DANA Rp20.000', 'E-Money', 'Tukarkan poinmu dan dapatkan Saldo DANA Rp20.000.\r\n', 200, 'aktif', 'reward_1765950261.jpg', NULL, 49, 1);

-- --------------------------------------------------------

--
-- Table structure for table `setoran`
--

CREATE TABLE `setoran` (
  `id_setoran` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `id_bank` int DEFAULT NULL,
  `tanggal` datetime DEFAULT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `berat` float DEFAULT NULL,
  `foto_bukti` varchar(255) DEFAULT NULL,
  `status` enum('menunggu','diverifikasi','ditolak') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `setoran`
--

INSERT INTO `setoran` (`id_setoran`, `id_user`, `id_bank`, `tanggal`, `kategori`, `berat`, `foto_bukti`, `status`) VALUES
(18, 3, 1, '2025-12-18 09:49:12', 'Sampah Sisa Makanan', 25, 'setor_1766022552_988.jpg', 'diverifikasi'),
(19, 3, 1, '2025-12-18 09:49:58', 'Sampah Sisa Buah dan Sayur', 10, 'setor_1766022598_153.jpg', 'diverifikasi'),
(20, 3, 1, '2025-12-18 11:22:56', 'Sampah Pertanian', 5.5, 'setor_1766028176_923.jpg', 'diverifikasi'),
(21, 3, 1, '2025-12-18 11:23:53', 'Sampah Hewani', 15, 'setor_1766028233_596.jpg', 'diverifikasi'),
(22, 3, 1, '2025-12-18 11:24:33', 'Sampah Kotoran Hewan', 12, 'setor_1766028273_156.jpg', 'menunggu'),
(23, 3, 1, '2025-12-18 11:31:13', 'Sampah Hewani', 11, 'setor_1766028673_924.jpg', 'menunggu');

-- --------------------------------------------------------

--
-- Table structure for table `struk_reward`
--

CREATE TABLE `struk_reward` (
  `id_struk` int NOT NULL,
  `id_req` int NOT NULL,
  `nomor_struk` varchar(50) NOT NULL,
  `tipe_reward` enum('emoney','green_impact','voucher') NOT NULL,
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `struk_reward`
--

INSERT INTO `struk_reward` (`id_struk`, `id_req`, `nomor_struk`, `tipe_reward`, `tanggal`) VALUES
(6, 7, '202512180007', 'emoney', '2025-12-18 09:51:58'),
(7, 8, '202512180008', 'emoney', '2025-12-18 11:28:18');

-- --------------------------------------------------------

--
-- Table structure for table `transaksipoin`
--

CREATE TABLE `transaksipoin` (
  `id_transaksi` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `id_reward` int DEFAULT NULL,
  `tanggal` datetime DEFAULT NULL,
  `poin_digunakan` int DEFAULT NULL,
  `poin_diterima` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaksipoin`
--

INSERT INTO `transaksipoin` (`id_transaksi`, `id_user`, `id_reward`, `tanggal`, `poin_digunakan`, `poin_diterima`) VALUES
(22, 3, NULL, '2025-12-18 09:49:12', 0, 250),
(23, 3, NULL, '2025-12-18 09:49:58', 0, 120),
(24, 3, 5, '2025-12-18 09:51:58', 200, 0),
(25, 3, NULL, '2025-12-18 11:22:56', 0, 28),
(26, 3, NULL, '2025-12-18 11:23:53', 0, 120),
(27, 3, NULL, '2025-12-18 11:24:33', 0, 48),
(28, 3, 1, '2025-12-18 11:28:18', 100, 0),
(29, 3, NULL, '2025-12-18 11:31:13', 0, 88);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` int NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `alamat` text,
  `no_hp` varchar(15) DEFAULT NULL,
  `tanggal_daftar` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'aktif',
  `total_setor` int DEFAULT '0',
  `total_berat` decimal(10,2) DEFAULT '0.00',
  `tanggal_lahir` date DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_expire` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `nama`, `email`, `password`, `alamat`, `no_hp`, `tanggal_daftar`, `status`, `total_setor`, `total_berat`, `tanggal_lahir`, `reset_token`, `reset_expire`) VALUES
(2, 'Rahma Fauziah', 'dewirahmafzh@gmail.com', '$2y$10$lZcIeQ7L.l5rCLrPAfUa4e2QEzO7GlrkLiNoeMVIjkXPnJ/foofDK', '', '', NULL, 'aktif', 0, 0.00, NULL, '21f3513fa34fb875ff6ccbd6ec7fafa8d702c6f19ed61850a003cc7bb64eb4d1', '2026-01-02 14:44:20'),
(3, '171_Dewi Rahma Fauziah', 'fauziahrahma138@gmail.com', '', '', '', NULL, 'aktif', 4, 55.50, NULL, NULL, NULL),
(4, 'makanyuk', 'makanyuk2405@gmail.com', '$2y$10$WWbkV1eokwpTSeKKKkv/DuEZaK.MtYWLDmx0TVl.VhFhLpbWa9Z2q', '', '', NULL, 'aktif', 0, 0.00, NULL, '695f845fd5eba0e039310126a355b3c450cee97aaed491002334a62ecf120782', '2025-12-18 05:47:34');

-- --------------------------------------------------------

--
-- Table structure for table `validasi`
--

CREATE TABLE `validasi` (
  `id_validasi` int NOT NULL,
  `id_admin` int DEFAULT NULL,
  `id_setoran` int DEFAULT NULL,
  `tanggal_validasi` date DEFAULT NULL,
  `keterangan` text,
  `status_validasi` enum('diterima','ditolak','menunggu') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `validasi`
--

INSERT INTO `validasi` (`id_validasi`, `id_admin`, `id_setoran`, `tanggal_validasi`, `keterangan`, `status_validasi`) VALUES
(16, 1, 18, '2025-12-18', 'Setoran diterima', 'diterima'),
(17, 1, 19, '2025-12-18', 'Setoran diterima', 'diterima'),
(18, 1, 20, '2025-12-18', 'Setoran diterima', 'diterima'),
(19, 1, 21, '2025-12-18', 'Setoran diterima', 'diterima');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indexes for table `banksampah`
--
ALTER TABLE `banksampah`
  ADD PRIMARY KEY (`id_bank`);

--
-- Indexes for table `req_reward`
--
ALTER TABLE `req_reward`
  ADD PRIMARY KEY (`id_req`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_reward` (`id_reward`);

--
-- Indexes for table `reward`
--
ALTER TABLE `reward`
  ADD PRIMARY KEY (`id_reward`);

--
-- Indexes for table `setoran`
--
ALTER TABLE `setoran`
  ADD PRIMARY KEY (`id_setoran`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_bank` (`id_bank`);

--
-- Indexes for table `struk_reward`
--
ALTER TABLE `struk_reward`
  ADD PRIMARY KEY (`id_struk`),
  ADD KEY `id_req` (`id_req`);

--
-- Indexes for table `transaksipoin`
--
ALTER TABLE `transaksipoin`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_reward` (`id_reward`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`);

--
-- Indexes for table `validasi`
--
ALTER TABLE `validasi`
  ADD PRIMARY KEY (`id_validasi`),
  ADD KEY `id_admin` (`id_admin`),
  ADD KEY `id_setoran` (`id_setoran`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `banksampah`
--
ALTER TABLE `banksampah`
  MODIFY `id_bank` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `req_reward`
--
ALTER TABLE `req_reward`
  MODIFY `id_req` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `reward`
--
ALTER TABLE `reward`
  MODIFY `id_reward` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `setoran`
--
ALTER TABLE `setoran`
  MODIFY `id_setoran` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `struk_reward`
--
ALTER TABLE `struk_reward`
  MODIFY `id_struk` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transaksipoin`
--
ALTER TABLE `transaksipoin`
  MODIFY `id_transaksi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `validasi`
--
ALTER TABLE `validasi`
  MODIFY `id_validasi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `req_reward`
--
ALTER TABLE `req_reward`
  ADD CONSTRAINT `req_reward_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `req_reward_ibfk_2` FOREIGN KEY (`id_reward`) REFERENCES `reward` (`id_reward`);

--
-- Constraints for table `setoran`
--
ALTER TABLE `setoran`
  ADD CONSTRAINT `setoran_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `setoran_ibfk_2` FOREIGN KEY (`id_bank`) REFERENCES `banksampah` (`id_bank`);

--
-- Constraints for table `struk_reward`
--
ALTER TABLE `struk_reward`
  ADD CONSTRAINT `struk_reward_ibfk_1` FOREIGN KEY (`id_req`) REFERENCES `req_reward` (`id_req`);

--
-- Constraints for table `transaksipoin`
--
ALTER TABLE `transaksipoin`
  ADD CONSTRAINT `transaksipoin_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `transaksipoin_ibfk_2` FOREIGN KEY (`id_reward`) REFERENCES `reward` (`id_reward`);

--
-- Constraints for table `validasi`
--
ALTER TABLE `validasi`
  ADD CONSTRAINT `validasi_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`),
  ADD CONSTRAINT `validasi_ibfk_2` FOREIGN KEY (`id_setoran`) REFERENCES `setoran` (`id_setoran`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 26, 2026 at 04:49 PM
-- Server version: 8.0.30
-- PHP Version: 8.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `al_falah`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id` int NOT NULL,
  `siswa_id` int DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `kehadiran` enum('Hadir','Sakit','Izin','Alpa') DEFAULT NULL,
  `created_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hafalan`
--

CREATE TABLE `hafalan` (
  `id` int NOT NULL,
  `siswa_id` int DEFAULT NULL,
  `status` enum('Belum Hafal','Masih Menghafal','Sudah Lancar') DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `hafalan`
--

INSERT INTO `hafalan` (`id`, `siswa_id`, `status`, `updated_by`, `updated_at`) VALUES
(1, 1, 'Sudah Lancar', 1, '2026-04-26 16:16:39'),
(2, 2, 'Belum Hafal', 1, '2026-04-26 16:16:39'),
(3, 3, 'Belum Hafal', 1, '2026-04-26 16:16:39'),
(4, 4, 'Belum Hafal', 1, '2026-04-26 16:16:39'),
(5, 5, 'Belum Hafal', 1, '2026-04-26 16:16:39'),
(6, 6, 'Belum Hafal', 1, '2026-04-26 16:16:39'),
(7, 7, 'Belum Hafal', 1, '2026-04-26 16:16:39'),
(8, 8, 'Belum Hafal', 1, '2026-04-26 16:16:39'),
(9, 9, 'Belum Hafal', 1, '2026-04-26 16:16:39'),
(10, 10, 'Belum Hafal', 1, '2026-04-26 16:16:39');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` int NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `nis` varchar(20) DEFAULT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') DEFAULT NULL,
  `kelas` varchar(20) DEFAULT NULL,
  `status` enum('aktif','tidak_aktif') DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id`, `nama`, `nis`, `jenis_kelamin`, `kelas`, `status`, `created_at`) VALUES
(1, 'Ahmad Zulkarnain', '20231001', 'Laki-laki', '9A - Tahfidz', 'aktif', '2026-04-26 16:14:17'),
(2, 'Fatimah Az-Zahra', '20231002', 'Perempuan', '8C - Ikhwan', 'aktif', '2026-04-26 16:14:17'),
(3, 'Muhammad Rizky', '20231003', 'Laki-laki', '7B - Reguler', 'aktif', '2026-04-26 16:14:17'),
(4, 'Siti Aminah', '20231004', 'Perempuan', '9A - Tahfidz', 'aktif', '2026-04-26 16:14:17'),
(5, 'Umar bin Khattab', '20231005', 'Laki-laki', '8A - Tahfidz', 'aktif', '2026-04-26 16:14:17'),
(6, 'Ahmad Al-Ghifari', '20231006', 'Laki-laki', '7-A', 'aktif', '2026-04-26 16:14:17'),
(7, 'Fatimah Nurul Huda', '20231007', 'Perempuan', '7-A', 'aktif', '2026-04-26 16:14:17'),
(8, 'Muhammad Zulkifli', '20231008', 'Laki-laki', '7-A', 'aktif', '2026-04-26 16:14:17'),
(9, 'Siti Khadijah', '20231009', 'Perempuan', '7-A', 'aktif', '2026-04-26 16:14:17'),
(10, 'Yahya Al-Fatih', '20231010', 'Laki-laki', '7-A', 'aktif', '2026-04-26 16:14:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin_guru','kepala_sekolah') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin Guru', 'guru@alfalah.com', '$2y$10$kLuQknJIc7xOLfq.jjP4QO8lea78J.p9B6bJAtAq.Eqmm.T8Ed5By', 'admin_guru', '2026-04-26 16:14:17'),
(2, 'Kepala Sekolah', 'kepala@alfalah.com', '$2y$10$kLuQknJIc7xOLfq.jjP4QO8lea78J.p9B6bJAtAq.Eqmm.T8Ed5By', 'kepala_sekolah', '2026-04-26 16:14:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `hafalan`
--
ALTER TABLE `hafalan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nis` (`nis`);

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
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hafalan`
--
ALTER TABLE `hafalan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`),
  ADD CONSTRAINT `absensi_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `hafalan`
--
ALTER TABLE `hafalan`
  ADD CONSTRAINT `hafalan_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`),
  ADD CONSTRAINT `hafalan_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

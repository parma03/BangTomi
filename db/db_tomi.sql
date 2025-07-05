-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 05, 2025 at 06:23 AM
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
-- Database: `db_tomi`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_appsetting`
--

CREATE TABLE `tb_appsetting` (
  `id_appsetting` bigint(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `logo` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_appsetting`
--

INSERT INTO `tb_appsetting` (`id_appsetting`, `name`, `logo`) VALUES
(1, 'Sumbar Protokol INTEGRETED', 'Logo1.png');

-- --------------------------------------------------------

--
-- Table structure for table `tb_kegiatan`
--

CREATE TABLE `tb_kegiatan` (
  `id_kegiatan` bigint(11) NOT NULL,
  `judul_kegiatan` varchar(255) NOT NULL,
  `deksripsi_kegiatan` text NOT NULL,
  `jadwal_kegiatan` timestamp NOT NULL DEFAULT current_timestamp(),
  `kehadiran_kegiatan` text NOT NULL,
  `thumbnails_kegiatan` text NOT NULL,
  `status_kegiatan` enum('selesai','pending') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_kegiatan`
--

INSERT INTO `tb_kegiatan` (`id_kegiatan`, `judul_kegiatan`, `deksripsi_kegiatan`, `jadwal_kegiatan`, `kehadiran_kegiatan`, `thumbnails_kegiatan`, `status_kegiatan`, `created_at`, `updated_at`) VALUES
(1, 'UPACARA HARI LAHIR PANCASILA 2024', 'UPACARA HARI LAHIR PANCASILA 2024', '2024-06-08 08:11:24', 'http://docs.google.com/spreadsheets/d/1FD1PfsdPiOPsTQb9l6oyh-mptjy9LzXt6rtybFF07G0/edit?resourcekey=&gid=1861385280#gid=1861385280', 'Snaptik.app_7319763115694968069.mp4', 'pending', '2025-06-10 08:11:24', '2025-06-10 14:34:13'),
(4, 'tes eee', 'tesd123', '2025-06-11 07:23:00', 'http://docs.google.com/spreadsheets/d/1FD1PfsdPiOP...\n', 'kegiatan_4_1749975969_684e83a1e3603.mp4', 'pending', '2025-06-15 07:24:06', '2025-06-15 08:40:58'),
(5, 'tes eee1243', 'tes23', '2025-07-02 08:27:00', 'ets', 'kegiatan_1749976084_684e84141ff79.mp4', 'pending', '2025-06-15 08:28:04', '2025-06-15 08:28:04');

-- --------------------------------------------------------

--
-- Table structure for table `tb_penugasan`
--

CREATE TABLE `tb_penugasan` (
  `id_penugasan` bigint(11) NOT NULL,
  `id_kegiatan` bigint(11) NOT NULL,
  `id_pegawai` bigint(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_user`
--

CREATE TABLE `tb_user` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `nohp` varchar(20) NOT NULL,
  `role` enum('admin','petugas') NOT NULL DEFAULT 'petugas',
  `password` varchar(255) NOT NULL,
  `photo_profile` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tb_user`
--

INSERT INTO `tb_user` (`id`, `nama`, `email`, `nohp`, `role`, `password`, `photo_profile`, `created_at`, `updated_at`) VALUES
(1, 'Tri Setiawan', 'poseidonseal03@gmail.com', '082170710632', 'admin', '$2a$12$/kiKCntHFg1NaPjDil80J.pq8Tuf8UusLdi2iVAbHTJpdD9njqiYq', 'profile_1_1749906931.png', '2025-06-05 16:58:03', '2025-07-05 02:29:46'),
(11, 'Poseidon Seal1', 'tes123@gmail.com', '085219712554', 'admin', '$2y$10$CkTe4Tff4ZbThRzgspT3ru94bvKfCY6PiNOj5KjD0jG9NYqACYnPq', 'admin_11_1749294466_68441d82384ac.png', '2025-06-07 10:54:35', '2025-06-30 20:07:02'),
(12, 'Poseidon Seal1', 'poseidonseal03@gmail.com1', '085219712554', 'petugas', '$2y$10$.NiC3YmJcf/.qYBsjKzcrO9/zBKGUXyKJSL6pjPV7QXc1q1kWU3dG', 'petugas_1749539324_6847d9fcc701a.jpg', '2025-06-10 07:08:44', '2025-06-10 07:08:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_appsetting`
--
ALTER TABLE `tb_appsetting`
  ADD PRIMARY KEY (`id_appsetting`);

--
-- Indexes for table `tb_kegiatan`
--
ALTER TABLE `tb_kegiatan`
  ADD PRIMARY KEY (`id_kegiatan`);

--
-- Indexes for table `tb_penugasan`
--
ALTER TABLE `tb_penugasan`
  ADD PRIMARY KEY (`id_penugasan`),
  ADD KEY `id_kegiatan` (`id_kegiatan`),
  ADD KEY `id_pegawai` (`id_pegawai`);

--
-- Indexes for table `tb_user`
--
ALTER TABLE `tb_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_appsetting`
--
ALTER TABLE `tb_appsetting`
  MODIFY `id_appsetting` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tb_kegiatan`
--
ALTER TABLE `tb_kegiatan`
  MODIFY `id_kegiatan` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tb_penugasan`
--
ALTER TABLE `tb_penugasan`
  MODIFY `id_penugasan` bigint(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_user`
--
ALTER TABLE `tb_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

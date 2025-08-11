-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 02, 2025 at 09:00 AM
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
  `logo` text DEFAULT NULL,
  `name_header` varchar(255) DEFAULT NULL,
  `description_header` varchar(255) DEFAULT NULL,
  `background_header` text DEFAULT NULL,
  `video_header` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_appsetting`
--

INSERT INTO `tb_appsetting` (`id_appsetting`, `name`, `logo`, `name_header`, `description_header`, `background_header`, `video_header`) VALUES
(1, 'ICANG KONTOL', 'Logo1.png', NULL, NULL, NULL, NULL);

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
(1, 'UPACARA HARI LAHIR PANCASILA 2024', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', '2025-07-13 07:23:00', 'http://docs.google.com/spreadsheets/d/1FD1PfsdPiOPsTQb9l6oyh-mptjy9LzXt6rtybFF07G0/edit?resourcekey=&gid=1861385280#gid=1861385280', 'Snaptik.app_7319763115694968069.mp4', 'selesai', '2025-06-10 08:11:24', '2025-08-02 02:57:27'),
(4, 'tes eee', 'tesd123', '2025-07-13 04:23:00', 'http://docs.google.com/spreadsheets/d/1FD1PfsdPiOP...\n', 'kegiatan_4_1749975969_684e83a1e3603.mp4', 'pending', '2025-06-15 07:24:06', '2025-08-02 02:56:13'),
(5, 'tes eee1243', 'tes23', '2025-07-13 07:23:00', 'ets', 'kegiatan_1749976084_684e84141ff79.mp4', 'pending', '2025-06-15 08:28:04', '2025-07-13 14:01:01'),
(6, 'UPACARA HARI LAHIR PANCASILA 2024', 'UPACARA HARI LAHIR PANCASILA 2024', '2025-07-13 07:23:00', 'http://docs.google.com/spreadsheets/d/1FD1PfsdPiOPsTQb9l6oyh-mptjy9LzXt6rtybFF07G0/edit?resourcekey=&gid=1861385280#gid=1861385280', 'Snaptik.app_7319763115694968069.mp4', 'pending', '2025-06-10 08:11:24', '2025-07-13 14:01:02'),
(7, 'tes eee', 'tesd123', '2025-07-13 07:23:00', 'http://docs.google.com/spreadsheets/d/1FD1PfsdPiOP...\r\n', 'kegiatan_4_1749975969_684e83a1e3603.mp4', 'pending', '2025-06-15 07:24:06', '2025-07-13 14:01:04'),
(8, 'tes eee1243', 'tes23', '2025-07-13 07:23:00', 'ets', 'kegiatan_1749976084_684e84141ff79.mp4', 'pending', '2025-06-15 08:28:04', '2025-07-13 14:01:05'),
(9, 'TES HARI INI', 'tes', '2025-07-15 14:28:00', 'tes', 'kegiatan_1752416895_6873c27fb85a1.jpg', 'pending', '2025-07-13 14:28:15', '2025-07-13 14:28:15');

-- --------------------------------------------------------

--
-- Table structure for table `tb_penugasan`
--

CREATE TABLE `tb_penugasan` (
  `id_penugasan` bigint(11) NOT NULL,
  `id_kegiatan` bigint(11) NOT NULL,
  `id_pegawai` bigint(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_penugasan`
--

INSERT INTO `tb_penugasan` (`id_penugasan`, `id_kegiatan`, `id_pegawai`) VALUES
(2, 1, 14),
(4, 1, 12),
(5, 1, 15),
(6, 1, 16),
(7, 4, 12),
(8, 4, 14),
(9, 4, 15);

-- --------------------------------------------------------

--
-- Table structure for table `tb_user`
--

CREATE TABLE `tb_user` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `nohp` varchar(20) NOT NULL,
  `role` enum('admin','petugas','staf acara') NOT NULL DEFAULT 'petugas',
  `password` varchar(255) NOT NULL,
  `photo_profile` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tb_user`
--

INSERT INTO `tb_user` (`id`, `nama`, `email`, `nohp`, `role`, `password`, `photo_profile`, `created_at`, `updated_at`) VALUES
(1, 'Tri Setiawan', 'poseidonseal03@gmail.com', '082170710632', 'admin', '$2a$12$/kiKCntHFg1NaPjDil80J.pq8Tuf8UusLdi2iVAbHTJpdD9njqiYq', 'profile_1_1751690003.png', '2025-06-05 16:58:03', '2025-08-02 06:45:11'),
(11, 'Poseidon Seal1', 'tes123@gmail.com', '085219712554', 'admin', '$2y$10$CkTe4Tff4ZbThRzgspT3ru94bvKfCY6PiNOj5KjD0jG9NYqACYnPq', 'admin_11_1749294466_68441d82384ac.png', '2025-06-07 10:54:35', '2025-06-30 20:07:02'),
(12, 'Poseidon Seal1', 'poseidonseal03@gmail.com1', '085219712554', 'petugas', '$2y$10$.NiC3YmJcf/.qYBsjKzcrO9/zBKGUXyKJSL6pjPV7QXc1q1kWU3dG', 'petugas_1749539324_6847d9fcc701a.jpg', '2025-06-10 07:08:44', '2025-06-10 07:08:44'),
(14, 'Poseidon Seal2', 'poseidonseal03@gmail.com11', '085219712554', 'petugas', '$2y$10$.NiC3YmJcf/.qYBsjKzcrO9/zBKGUXyKJSL6pjPV7QXc1q1kWU3dG', 'petugas_1749539324_6847d9fcc701a.jpg', '2025-06-10 07:08:44', '2025-07-05 04:47:10'),
(15, 'Poseidon Seal3', 'poseidonseal03@gmail.com112', '085219712554', 'petugas', '$2y$10$.NiC3YmJcf/.qYBsjKzcrO9/zBKGUXyKJSL6pjPV7QXc1q1kWU3dG', 'petugas_1749539324_6847d9fcc701a.jpg', '2025-06-10 07:08:44', '2025-07-05 04:47:14'),
(16, 'Poseidon Seal4', 'poseidonseal03@gmail.com11122', '085219712554', 'petugas', '$2y$10$.NiC3YmJcf/.qYBsjKzcrO9/zBKGUXyKJSL6pjPV7QXc1q1kWU3dG', 'petugas_1749539324_6847d9fcc701a.jpg', '2025-06-10 07:08:44', '2025-07-05 04:47:17');

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
  MODIFY `id_kegiatan` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tb_penugasan`
--
ALTER TABLE `tb_penugasan`
  MODIFY `id_penugasan` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tb_user`
--
ALTER TABLE `tb_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

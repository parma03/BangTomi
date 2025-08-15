-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 15, 2025 at 09:03 AM
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
  `video_header` text DEFAULT NULL,
  `foto_tentang_kami` text DEFAULT NULL,
  `judul_tentang_kami` varchar(255) DEFAULT NULL,
  `deskripsi_tentang_kami` text DEFAULT NULL,
  `background_testimonial` varchar(255) DEFAULT '',
  `alamat` text DEFAULT '\'\'',
  `phone` varchar(50) DEFAULT '',
  `email` varchar(100) DEFAULT '',
  `twitter_link` varchar(255) DEFAULT '',
  `facebook_link` varchar(255) DEFAULT '',
  `instagram_link` varchar(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_appsetting`
--

INSERT INTO `tb_appsetting` (`id_appsetting`, `name`, `logo`, `name_header`, `description_header`, `background_header`, `video_header`, `foto_tentang_kami`, `judul_tentang_kami`, `deskripsi_tentang_kami`, `background_testimonial`, `alamat`, `phone`, `email`, `twitter_link`, `facebook_link`, `instagram_link`) VALUES
(1, 'KONTOL KUDA', 'Logo1.png', 'tes', 'Coba Deskripsi', 'bg_1755071878_689c458669973.jpg', 'video_1754123212_688dcbccd747a.mp4', 'about_1755177388_689de1aca8390.jpg', 'Tes 1 Tentang Kami', 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using \r\n\r\n\r\n\r\n\'Content here, content here\', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for \'lorem ipsum\' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).', 'testimonial_bg_1755187214_689e080e80b94.png', 'Jln.Andamar\r\nKamar', '+6285219712554', 'person.one@mails.com', 'http://localhost:55528/dashboard/admin/kontrolPanel.php', 'http://localhost:55528/dashboard/admin/kontrolPanel.php', 'http://localhost:55528/dashboard/admin/kontrolPanel.php');

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
(1, 'UPACARA HARI LAHIR PANCASILA 2024', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', '2025-08-13 07:23:00', 'http://docs.google.com/spreadsheets/d/1FD1PfsdPiOPsTQb9l6oyh-mptjy9LzXt6rtybFF07G0/edit?resourcekey=&gid=1861385280#gid=1861385280', 'Snaptik.app_7319763115694968069.mp4', 'pending', '2025-06-10 08:11:24', '2025-08-06 10:56:53'),
(4, 'tes eee', 'tesd123', '2025-07-13 04:23:00', 'http://docs.google.com/spreadsheets/d/1FD1PfsdPiOP...\n', 'kegiatan_4_1749975969_684e83a1e3603.mp4', 'selesai', '2025-06-15 07:24:06', '2025-08-11 14:44:55'),
(5, 'tes eee1243', 'tes23', '2025-07-13 07:23:00', 'ets', 'kegiatan_1749976084_684e84141ff79.mp4', 'selesai', '2025-06-15 08:28:04', '2025-08-15 05:08:04'),
(6, 'UPACARA HARI LAHIR PANCASILA 2024', 'UPACARA HARI LAHIR PANCASILA 2024', '2025-08-07 07:23:00', 'http://docs.google.com/spreadsheets/d/1FD1PfsdPiOPsTQb9l6oyh-mptjy9LzXt6rtybFF07G0/edit?resourcekey=&gid=1861385280#gid=1861385280', 'Snaptik.app_7319763115694968069.mp4', 'pending', '2025-06-10 08:11:24', '2025-08-06 10:53:44'),
(7, 'tes eee', 'tesd123', '2025-07-13 07:23:00', 'http://docs.google.com/spreadsheets/d/1FD1PfsdPiOP...\r\n', 'kegiatan_1752416895_6873c27fb85a1.jpg', 'selesai', '2025-06-15 07:24:06', '2025-08-15 06:46:14'),
(8, 'tes eee1243', 'tes23', '2025-07-13 07:23:00', 'ets', 'kegiatan_1749976084_684e84141ff79.mp4', 'pending', '2025-06-15 08:28:04', '2025-07-13 14:01:05'),
(9, 'TES HARI INI', 'tes', '2025-07-15 14:28:00', 'tes', 'kegiatan_1752416895_6873c27fb85a1.jpg', 'pending', '2025-07-13 14:28:15', '2025-07-13 14:28:15');

-- --------------------------------------------------------

--
-- Table structure for table `tb_komentar`
--

CREATE TABLE `tb_komentar` (
  `id_komentar` bigint(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `instansi` varchar(255) NOT NULL,
  `rating` int(11) NOT NULL,
  `komentar` text NOT NULL,
  `isShow` enum('true','false','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_komentar`
--

INSERT INTO `tb_komentar` (`id_komentar`, `nama`, `instansi`, `rating`, `komentar`, `isShow`) VALUES
(1, 'Icang', 'Sistem Analis', 5, 'is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum', 'true'),
(2, 'Tri Setiawan', 'Aqua Botol', 3, 'is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum', 'true'),
(3, 'Icang lagi', 'Rokok Surya', 2, 'The form action property is not set!', 'false');

-- --------------------------------------------------------

--
-- Table structure for table `tb_notification_log`
--

CREATE TABLE `tb_notification_log` (
  `id_log` bigint(11) NOT NULL,
  `id_kegiatan` bigint(11) NOT NULL,
  `id_petugas` int(11) NOT NULL,
  `recipient` varchar(50) NOT NULL COMMENT 'Group Chat ID',
  `status` enum('sent','failed') NOT NULL,
  `response_data` text DEFAULT NULL,
  `error_detail` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_notification_log`
--

INSERT INTO `tb_notification_log` (`id_log`, `id_kegiatan`, `id_petugas`, `recipient`, `status`, `response_data`, `error_detail`, `sent_at`) VALUES
(1, 4, 12, '-4929727465', 'sent', '{\"ok\":true,\"result\":{\"message_id\":6,\"from\":{\"id\":8483301260,\"is_bot\":true,\"first_name\":\"testingbot\",\"username\":\"IcangTestingBot\"},\"chat\":{\"id\":-4929727465,\"title\":\"Newbot\",\"type\":\"group\",\"all_members_are_administrators\":true,\"accepted_gift_types\":{\"unlimited_gifts\":false,\"limited_gifts\":false,\"unique_gifts\":false,\"premium_subscription\":false}},\"date\":1755070835,\"text\":\"\\ud83d\\udce2 NOTIFIKASI MANUAL KEGIATAN\\n\\n\\ud83d\\udccb Kegiatan: tes eee\\n\\ud83d\\udcc5 Jadwal: 13\\/07\\/2025 11:23\\n\\ud83d\\udcdd Deskripsi: tesd123...\\n\\n\\u26a0\\ufe0f Pengingat khusus untuk: Poseidon Seal1\\n\\n\\ud83d\\udcbc Mohon mempersiapkan diri dengan baik dan koordinasi dengan tim!\\n\\n\\ud83d\\udd17 Link Kehadiran: http:\\/\\/docs.google.com\\/spreadsheets\\/d\\/1FD1PfsdPiOP...\",\"entities\":[{\"offset\":3,\"length\":26,\"type\":\"bold\"},{\"offset\":34,\"length\":9,\"type\":\"bold\"},{\"offset\":55,\"length\":7,\"type\":\"bold\"},{\"offset\":83,\"length\":10,\"type\":\"bold\"},{\"offset\":109,\"length\":23,\"type\":\"bold\"},{\"offset\":220,\"length\":15,\"type\":\"bold\"},{\"offset\":236,\"length\":50,\"type\":\"url\"}],\"link_preview_options\":{\"is_disabled\":true}}}', '', '2025-08-13 02:40:36'),
(2, 4, 14, '-4929727465', 'sent', '{\"ok\":true,\"result\":{\"message_id\":6,\"from\":{\"id\":8483301260,\"is_bot\":true,\"first_name\":\"testingbot\",\"username\":\"IcangTestingBot\"},\"chat\":{\"id\":-4929727465,\"title\":\"Newbot\",\"type\":\"group\",\"all_members_are_administrators\":true,\"accepted_gift_types\":{\"unlimited_gifts\":false,\"limited_gifts\":false,\"unique_gifts\":false,\"premium_subscription\":false}},\"date\":1755070835,\"text\":\"\\ud83d\\udce2 NOTIFIKASI MANUAL KEGIATAN\\n\\n\\ud83d\\udccb Kegiatan: tes eee\\n\\ud83d\\udcc5 Jadwal: 13\\/07\\/2025 11:23\\n\\ud83d\\udcdd Deskripsi: tesd123...\\n\\n\\u26a0\\ufe0f Pengingat khusus untuk: Poseidon Seal1\\n\\n\\ud83d\\udcbc Mohon mempersiapkan diri dengan baik dan koordinasi dengan tim!\\n\\n\\ud83d\\udd17 Link Kehadiran: http:\\/\\/docs.google.com\\/spreadsheets\\/d\\/1FD1PfsdPiOP...\",\"entities\":[{\"offset\":3,\"length\":26,\"type\":\"bold\"},{\"offset\":34,\"length\":9,\"type\":\"bold\"},{\"offset\":55,\"length\":7,\"type\":\"bold\"},{\"offset\":83,\"length\":10,\"type\":\"bold\"},{\"offset\":109,\"length\":23,\"type\":\"bold\"},{\"offset\":220,\"length\":15,\"type\":\"bold\"},{\"offset\":236,\"length\":50,\"type\":\"url\"}],\"link_preview_options\":{\"is_disabled\":true}}}', '', '2025-08-13 02:40:36'),
(3, 4, 15, '-4929727465', 'sent', '{\"ok\":true,\"result\":{\"message_id\":6,\"from\":{\"id\":8483301260,\"is_bot\":true,\"first_name\":\"testingbot\",\"username\":\"IcangTestingBot\"},\"chat\":{\"id\":-4929727465,\"title\":\"Newbot\",\"type\":\"group\",\"all_members_are_administrators\":true,\"accepted_gift_types\":{\"unlimited_gifts\":false,\"limited_gifts\":false,\"unique_gifts\":false,\"premium_subscription\":false}},\"date\":1755070835,\"text\":\"\\ud83d\\udce2 NOTIFIKASI MANUAL KEGIATAN\\n\\n\\ud83d\\udccb Kegiatan: tes eee\\n\\ud83d\\udcc5 Jadwal: 13\\/07\\/2025 11:23\\n\\ud83d\\udcdd Deskripsi: tesd123...\\n\\n\\u26a0\\ufe0f Pengingat khusus untuk: Poseidon Seal1\\n\\n\\ud83d\\udcbc Mohon mempersiapkan diri dengan baik dan koordinasi dengan tim!\\n\\n\\ud83d\\udd17 Link Kehadiran: http:\\/\\/docs.google.com\\/spreadsheets\\/d\\/1FD1PfsdPiOP...\",\"entities\":[{\"offset\":3,\"length\":26,\"type\":\"bold\"},{\"offset\":34,\"length\":9,\"type\":\"bold\"},{\"offset\":55,\"length\":7,\"type\":\"bold\"},{\"offset\":83,\"length\":10,\"type\":\"bold\"},{\"offset\":109,\"length\":23,\"type\":\"bold\"},{\"offset\":220,\"length\":15,\"type\":\"bold\"},{\"offset\":236,\"length\":50,\"type\":\"url\"}],\"link_preview_options\":{\"is_disabled\":true}}}', '', '2025-08-13 02:40:36'),
(4, 4, 12, '-4929727465', 'sent', '{\"ok\":true,\"result\":{\"message_id\":7,\"from\":{\"id\":8483301260,\"is_bot\":true,\"first_name\":\"testingbot\",\"username\":\"IcangTestingBot\"},\"chat\":{\"id\":-4929727465,\"title\":\"Newbot\",\"type\":\"group\",\"all_members_are_administrators\":true,\"accepted_gift_types\":{\"unlimited_gifts\":false,\"limited_gifts\":false,\"unique_gifts\":false,\"premium_subscription\":false}},\"date\":1755070883,\"text\":\"\\ud83d\\udce2 NOTIFIKASI MANUAL KEGIATAN\\n\\n\\ud83d\\udccb Kegiatan: tes eee\\n\\ud83d\\udcc5 Jadwal: 13\\/07\\/2025 11:23\\n\\ud83d\\udcdd Deskripsi: tesd123...\\n\\n\\u26a0\\ufe0f Pengingat khusus untuk: Poseidon Seal2\\n\\n\\ud83d\\udcbc Mohon mempersiapkan diri dengan baik dan koordinasi dengan tim!\\n\\n\\ud83d\\udd17 Link Kehadiran: http:\\/\\/docs.google.com\\/spreadsheets\\/d\\/1FD1PfsdPiOP...\",\"entities\":[{\"offset\":3,\"length\":26,\"type\":\"bold\"},{\"offset\":34,\"length\":9,\"type\":\"bold\"},{\"offset\":55,\"length\":7,\"type\":\"bold\"},{\"offset\":83,\"length\":10,\"type\":\"bold\"},{\"offset\":109,\"length\":23,\"type\":\"bold\"},{\"offset\":220,\"length\":15,\"type\":\"bold\"},{\"offset\":236,\"length\":50,\"type\":\"url\"}],\"link_preview_options\":{\"is_disabled\":true}}}', '', '2025-08-13 02:41:24'),
(5, 4, 14, '-4929727465', 'sent', '{\"ok\":true,\"result\":{\"message_id\":7,\"from\":{\"id\":8483301260,\"is_bot\":true,\"first_name\":\"testingbot\",\"username\":\"IcangTestingBot\"},\"chat\":{\"id\":-4929727465,\"title\":\"Newbot\",\"type\":\"group\",\"all_members_are_administrators\":true,\"accepted_gift_types\":{\"unlimited_gifts\":false,\"limited_gifts\":false,\"unique_gifts\":false,\"premium_subscription\":false}},\"date\":1755070883,\"text\":\"\\ud83d\\udce2 NOTIFIKASI MANUAL KEGIATAN\\n\\n\\ud83d\\udccb Kegiatan: tes eee\\n\\ud83d\\udcc5 Jadwal: 13\\/07\\/2025 11:23\\n\\ud83d\\udcdd Deskripsi: tesd123...\\n\\n\\u26a0\\ufe0f Pengingat khusus untuk: Poseidon Seal2\\n\\n\\ud83d\\udcbc Mohon mempersiapkan diri dengan baik dan koordinasi dengan tim!\\n\\n\\ud83d\\udd17 Link Kehadiran: http:\\/\\/docs.google.com\\/spreadsheets\\/d\\/1FD1PfsdPiOP...\",\"entities\":[{\"offset\":3,\"length\":26,\"type\":\"bold\"},{\"offset\":34,\"length\":9,\"type\":\"bold\"},{\"offset\":55,\"length\":7,\"type\":\"bold\"},{\"offset\":83,\"length\":10,\"type\":\"bold\"},{\"offset\":109,\"length\":23,\"type\":\"bold\"},{\"offset\":220,\"length\":15,\"type\":\"bold\"},{\"offset\":236,\"length\":50,\"type\":\"url\"}],\"link_preview_options\":{\"is_disabled\":true}}}', '', '2025-08-13 02:41:24'),
(6, 4, 15, '-4929727465', 'sent', '{\"ok\":true,\"result\":{\"message_id\":7,\"from\":{\"id\":8483301260,\"is_bot\":true,\"first_name\":\"testingbot\",\"username\":\"IcangTestingBot\"},\"chat\":{\"id\":-4929727465,\"title\":\"Newbot\",\"type\":\"group\",\"all_members_are_administrators\":true,\"accepted_gift_types\":{\"unlimited_gifts\":false,\"limited_gifts\":false,\"unique_gifts\":false,\"premium_subscription\":false}},\"date\":1755070883,\"text\":\"\\ud83d\\udce2 NOTIFIKASI MANUAL KEGIATAN\\n\\n\\ud83d\\udccb Kegiatan: tes eee\\n\\ud83d\\udcc5 Jadwal: 13\\/07\\/2025 11:23\\n\\ud83d\\udcdd Deskripsi: tesd123...\\n\\n\\u26a0\\ufe0f Pengingat khusus untuk: Poseidon Seal2\\n\\n\\ud83d\\udcbc Mohon mempersiapkan diri dengan baik dan koordinasi dengan tim!\\n\\n\\ud83d\\udd17 Link Kehadiran: http:\\/\\/docs.google.com\\/spreadsheets\\/d\\/1FD1PfsdPiOP...\",\"entities\":[{\"offset\":3,\"length\":26,\"type\":\"bold\"},{\"offset\":34,\"length\":9,\"type\":\"bold\"},{\"offset\":55,\"length\":7,\"type\":\"bold\"},{\"offset\":83,\"length\":10,\"type\":\"bold\"},{\"offset\":109,\"length\":23,\"type\":\"bold\"},{\"offset\":220,\"length\":15,\"type\":\"bold\"},{\"offset\":236,\"length\":50,\"type\":\"url\"}],\"link_preview_options\":{\"is_disabled\":true}}}', '', '2025-08-13 02:41:24'),
(7, 4, 12, '-4929727465', 'sent', '{\"ok\":true,\"result\":{\"message_id\":9,\"from\":{\"id\":8483301260,\"is_bot\":true,\"first_name\":\"testingbot\",\"username\":\"IcangTestingBot\"},\"chat\":{\"id\":-4929727465,\"title\":\"Newbot\",\"type\":\"group\",\"all_members_are_administrators\":true,\"accepted_gift_types\":{\"unlimited_gifts\":false,\"limited_gifts\":false,\"unique_gifts\":false,\"premium_subscription\":false}},\"date\":1755070983,\"text\":\"\\ud83d\\udce2 NOTIFIKASI MANUAL KEGIATAN\\n\\n\\ud83d\\udccb Kegiatan: tes eee\\n\\ud83d\\udcc5 Jadwal: 13\\/07\\/2025 11:23\\n\\ud83d\\udcdd Deskripsi: tesd123...\\n\\n\\u26a0\\ufe0f Pengingat khusus untuk: Poseidon Seal2\\n\\n\\ud83d\\udcbc Mohon mempersiapkan diri dengan baik dan koordinasi dengan tim!\\n\\n\\ud83d\\udd17 Link Kehadiran: http:\\/\\/docs.google.com\\/spreadsheets\\/d\\/1FD1PfsdPiOP...\",\"entities\":[{\"offset\":3,\"length\":26,\"type\":\"bold\"},{\"offset\":34,\"length\":9,\"type\":\"bold\"},{\"offset\":55,\"length\":7,\"type\":\"bold\"},{\"offset\":83,\"length\":10,\"type\":\"bold\"},{\"offset\":109,\"length\":23,\"type\":\"bold\"},{\"offset\":220,\"length\":15,\"type\":\"bold\"},{\"offset\":236,\"length\":50,\"type\":\"url\"}],\"link_preview_options\":{\"is_disabled\":true}}}', '', '2025-08-13 02:43:03'),
(8, 4, 14, '-4929727465', 'sent', '{\"ok\":true,\"result\":{\"message_id\":9,\"from\":{\"id\":8483301260,\"is_bot\":true,\"first_name\":\"testingbot\",\"username\":\"IcangTestingBot\"},\"chat\":{\"id\":-4929727465,\"title\":\"Newbot\",\"type\":\"group\",\"all_members_are_administrators\":true,\"accepted_gift_types\":{\"unlimited_gifts\":false,\"limited_gifts\":false,\"unique_gifts\":false,\"premium_subscription\":false}},\"date\":1755070983,\"text\":\"\\ud83d\\udce2 NOTIFIKASI MANUAL KEGIATAN\\n\\n\\ud83d\\udccb Kegiatan: tes eee\\n\\ud83d\\udcc5 Jadwal: 13\\/07\\/2025 11:23\\n\\ud83d\\udcdd Deskripsi: tesd123...\\n\\n\\u26a0\\ufe0f Pengingat khusus untuk: Poseidon Seal2\\n\\n\\ud83d\\udcbc Mohon mempersiapkan diri dengan baik dan koordinasi dengan tim!\\n\\n\\ud83d\\udd17 Link Kehadiran: http:\\/\\/docs.google.com\\/spreadsheets\\/d\\/1FD1PfsdPiOP...\",\"entities\":[{\"offset\":3,\"length\":26,\"type\":\"bold\"},{\"offset\":34,\"length\":9,\"type\":\"bold\"},{\"offset\":55,\"length\":7,\"type\":\"bold\"},{\"offset\":83,\"length\":10,\"type\":\"bold\"},{\"offset\":109,\"length\":23,\"type\":\"bold\"},{\"offset\":220,\"length\":15,\"type\":\"bold\"},{\"offset\":236,\"length\":50,\"type\":\"url\"}],\"link_preview_options\":{\"is_disabled\":true}}}', '', '2025-08-13 02:43:03'),
(9, 4, 15, '-4929727465', 'sent', '{\"ok\":true,\"result\":{\"message_id\":9,\"from\":{\"id\":8483301260,\"is_bot\":true,\"first_name\":\"testingbot\",\"username\":\"IcangTestingBot\"},\"chat\":{\"id\":-4929727465,\"title\":\"Newbot\",\"type\":\"group\",\"all_members_are_administrators\":true,\"accepted_gift_types\":{\"unlimited_gifts\":false,\"limited_gifts\":false,\"unique_gifts\":false,\"premium_subscription\":false}},\"date\":1755070983,\"text\":\"\\ud83d\\udce2 NOTIFIKASI MANUAL KEGIATAN\\n\\n\\ud83d\\udccb Kegiatan: tes eee\\n\\ud83d\\udcc5 Jadwal: 13\\/07\\/2025 11:23\\n\\ud83d\\udcdd Deskripsi: tesd123...\\n\\n\\u26a0\\ufe0f Pengingat khusus untuk: Poseidon Seal2\\n\\n\\ud83d\\udcbc Mohon mempersiapkan diri dengan baik dan koordinasi dengan tim!\\n\\n\\ud83d\\udd17 Link Kehadiran: http:\\/\\/docs.google.com\\/spreadsheets\\/d\\/1FD1PfsdPiOP...\",\"entities\":[{\"offset\":3,\"length\":26,\"type\":\"bold\"},{\"offset\":34,\"length\":9,\"type\":\"bold\"},{\"offset\":55,\"length\":7,\"type\":\"bold\"},{\"offset\":83,\"length\":10,\"type\":\"bold\"},{\"offset\":109,\"length\":23,\"type\":\"bold\"},{\"offset\":220,\"length\":15,\"type\":\"bold\"},{\"offset\":236,\"length\":50,\"type\":\"url\"}],\"link_preview_options\":{\"is_disabled\":true}}}', '', '2025-08-13 02:43:03');

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
-- Table structure for table `tb_record_kegiatan`
--

CREATE TABLE `tb_record_kegiatan` (
  `id_foto_kegiatan` bigint(11) NOT NULL,
  `id_kegiatan` bigint(11) NOT NULL,
  `record_kegiatan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_record_kegiatan`
--

INSERT INTO `tb_record_kegiatan` (`id_foto_kegiatan`, `id_kegiatan`, `record_kegiatan`) VALUES
(6, 4, 'dokumentasi_4_1754923495_0.png'),
(7, 4, 'dokumentasi_4_1754923495_1.png'),
(8, 4, 'dokumentasi_4_1754923495_2.png'),
(9, 4, 'dokumentasi_4_1754923495_3.png'),
(10, 4, 'dokumentasi_4_1754923495_4.png'),
(11, 4, 'dokumentasi_4_1754923495_5.png'),
(12, 4, 'dokumentasi_4_1754923495_6.png'),
(13, 4, 'dokumentasi_4_1754923495_7.png'),
(14, 4, 'dokumentasi_4_1754923495_8.png'),
(15, 4, 'dokumentasi_4_1754923495_9.png'),
(16, 7, 'dokumentasi_7_1755226584_0.png'),
(17, 7, 'dokumentasi_7_1755226584_1.png'),
(18, 7, 'dokumentasi_7_1755226584_2.png'),
(19, 7, 'dokumentasi_7_1755226584_3.png'),
(20, 7, 'dokumentasi_7_1755226584_4.png'),
(21, 4, 'rekap_4_1755227351_0.png'),
(22, 4, 'rekap_4_1755227351_1.png'),
(25, 4, 'rekap_4_1755227399_0.mp4'),
(26, 4, 'rekap_4_1755234470_0.png'),
(27, 4, 'rekap_4_1755234470_1.png'),
(28, 4, 'rekap_4_1755234470_2.png'),
(29, 4, 'rekap_4_1755234470_3.png'),
(30, 5, 'dokumentasi_5_1755234484_0.png'),
(31, 5, 'dokumentasi_5_1755234484_1.png'),
(32, 5, 'dokumentasi_5_1755234484_2.png'),
(33, 5, 'dokumentasi_5_1755234484_3.png'),
(34, 5, 'dokumentasi_5_1755234484_4.png');

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
(1, 'Tri Setiawan1', 'poseidonseal03@gmail.com', '62821707106321', 'admin', '$2a$12$/kiKCntHFg1NaPjDil80J.pq8Tuf8UusLdi2iVAbHTJpdD9njqiYq', 'profile_1_1751690003.png', '2025-06-05 16:58:03', '2025-08-15 06:52:20'),
(11, 'Poseidon Seal1', 'tes123@gmail.com', '6282170710632', 'admin', '$2y$10$CkTe4Tff4ZbThRzgspT3ru94bvKfCY6PiNOj5KjD0jG9NYqACYnPq', 'admin_11_1755074305_689c4f019eac1.jpg', '2025-06-07 10:54:35', '2025-08-13 08:38:25'),
(12, 'Poseidon Seal1', 'poseidonseal03@gmail.com1', '6282170710632', 'petugas', '$2y$10$B9FR6Llv8nY8XkbdD57eZ.zQIBT6HotjttgNhvgGH.yDmoVUfx1o.', 'petugas_1749539324_6847d9fcc701a.jpg', '2025-06-10 07:08:44', '2025-08-15 06:51:07'),
(14, 'Poseidon Seal2', 'poseidonseal03@gmail.com11', '6282170710632', 'petugas', '$2y$10$.NiC3YmJcf/.qYBsjKzcrO9/zBKGUXyKJSL6pjPV7QXc1q1kWU3dG', 'petugas_1749539324_6847d9fcc701a.jpg', '2025-06-10 07:08:44', '2025-08-06 10:54:34'),
(15, 'Poseidon Seal3', 'poseidonseal03@gmail.com112', '6282170710632', 'petugas', '$2y$10$.NiC3YmJcf/.qYBsjKzcrO9/zBKGUXyKJSL6pjPV7QXc1q1kWU3dG', 'petugas_1749539324_6847d9fcc701a.jpg', '2025-06-10 07:08:44', '2025-08-06 10:54:32'),
(16, 'Poseidon Seal4', 'poseidonseal03@gmail.com11122', '6282170710632', 'petugas', '$2y$10$.NiC3YmJcf/.qYBsjKzcrO9/zBKGUXyKJSL6pjPV7QXc1q1kWU3dG', 'petugas_1749539324_6847d9fcc701a.jpg', '2025-06-10 07:08:44', '2025-08-06 10:54:36');

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
-- Indexes for table `tb_komentar`
--
ALTER TABLE `tb_komentar`
  ADD PRIMARY KEY (`id_komentar`);

--
-- Indexes for table `tb_notification_log`
--
ALTER TABLE `tb_notification_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `idx_kegiatan` (`id_kegiatan`),
  ADD KEY `idx_petugas` (`id_petugas`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_sent_at` (`sent_at`);

--
-- Indexes for table `tb_penugasan`
--
ALTER TABLE `tb_penugasan`
  ADD PRIMARY KEY (`id_penugasan`),
  ADD KEY `id_kegiatan` (`id_kegiatan`),
  ADD KEY `id_pegawai` (`id_pegawai`);

--
-- Indexes for table `tb_record_kegiatan`
--
ALTER TABLE `tb_record_kegiatan`
  ADD PRIMARY KEY (`id_foto_kegiatan`),
  ADD KEY `id_kegiatan` (`id_kegiatan`);

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
-- AUTO_INCREMENT for table `tb_komentar`
--
ALTER TABLE `tb_komentar`
  MODIFY `id_komentar` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tb_notification_log`
--
ALTER TABLE `tb_notification_log`
  MODIFY `id_log` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tb_penugasan`
--
ALTER TABLE `tb_penugasan`
  MODIFY `id_penugasan` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tb_record_kegiatan`
--
ALTER TABLE `tb_record_kegiatan`
  MODIFY `id_foto_kegiatan` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `tb_user`
--
ALTER TABLE `tb_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

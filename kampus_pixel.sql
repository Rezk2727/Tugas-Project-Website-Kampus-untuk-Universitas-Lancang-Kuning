-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 24, 2026 at 04:42 PM
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
-- Database: `kampus_pixel`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `message`, `is_active`, `created_at`) VALUES
(1, 'Selamat datang di Universitas Lancang Kuning', 0, '2026-07-22 13:58:14'),
(2, 'Selamat datang di Universitas Lancang Kuning', 1, '2026-07-22 13:58:24');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `event_id`, `user_id`, `parent_id`, `message`, `created_at`) VALUES
(1, 1, 3, NULL, 'Apakah saya masih  bisauntuk ikut serta dalam serangkaian acaraini?', '2026-07-20 22:23:42'),
(2, 1, 3, 1, 'Tentu', '2026-07-20 22:24:14'),
(4, 1, 3, 3, 'Ya,ada yang bisa saya,bantu?', '2026-07-20 22:37:33'),
(5, 1, 3, 3, 'Ya,ada yang bisa saya bantu?', '2026-07-20 22:38:01'),
(6, 1, 3, 3, 'Ada yang bisa saya abnyu?', '2026-07-20 22:38:36'),
(7, 1, 3, 1, 'YA', '2026-07-20 22:38:47'),
(8, 1, 3, 3, 'Halo!', '2026-07-20 23:18:45'),
(10, 2, 3, 9, 'Wah! terimakasih untuk dukungan,semoga acara wisudanya sukses untuk pelepasan Mahasiswa angkatan ke-23', '2026-07-20 23:56:42');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `category` enum('seminar','workshop','lomba','pelatihan') NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `event_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `category`, `title`, `description`, `event_date`, `created_at`) VALUES
(1, 'seminar', 'Seminar terbuka untuk umum tentang  perkembangan teknologi di Unversitas Potensi Utama', 'Pada acara ini,kita membuka seminar untuk umum yang tujuannya untuk meningkatkan relasi bagi orang-orang awam tentang perkembangan teknologi yang tiap tahunnya selalu ada perubahan dan peningkatan pada teknologi.', '2026-07-10', '2026-07-20 22:22:53'),
(2, 'workshop', 'Para Dosen sedang melakukan rapat untuk persiapan wisuda untuk Mahasiswa angkatan 2023', 'Denan adanya rapat ini,para dosen saling berdiskusi untuk perencanaan wisudah untuk Mahasiswa angkatan ke-23 atau bisa dikatakan Mahasiswwa tahun 2023.', '2026-07-24', '2026-07-20 23:54:03'),
(3, 'seminar', 'Seminar Game Developer Pemula', 'Para Game Developer sedang menyelenggarakan Seminar tebuka bagi para calon Game Developer pemula untuk mempelajari dasar-dasar pengembangan game hingga game tersebut bisa disiarkan ke publik.', '2026-07-30', '2026-07-22 10:21:13');

-- --------------------------------------------------------

--
-- Table structure for table `event_media`
--

CREATE TABLE `event_media` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` enum('image','video') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_media`
--

INSERT INTO `event_media` (`id`, `event_id`, `file_path`, `file_type`) VALUES
(1, 1, 'pixel_media_6a5e9fbda76065.45071095.jpg', 'image'),
(2, 2, 'pixel_media_6a5eb51b9f4ea3.96023135.jpg', 'image'),
(3, 3, 'pixel_media_6a6099991c1903.49389427.mp4', 'video');

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `lecturer_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `prodi` varchar(100) NOT NULL,
  `file_question` varchar(255) NOT NULL,
  `deadline` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_submissions`
--

CREATE TABLE `quiz_submissions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `file_answer` varchar(255) NOT NULL,
  `grade` int(11) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','student','lecturer') NOT NULL,
  `identity_code` varchar(50) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT 'default_pixel.png',
  `status` enum('active','banned') DEFAULT 'active',
  `last_seen` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `prodi` varchar(100) DEFAULT NULL,
  `class_name` varchar(50) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `identity_code`, `profile_photo`, `status`, `last_seen`, `created_at`, `prodi`, `class_name`, `subject`) VALUES
(3, 'admin', 'admin@gmail.com', '$2y$10$pFKoTmy8Sx/wbAFojUmUEOQVcsrLpLxQr56XGNILMp6B/BtfaI7VO', 'admin', NULL, 'pixel_media_6a5e9fb86a5da3.71285153.png', 'active', '2026-07-24 08:11:51', '2026-07-20 22:16:58', NULL, NULL, NULL),
(4, 'admiiin', 'admiiin@gmail.com', '$2y$10$kTxndrvBQQiEYXg3XMIcHeogj3/5S8Zcz.4A2VFMrtPztF68opjCe', 'admin', NULL, 'default_pixel.png', 'active', '2026-07-22 01:54:25', '2026-07-22 05:53:59', NULL, NULL, NULL),
(5, 'Ahmad Rezki Hamdallah', 'rezki@gmail.com', '$2y$10$w9Bm8IAQ19o8wGgdjQVXIepfL769KQUFm5e/H3ny3tfEWAI/5ytbO', 'student', '2412000067', 'pixel_media_6a617be9ab3747.90738462.png', 'active', '2026-07-22 22:29:50', '2026-07-23 02:26:04', 'Informatika', 'IF A Malam', ''),
(6, 'Hj Dr.Rezk2727 S.Kom ,.M.Kom', 'rezk2727@gmail.com', '$2y$10$xnIiOklxl0nJlxNqs2jIqulOa0R1X3qfIl5bN62O9kEzhWDTOQHGm', 'lecturer', '123', 'pixel_media_6a617c86b8d594.34563939.png', 'active', '2026-07-22 22:29:27', '2026-07-23 02:29:07', 'Informatika', '', 'Teknik Pengembangan Game');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_media`
--
ALTER TABLE `event_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lecturer_id` (`lecturer_id`);

--
-- Indexes for table `quiz_submissions`
--
ALTER TABLE `quiz_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`),
  ADD KEY `student_id` (`student_id`);

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
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `event_media`
--
ALTER TABLE `event_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `quiz_submissions`
--
ALTER TABLE `quiz_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_media`
--
ALTER TABLE `event_media`
  ADD CONSTRAINT `event_media_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_submissions`
--
ALTER TABLE `quiz_submissions`
  ADD CONSTRAINT `quiz_submissions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

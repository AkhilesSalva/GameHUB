-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2025 at 09:51 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_game_crud`
--

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `link_type` enum('single','part') NOT NULL DEFAULT 'single',
  `link_single` varchar(255) NOT NULL,
  `gambar_path` varchar(255) NOT NULL,
  `hero_image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `download_count` int(11) NOT NULL DEFAULT 0,
  `view_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`id`, `nama`, `deskripsi`, `link_type`, `link_single`, `gambar_path`, `hero_image_path`, `created_at`, `download_count`, `view_count`) VALUES
(2, 'Little Nightmare III', 'Game Petualangan horror adventure parah', 'single', 'https://pixeldrain.com/u/d2jBZ45E', 'assets/img/cover_68fe6c9bb036e.jpg', 'assets/img/cover_68fe6c9bb0725.jpg', '2025-10-26 18:46:51', 3, 87),
(3, 'Mortal Kombat XL', 'game gebuk-gebukan dan sebagainya', 'part', 'https://drive.google.com/file/d/1UXXfzFVaxE_G8AHZ8FPX43Ziu172UkUK/view,https://drive.google.com/file/d/17iIwpnHyX2UnXFYPPLtmo1C84ptIHrTA/view?usp=sharing', 'assets/img/img_68fe83858bb53.jpg', 'assets/img/img_68fe83858c068.jpg', '2025-10-26 20:24:37', 8, 25),
(4, 'Left 4 Dead 2', 'zombie bang', 'single', 'https://pixeldrain.com/u/d2jBZ45E', 'assets/img/img_68fe8a5d082be.jpg', 'assets/img/img_68fe8b13e7ca9.jpg', '2025-10-26 20:53:49', 2, 5),
(5, 'Outlast', 'game horror bang', 'single', 'https://pixeldrain.com/u/d2jBZ45E', 'assets/img/img_68fe8b96bed8d.jpg', 'assets/img/img_68fe8b96bf22e.jpg', '2025-10-26 20:59:02', 0, 0),
(6, 'Elden RIng', 'gitu dah', 'single', 'https://pixeldrain.com/u/d2jBZ45E', 'assets/img/img_68fe8c059570b.jpg', 'assets/img/img_68fe8c0595b8b.jpg', '2025-10-26 21:00:53', 0, 1),
(7, 'Minecraft Pocket Edition', 'mengcrafting abangkuh', 'single', 'https://pixeldrain.com/u/d2jBZ45E', 'assets/img/img_68fe8c780df65.jpg', 'assets/img/img_68fe8c780e268.jpg', '2025-10-26 21:02:48', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `game_genre`
--

CREATE TABLE `game_genre` (
  `game_id` int(11) NOT NULL,
  `genre_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `game_genre`
--

INSERT INTO `game_genre` (`game_id`, `genre_id`) VALUES
(2, 3),
(2, 5),
(3, 1),
(3, 6),
(4, 1),
(4, 2),
(4, 5),
(5, 5),
(6, 2),
(6, 6),
(7, 3);

-- --------------------------------------------------------

--
-- Table structure for table `genre`
--

CREATE TABLE `genre` (
  `id` int(11) NOT NULL,
  `nama_genre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `genre`
--

INSERT INTO `genre` (`id`, `nama_genre`) VALUES
(1, 'Action'),
(3, 'Adventure'),
(6, 'Fighting'),
(5, 'Horror'),
(2, 'RPG'),
(4, 'Simulation');

-- --------------------------------------------------------

--
-- Table structure for table `komentar`
--

CREATE TABLE `komentar` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `isi_komentar` text NOT NULL,
  `parent_id` int(11) DEFAULT 0,
  `is_admin_reply` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `komentar`
--

INSERT INTO `komentar` (`id`, `game_id`, `user_id`, `username`, `isi_komentar`, `parent_id`, `is_admin_reply`, `created_at`) VALUES
(1, 2, 0, 'Digidaw', 'bang, linknya gabisa bang...', 0, 0, '2025-10-27 02:16:14'),
(8, 2, 2, 'akhilessalv', 'udh bisa bang, cek aja linknya yg udh saya share.', 1, 1, '2025-10-27 04:09:49'),
(9, 2, 2, 'akhilessalv', 'nanti akan ada patch baru yah kedepannya', 0, 1, '2025-10-27 04:10:04');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`) VALUES
(2, 'akhilessalv', '$2y$10$zKpSpE9FguxBTVMPvlMxxOi0wK7EpDfONm3dAFos9XdDocyxp.Ci2', 'Akhiles Salva', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `game_genre`
--
ALTER TABLE `game_genre`
  ADD PRIMARY KEY (`game_id`,`genre_id`),
  ADD KEY `genre_id` (`genre_id`);

--
-- Indexes for table `genre`
--
ALTER TABLE `genre`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_genre` (`nama_genre`);

--
-- Indexes for table `komentar`
--
ALTER TABLE `komentar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `parent_id` (`parent_id`);

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
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `genre`
--
ALTER TABLE `genre`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `komentar`
--
ALTER TABLE `komentar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `game_genre`
--
ALTER TABLE `game_genre`
  ADD CONSTRAINT `game_genre_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `game_genre_ibfk_2` FOREIGN KEY (`genre_id`) REFERENCES `genre` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 03, 2026 at 12:37 PM
-- Server version: 8.0.45-0ubuntu0.24.04.1
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mi_spotify_tfg`
--

-- --------------------------------------------------------

--
-- Table structure for table `follows`
--

CREATE TABLE `follows` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `playlist_id` int DEFAULT NULL,
  `artist_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `song_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `playback_history`
--

CREATE TABLE `playback_history` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `song_id` int NOT NULL,
  `played_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `playlists`
--

CREATE TABLE `playlists` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `is_public` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `playlists`
--

INSERT INTO `playlists` (`id`, `user_id`, `name`, `description`, `is_public`, `created_at`) VALUES
(40, 9, 'TEST1', 'Esto es un test', 1, '2026-05-02 12:26:26'),
(41, 9, 'WEDFADFGSDFG', '', 1, '2026-05-02 16:27:13'),
(42, 9, 'ASDASDASDASD', '', 1, '2026-05-02 16:30:33');

-- --------------------------------------------------------

--
-- Table structure for table `playlist_songs`
--

CREATE TABLE `playlist_songs` (
  `id` int NOT NULL,
  `playlist_id` int NOT NULL,
  `song_id` int NOT NULL,
  `position` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `songs`
--

CREATE TABLE `songs` (
  `id` int NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `artist` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `album` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `duration` int DEFAULT NULL,
  `genre` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `audio_url` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `cover_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `stripe_subscription_id` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('active','canceled','past_due','trialing') COLLATE utf8mb4_general_ci NOT NULL,
  `current_period_end` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `google_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('user','admin') COLLATE utf8mb4_general_ci DEFAULT 'user',
  `is_premium` tinyint(1) NOT NULL DEFAULT '0',
  `premium_since` datetime DEFAULT NULL,
  `stripe_payment_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `subscription_status` enum('free','premium') COLLATE utf8mb4_general_ci DEFAULT 'free',
  `stripe_customer_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `google_id`, `role`, `is_premium`, `premium_since`, `stripe_payment_id`, `subscription_status`, `stripe_customer_id`, `created_at`) VALUES
(5, 'Virtu Dnb', 'virtudnb@gmail.com', NULL, '116863361522965567324', 'user', 0, NULL, NULL, 'free', NULL, '2026-02-14 22:39:03'),
(6, 'Pepe Tureatca', 'pepemaladin01@gmail.com', NULL, '101676639106574011772', 'user', 0, NULL, NULL, 'free', NULL, '2026-02-15 15:11:37'),
(7, 'Alexandru Pepe Tureatca', 'alexandrupepetureatca@gmail.com', NULL, '100479716282071127192', 'user', 0, NULL, NULL, 'free', NULL, '2026-02-15 15:12:59'),
(8, '1234', 'pepeturps4@gmail.com', NULL, '101512367024666536955', 'user', 0, NULL, NULL, 'free', NULL, '2026-04-25 10:15:33'),
(9, 'asd', 'ASDASD@GMAIL.COM', '$2y$10$mcfrteqwCbSPFcaXE9Zo3egXaA5RIdqyno.jNXcYuyaRamV99rtXO', NULL, 'user', 1, '2026-04-26 09:51:36', 'pi_3TQPLaE82ZzgUmDQ04tPm5TX', 'free', NULL, '2026-04-25 10:16:44'),
(10, 'asd213', 'ASDAasdSD@GMAIL.COM', '$2y$10$QwQeUjDb/CsYeioOKs1dBu1mDI127a231FwRg8/POPm5Kvq8WRZDK', NULL, 'user', 0, NULL, NULL, 'free', NULL, '2026-04-25 10:48:03'),
(11, 'PepeAdmin', 'pepeadmin@pepe.com', '$2y$10$Fg/VrTs2N1Tktm8jTtGEbOjamh4D8kJNdc2kuh7NZTXzE55cSUEyu', NULL, 'admin', 0, NULL, NULL, 'free', NULL, '2026-04-25 15:49:32'),
(12, 'David Calinescu', 'gavrinescuu__@gmail.com', '$2y$10$jz2jiykPZqkFZQskZOc40OBWQIutRmoVPp4QS0rOCuMSPSAYVrGMy', NULL, 'user', 0, NULL, NULL, 'free', NULL, '2026-04-26 09:54:31'),
(13, 'devAdmin', 'devAdmin@gmail.com', '$2y$10$bqTfJakegEFUoYdfk4/VH.ehOOlLnVB2bU0ZRzI..LEKcNPBxtQrK', NULL, 'admin', 0, NULL, NULL, 'free', NULL, '2026-04-28 20:27:53'),
(14, 'DAVID AFDASFADA', 'DAVIDAFDASFADA@GMAIL.COM', '$2y$10$QWlpaTi3qI71dlEiFOPakue/2gk9HrsxIGzUv4dzAm96MQl63LfCu', NULL, 'admin', 0, NULL, NULL, 'free', NULL, '2026-04-28 20:45:49'),
(15, 'usuarioTest', 'usuarioTest@gmail.com', '$2y$10$sH1lfZXe63blcztq0gqWB.H0YK66LYzp2IOIEmUDj8a9NTDwEQFFa', NULL, 'user', 0, NULL, NULL, 'free', NULL, '2026-05-03 10:23:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `playlist_id` (`playlist_id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_song` (`user_id`,`song_id`),
  ADD KEY `song_id` (`song_id`);

--
-- Indexes for table `playback_history`
--
ALTER TABLE `playback_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `song_id` (`song_id`);

--
-- Indexes for table `playlists`
--
ALTER TABLE `playlists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `playlist_songs`
--
ALTER TABLE `playlist_songs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `playlist_id` (`playlist_id`),
  ADD KEY `song_id` (`song_id`);

--
-- Indexes for table `songs`
--
ALTER TABLE `songs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `follows`
--
ALTER TABLE `follows`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `playback_history`
--
ALTER TABLE `playback_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `playlists`
--
ALTER TABLE `playlists`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `playlist_songs`
--
ALTER TABLE `playlist_songs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `songs`
--
ALTER TABLE `songs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `follows`
--
ALTER TABLE `follows`
  ADD CONSTRAINT `follows_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `follows_ibfk_2` FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `playback_history`
--
ALTER TABLE `playback_history`
  ADD CONSTRAINT `playback_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `playback_history_ibfk_2` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `playlists`
--
ALTER TABLE `playlists`
  ADD CONSTRAINT `playlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `playlist_songs`
--
ALTER TABLE `playlist_songs`
  ADD CONSTRAINT `playlist_songs_ibfk_1` FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `playlist_songs_ibfk_2` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

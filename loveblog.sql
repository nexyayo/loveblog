-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 07, 2025 at 09:22 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `loveblog`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Miłość', 'Posty związane z miłością i relacjami', '2025-03-16 19:30:15', '2025-03-16 19:30:15'),
(2, 'Związki', 'Posty o związkach i ich problemach', '2025-03-16 19:30:15', '2025-03-16 19:30:15'),
(3, 'Randki', 'Posty o randkach i spotkaniach', '2025-03-16 19:30:15', '2025-03-16 19:30:15'),
(4, 'Porady', 'Porady dotyczące relacji i miłości', '2025-03-16 19:30:15', '2025-03-16 19:30:15'),
(5, 'Inne', 'Inne tematy związane z relacjami', '2025-03-16 19:30:15', '2025-03-16 19:30:15');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'essa', '2025-03-16 19:40:02', '2025-03-16 19:40:02'),
(2, 3, 1, 'essa', '2025-03-16 20:44:58', '2025-03-16 20:44:58'),
(3, 3, 1, 'eee', '2025-03-16 20:45:00', '2025-03-16 20:45:00'),
(4, 3, 1, 'test', '2025-03-26 19:18:00', '2025-03-26 19:18:00');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `interest_categories`
--

CREATE TABLE `interest_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT 'fa-star',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interest_categories`
--

INSERT INTO `interest_categories` (`id`, `name`, `icon`, `created_at`, `updated_at`) VALUES
(1, 'Związki', 'fa-heart', '2025-03-16 18:59:56', '2025-03-16 18:59:56'),
(2, 'Randkowanie', 'fa-calendar-alt', '2025-03-16 18:59:56', '2025-03-16 18:59:56'),
(3, 'Miłość', 'fa-heart-circle', '2025-03-16 18:59:56', '2025-03-16 18:59:56'),
(4, 'Przyjaźń', 'fa-user-friends', '2025-03-16 18:59:56', '2025-03-16 18:59:56'),
(5, 'Technologia', 'fa-laptop', '2025-03-16 18:59:56', '2025-03-16 18:59:56'),
(6, 'Podróże', 'fa-plane', '2025-03-16 18:59:56', '2025-03-16 18:59:56'),
(7, 'Muzyka', 'fa-music', '2025-03-16 18:59:56', '2025-03-16 18:59:56'),
(8, 'Film', 'fa-film', '2025-03-16 18:59:56', '2025-03-16 18:59:56'),
(9, 'Książki', 'fa-book', '2025-03-16 18:59:56', '2025-03-16 18:59:56'),
(10, 'Sport', 'fa-running', '2025-03-16 18:59:56', '2025-03-16 18:59:56'),
(11, 'Gotowanie', 'fa-utensils', '2025-03-16 18:59:56', '2025-03-16 18:59:56'),
(12, 'Sztuka', 'fa-palette', '2025-03-16 18:59:56', '2025-03-16 18:59:56'),
(13, 'Fotografia', 'fa-camera', '2025-03-16 18:59:56', '2025-03-16 18:59:56'),
(14, 'Gry', 'fa-gamepad', '2025-03-16 18:59:56', '2025-03-16 18:59:56'),
(15, 'Zwierzęta', 'fa-paw', '2025-03-16 18:59:56', '2025-03-16 18:59:56'),
(16, 'Moda', 'fa-tshirt', '2025-03-16 18:59:56', '2025-03-16 18:59:56'),
(17, 'Zdrowie', 'fa-heartbeat', '2025-03-16 18:59:56', '2025-03-16 18:59:56'),
(18, 'Nauka', 'fa-microscope', '2025-03-16 18:59:56', '2025-03-16 18:59:56'),
(19, 'Biznes', 'fa-briefcase', '2025-03-16 18:59:56', '2025-03-16 18:59:56'),
(20, 'Edukacja', 'fa-graduation-cap', '2025-03-16 18:59:56', '2025-03-16 18:59:56');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`id`, `post_id`, `user_id`, `created_at`) VALUES
(13, 2, 1, '2025-03-16 20:33:18'),
(22, 3, 1, '2025-03-26 19:18:15');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(50) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `likes_count` int(11) DEFAULT 0,
  `comments_count` int(11) DEFAULT 0,
  `views_count` int(11) DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `title`, `content`, `category`, `image`, `likes_count`, `comments_count`, `views_count`, `created_at`, `updated_at`) VALUES
(1, 25, 'Jak znaleźć miłość w internecie?', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam auctor, nisl eget ultricies tincidunt, nisl nisl aliquam nisl, eget ultricies nisl nisl eget nisl. Nullam auctor, nisl eget ultricies tincidunt, nisl nisl aliquam nisl, eget ultricies nisl nisl eget nisl.', 'Miłość', NULL, 0, 1, 36, '2025-03-16 19:30:15', '2025-03-16 19:30:15'),
(2, 1, 'essa', '<p>essa</p>', 'Miłość', NULL, 1, 0, 21, '2025-03-16 19:48:08', '2025-03-16 19:48:08'),
(3, 1, 'essa', '<p>esaeaseas</p>', 'Związki', NULL, 1, 3, 43, '2025-03-16 20:33:36', '2025-03-16 20:33:36'),
(4, 1, 'tytul', '<p>tresc</p>', 'Porady', NULL, 0, 0, 0, '2025-04-01 16:14:34', '2025-04-01 16:14:34'),
(5, 1, 'test2', '<p>test2</p>', 'Randki', NULL, 0, 0, 1, '2025-04-07 15:08:57', '2025-04-07 15:08:57');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `age` int(3) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_image` text DEFAULT NULL,
  `banner_image` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `token_expires` datetime DEFAULT NULL,
  `interests` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`interests`)),
  `location` varchar(100) DEFAULT NULL,
  `social_links` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`social_links`)),
  `profile_views` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `age`, `email`, `password`, `gender`, `created_at`, `updated_at`, `profile_image`, `banner_image`, `bio`, `remember_token`, `token_expires`, `interests`, `location`, `social_links`, `profile_views`) VALUES
(1, 'Damian', 19, 'damiankow77@o2.pl', '$2y$10$LVCAM6tPKcgihh0sQBQbCenH7lztiGvmX1IOaPsNVJD6g5y4H6TNu', 'male', '2025-03-01 16:50:11', '2025-04-07 19:00:35', NULL, NULL, 'eeki', NULL, NULL, NULL, NULL, NULL, 170),
(2, 'szef', 19, 'szef@szef.pl', '$2y$10$ZVPj6MrkF6NuVCWWxPaJ2eYHX9Q6qPUC4X/wYL9TGODZJwD6RtUJC', 'male', '2025-03-01 16:55:24', '2025-03-16 20:27:50', NULL, NULL, 'esssa', '3a7407be528b36418f08c69be745a65ca2f5e05c5dfa636ae9d9cfa36b7ee7eb', '2025-03-16 20:57:11', NULL, NULL, NULL, 10),
(3, 'Kacper', 19, 'kacperzagrodzki73@gmail.com', '$2y$10$xnoCmYrVpUehu/9r9fEkdOUePInWhTD3/IhxBsRjoBY8srXM5ESqW', 'male', '2025-03-01 16:57:20', '2025-03-01 16:57:20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(4, 'test', 19, 'test@test.pl', '$2y$10$aq9SvgFKCwXZxNVOdnTvg.sTRx0jxsWnMCHfAn.0R3AMq.y5O68Bu', 'male', '2025-03-02 14:33:35', '2025-03-02 14:34:03', NULL, NULL, 'szef', NULL, NULL, NULL, NULL, NULL, 0),
(5, 'test', 19, 'test@testt.pl', '$2y$10$MLdTG4986nvXckhHTypKWeA4aaRLpXpBGI/97AAgoIqDx/juk6cVK', 'male', '2025-03-02 14:35:40', '2025-03-02 14:35:40', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(6, 'test', 19, 'test@testtt.pl', '$2y$10$YaX4IUS9tAwpxph0zveFWeg6wa4PpIACVttvOvq8NZh2NPq4B7pe.', 'male', '2025-03-02 14:37:13', '2025-03-02 14:37:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(7, 'test', 19, 'test@teeeesttt.pl', '$2y$10$pUCT7RQ05Aox.BOFisIzEe.0YwD02EHhTjZSdzeTt.SHN/30pSbAq', 'male', '2025-03-02 14:51:29', '2025-03-02 14:52:00', NULL, NULL, 'zagroda', NULL, NULL, NULL, NULL, NULL, 0),
(8, 'test', 19, 'test@teeeeeeesttt.pl', '$2y$10$WK41xxHXNztgIUEfr6YtTOcVu8BKhEkVopNAADjXEpUyLRAlu1Nxq', 'male', '2025-03-02 14:54:41', '2025-03-02 14:54:41', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(9, 'test', 19, 'essa@esse.pl', '$2y$10$M6Nk5Eu5iCnVzJTMFMvbyOj.BoV25XA1qgA0LmxyFQTBaOWj5eELG', 'male', '2025-03-02 14:55:20', '2025-03-02 14:55:20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(10, 'test', 19, 'essa@essee.pl', '$2y$10$Il7mvS1UEXzrDu9djHGeuug3Ez6h76Xrw.2QIXBWN8D16YLmIeew.', 'male', '2025-03-02 14:56:12', '2025-03-02 14:56:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(11, 'test', 19, 'essa@essee.plee', '$2y$10$zeZXrwNa4.yEVRg4H/T16.uJZAttCXjygu3v1L2FE0WaKZZjsBMm6', 'male', '2025-03-02 14:56:36', '2025-03-02 14:56:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(12, 'test', 19, 'essae@essee.plee', '$2y$10$azlR1XFr7EJcU.RSHrXAUeLka7D3Dh/GllfRDG.jJ82A69GyllDai', 'male', '2025-03-02 14:57:16', '2025-03-02 14:57:56', NULL, NULL, 'ig: k.zagrodzki', NULL, NULL, NULL, NULL, NULL, 0),
(13, 'test', 19, 'essasito@essasito.pl', '$2y$10$5TUSr3ipbCE3p1BEaqvBKeT1T/RXZISoS69O1sgiDFtYlCr2WSs2O', 'male', '2025-03-02 15:02:15', '2025-03-02 15:02:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(14, 'szef', 19, 'szef@szefff.pl', '$2y$10$k13xsJFj./m/se3mBrfyu.j6pHqxsaJxLIS4CiBm7j/qwlY4D9Is2', 'male', '2025-03-02 15:09:19', '2025-03-02 15:09:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(15, 'szef', 19, 'szef@szeffff.pl', '$2y$10$fOMqZx9g2yUavA6AgNoB9OnwDVbIxiRoqLv0pzKJc96R.XV9/3qNe', 'male', '2025-03-02 15:10:41', '2025-03-02 15:10:41', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(16, 'szef', 19, 'szef@szeffffff.pl', '$2y$10$MTGp8QhafktdClMtdhFIE.8Uebo/LX1yEXLM3tJssKS3t/Ru4S376', 'male', '2025-03-02 15:11:54', '2025-03-02 15:11:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(17, 'essa', 19, 'szef@szefffffffffffff.pl', '$2y$10$HYQGa/pUgCRlXvRv.MUGe.S3ea/qF4itzI583TgTeVkJ0JYylOMeS', 'male', '2025-03-02 15:21:10', '2025-03-02 15:21:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(18, 'Damain', 19, 'damianito@onet.pl', '$2y$10$4syNyFRWgYbRj7avGR0.L.MYGokoMvGEIKDU7Zuw3PwQCGfK14R.2', 'male', '2025-03-02 15:25:39', '2025-03-02 15:25:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(19, 'Damian', 19, 'dmnto@onet.pl', '$2y$10$3ZzwX8htCFJaCQ8RCUBDLOIfu2A9PSfBPnQwrrhfDY2VEDwdV9fZm', 'male', '2025-03-02 15:27:30', '2025-03-02 15:27:42', NULL, NULL, 'ghfghn', NULL, NULL, NULL, NULL, NULL, 0),
(20, 'Essa', 19, 'matisz@matisz.pl', '$2y$10$uuPeidBQBl7M0NHC0uio/OeW2vVeqNC25kzDYq1Neeq35fC3/pfQC', 'male', '2025-03-02 15:33:27', '2025-03-02 15:34:49', NULL, NULL, 'jnj', '429fc43e37f16cea3852f35affb95ae7e9d282d11d57dde98b12bc33c322f34f', '2025-04-01 17:34:49', NULL, NULL, NULL, 0),
(21, 'Kacper', 19, 'k.zagrod@onet.pl', '$2y$10$WmeqmgLx2PrqWrMeKRU1ae1WrZHyf6Gw0.zP.ONXJPXK1Fv8XZJo2', 'male', '2025-03-02 15:38:51', '2025-03-02 15:39:14', NULL, NULL, NULL, '693b3c57b7e3492d53133a0f2502cfab8f9d63a6db9caa8a1836ea6eb2b50ec8', '2025-04-01 17:39:14', NULL, NULL, NULL, 0),
(22, 'Skolim', 19, 'skolim@skolim.pl', '$2y$10$UVXm/ckh4RDnOCf1Gq1.g.arcUgVsbm1a.dbCN9QvsZaemK1WYbx.', 'male', '2025-03-02 18:52:25', '2025-03-02 18:52:58', NULL, NULL, NULL, '1a8b77f60037f31af3f9775d8387c68baf4942203fd142ba30c28e8626529def', '2025-04-01 20:52:58', NULL, NULL, NULL, 0),
(23, 'Szef', 19, 'szef@szefito.pl', '$2y$10$Z42bsNmPuaacXRnC3KWM4Ov.V8HCNI1vHrpnJHfUX0.ZErgeuRO6K', 'male', '2025-03-03 17:14:51', '2025-03-03 17:15:14', NULL, NULL, NULL, '7ef432cef30b208e793dc65c8c35f433ad0c890d2b21c503878a9b558779b298', '2025-04-02 19:15:14', NULL, NULL, NULL, 0),
(24, 'nigger', 19, 'nigger@nigger.pl', '$2y$10$IdwCDekDq7sgRfg0BuObA.vtuyXXrrTrGBO8jCxtTi2hWK2uQAOAC', 'male', '2025-03-04 19:10:19', '2025-03-04 19:10:38', NULL, NULL, NULL, '75981c80d2a0f25b2e6b29b7ae296d7322a2f4eff852fbe9368e1dfeced9e504', '2025-04-03 21:10:38', NULL, NULL, NULL, 0),
(25, 'Bartek', 19, 'bartekczerwinski66@gmail.com', '$2y$10$6YNjxUK/N5eUBaIESOOJTevNC.F7PJxa/kGl/pIqEixVtkpdpCuo.', 'male', '2025-03-16 17:46:38', '2025-03-16 17:47:00', NULL, NULL, NULL, 'e6eff04e835ad73f53d9f971807fa8d045f660c072b493d221fb235b7fea1765', '2025-03-16 18:57:00', NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `user_interests`
--

CREATE TABLE `user_interests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_interests`
--

INSERT INTO `user_interests` (`id`, `user_id`, `category_name`) VALUES
(84, 1, 'Inne'),
(85, 1, 'Miłość'),
(86, 1, 'Randki'),
(87, 1, 'Związki'),
(46, 2, 'Inne'),
(47, 2, 'Miłość'),
(48, 2, 'Porady'),
(49, 2, 'Randki'),
(50, 2, 'Związki');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeksy dla tabeli `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `interest_categories`
--
ALTER TABLE `interest_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `post_user_unique` (`post_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category` (`category`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeksy dla tabeli `user_interests`
--
ALTER TABLE `user_interests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_category` (`user_id`,`category_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `interest_categories`
--
ALTER TABLE `interest_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `user_interests`
--
ALTER TABLE `user_interests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_interests`
--
ALTER TABLE `user_interests`
  ADD CONSTRAINT `user_interests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

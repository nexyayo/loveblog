-- Tworzenie tabeli posts, jeśli nie istnieje
CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(50) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `likes_count` int(11) DEFAULT 0,
  `comments_count` int(11) DEFAULT 0,
  `views_count` int(11) DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `category` (`category`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tworzenie tabeli comments, jeśli nie istnieje
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tworzenie tabeli likes, jeśli nie istnieje
CREATE TABLE IF NOT EXISTS `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_user_unique` (`post_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tworzenie tabeli categories, jeśli nie istnieje
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dodanie przykładowych kategorii
INSERT INTO `categories` (`name`, `description`, `created_at`, `updated_at`) VALUES
('Miłość', 'Posty związane z miłością i relacjami', NOW(), NOW()),
('Związki', 'Posty o związkach i ich problemach', NOW(), NOW()),
('Randki', 'Posty o randkach i spotkaniach', NOW(), NOW()),
('Porady', 'Porady dotyczące relacji i miłości', NOW(), NOW()),
('Inne', 'Inne tematy związane z relacjami', NOW(), NOW());

-- Dodanie przykładowych postów (jeśli tabela jest pusta)
INSERT INTO `posts` (`user_id`, `title`, `content`, `category`, `image`, `created_at`, `updated_at`)
SELECT 
    (SELECT id FROM users LIMIT 1), 
    'Jak znaleźć miłość w internecie?', 
    'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam auctor, nisl eget ultricies tincidunt, nisl nisl aliquam nisl, eget ultricies nisl nisl eget nisl. Nullam auctor, nisl eget ultricies tincidunt, nisl nisl aliquam nisl, eget ultricies nisl nisl eget nisl.', 
    'Miłość', 
    NULL, 
    NOW(), 
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM posts LIMIT 1);

INSERT INTO `posts` (`user_id`, `title`, `content`, `category`, `image`, `created_at`, `updated_at`)
SELECT 
    (SELECT id FROM users LIMIT 1), 
    'Jak rozpoznać toksyczny związek?', 
    'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam auctor, nisl eget ultricies tincidunt, nisl nisl aliquam nisl, eget ultricies nisl nisl eget nisl. Nullam auctor, nisl eget ultricies tincidunt, nisl nisl aliquam nisl, eget ultricies nisl nisl eget nisl.', 
    'Związki', 
    NULL, 
    NOW(), 
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM posts LIMIT 1);

INSERT INTO `posts` (`user_id`, `title`, `content`, `category`, `image`, `created_at`, `updated_at`)
SELECT 
    (SELECT id FROM users LIMIT 1), 
    'Najlepsze miejsca na pierwszą randkę', 
    'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam auctor, nisl eget ultricies tincidunt, nisl nisl aliquam nisl, eget ultricies nisl nisl eget nisl. Nullam auctor, nisl eget ultricies tincidunt, nisl nisl aliquam nisl, eget ultricies nisl nisl eget nisl.', 
    'Randki', 
    NULL, 
    NOW(), 
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM posts LIMIT 1); 
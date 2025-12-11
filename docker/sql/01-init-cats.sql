SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

-- USERS first
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
                         `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                         `email` VARCHAR(255) NOT NULL UNIQUE,
                         `username` VARCHAR(100) NOT NULL,
                         `password` VARCHAR(255) NOT NULL, -- sem sa ukladá HASH, nie heslo
                         `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                         `posts_count` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default user
INSERT INTO `users` (`email`, `username`, `password`) VALUES
    ('admin@cats.com', 'admin', '$2y$12$SyF/DkwindkP.TJ3/fUkK.gT9h08.kttZ8HzxMH3nRqdKrqET4pEu');
-- hash pre heslo 'heslo123'

-- CATS
DROP TABLE IF EXISTS `cats`;
CREATE TABLE `cats` (
                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                        `meno` VARCHAR(255) NOT NULL,
                        `text` TEXT,
                        `status` VARCHAR(100),
                        `kastrovana` TINYINT(1) NOT NULL DEFAULT 0,
                        `fotka` VARCHAR(255),
                        `user_id` INT UNSIGNED NULL,
                        PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- LOCATIONS
DROP TABLE IF EXISTS `locations`;
CREATE TABLE `locations` (
                             `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                             `cat_id` INT UNSIGNED NOT NULL,
                             `city` VARCHAR(255) NOT NULL,
                             `latitude` DOUBLE NOT NULL,
                             `longitude` DOUBLE NOT NULL,
                             PRIMARY KEY (`id`),
                             FOREIGN KEY (`cat_id`) REFERENCES `cats`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- POSTS
DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
                         `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                         `cat_id` INT UNSIGNED NOT NULL,
                         `user_id` INT UNSIGNED NOT NULL,
                         `title` VARCHAR(255) NOT NULL,
                         `content` TEXT NOT NULL,
                         `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                         PRIMARY KEY (`id`),
                         FOREIGN KEY (`cat_id`) REFERENCES `cats`(`id`) ON DELETE CASCADE,
                         FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- FK from cats.user_id to users.id
ALTER TABLE `cats` ADD CONSTRAINT fk_cats_user
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- Insert initial cats assigned to default user (id=1)
INSERT INTO `cats` (`meno`, `text`, `status`, `kastrovana`, `fotka`, `user_id`) VALUES
                                                                                    ('Belka', 'Plachá mačka, ktorá už pribrala a vyzerá zdravo.', 'pozorovaná', 1, '/uploads/cat-3845851_1280.jpg', 1),
                                                                                    ('Bodka', 'Nezávislá, niekedy sa nechá pohladkať.', 'pozorovaná', 1, '/uploads/cat-7619000_1280.jpg', 1),
                                                                                    ('Kubo', 'Veľmi maznavý kocúr.', 'pozorovaná', 1, '/uploads/cat-7162788_1280.jpg', 1),
                                                                                    ('Micka', 'Divoká mačka, ktorá sa bojí ľudí.', NULL, 0, NULL, 1);

-- Insert locations
INSERT INTO `locations` (`cat_id`, `city`, `latitude`, `longitude`) VALUES
                                                                        (1, 'Bratislava', 48.1486, 17.1077),
                                                                        (2, 'Košice', 48.7164, 21.2611),
                                                                        (3, 'Žilina', 49.2235, 18.7390),
                                                                        (4, 'Nitra', 48.3091, 18.0840);

SET foreign_key_checks = 1;

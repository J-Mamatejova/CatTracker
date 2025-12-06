-- Init script for MariaDB / MySQL: create database 'cats' and table 'cats'
-- This script is placed in docker/sql/ and will be executed by the official MariaDB image
-- during the first container initialization (when the DB data directory is empty).

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `cats`;
CREATE TABLE `cats` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `meno` VARCHAR(255) NOT NULL,
  `text` TEXT,
  `status` VARCHAR(100),
  `kastrovana` TINYINT(1) NOT NULL DEFAULT 0,
  `fotka` VARCHAR(255),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- Notes:
--  - kastrovana uses TINYINT(1) (0=false, 1=true) which is typical MySQL boolean.
--  - If you already use MARIADB_DATABASE environment variable to create DB, you may keep that
--    and the CREATE DATABASE will not cause issues because of IF NOT EXISTS.
--  - Scripts in /docker-entrypoint-initdb.d run only on first initialization; to re-run,
--    remove the DB volume (or delete existing DB files) and recreate the container.

INSERT INTO `cats` (`meno`, `text`, `status`, `kastrovana`, `fotka`) VALUES
                                                ('Belle', 'Plachá mačka, ktorá už pribrala a vyzerá zdravo.', 'pozorovaná', 1, '/uploads/cat-3845851_1280.jpg'),
                                                ('Dot', 'Nezávislá, niekedy sa nechá pohladkať.', 'pozorovaná', 1, '/uploads/cat-7619000_1280.jpg'),
                                                ('Kubo', 'Veľmi maznavý kocúr.', 'pozorovaná', 1, '/uploads/cat-7162788_1280.jpg');

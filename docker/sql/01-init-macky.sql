-- Init script for MariaDB / MySQL: create database 'cats' and table 'macky'
-- This script is placed in docker/sql/ and will be executed by the official MariaDB image
-- during the first container initialization (when the DB data directory is empty).

CREATE DATABASE IF NOT EXISTS `cats`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `cats`;

CREATE TABLE IF NOT EXISTS `macky` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `meno` VARCHAR(255) NOT NULL,
  `text` TEXT,
  `status` VARCHAR(100),
  `kastrovana` TINYINT(1) NOT NULL DEFAULT 0,
  `fotka` VARCHAR(255),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notes:
--  - kastrovana uses TINYINT(1) (0=false, 1=true) which is typical MySQL boolean.
--  - If you already use MARIADB_DATABASE environment variable to create DB, you may keep that
--    and the CREATE DATABASE will not cause issues because of IF NOT EXISTS.
--  - Scripts in /docker-entrypoint-initdb.d run only on first initialization; to re-run,
--    remove the DB volume (or delete existing DB files) and recreate the container.


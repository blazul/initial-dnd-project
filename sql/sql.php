<?php
// sql/sql.php

$create = [];

// users
$create[] = "
CREATE TABLE `{$prefix}users` (
  `user_id`   INT AUTO_INCREMENT PRIMARY KEY,
  `username`  VARCHAR(50)  NOT NULL UNIQUE,
  `email`     VARCHAR(100) NOT NULL UNIQUE,
  `password`  VARCHAR(255) NOT NULL,
  `role`      ENUM('admin','user') NOT NULL DEFAULT 'user',
  `is_active` TINYINT(1)  NOT NULL DEFAULT 1,
  `created_at` DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// campaigns
$create[] = "
CREATE TABLE `{$prefix}campaigns` (
  `campaign_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`     INT        NOT NULL,
  `name`        VARCHAR(100) NOT NULL,
  `description` TEXT,
  `created_at`  DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX(`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `{$prefix}users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// characters
$create[] = "
CREATE TABLE `{$prefix}characters` (
  `character_id`    INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`         INT NOT NULL,
  `campaign_id`     INT NULL,
  `name`            VARCHAR(100) NOT NULL,
  `race`            VARCHAR(50),
  `character_class` VARCHAR(50),
  `level`           INT DEFAULT 1,
  `background`      VARCHAR(50),
  `alignment`       VARCHAR(50),
  `armor_class`     INT DEFAULT 10,
  `hit_points`      INT DEFAULT 10,
  `speed`           INT DEFAULT 30,
  `initiative`      INT DEFAULT 0,
  `description`     TEXT,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX(`user_id`),
  INDEX(`campaign_id`),
  FOREIGN KEY (`user_id`)     REFERENCES `{$prefix}users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`campaign_id`) REFERENCES `{$prefix}campaigns`(`campaign_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// attributes
$create[] = "
CREATE TABLE `{$prefix}attributes` (
  `character_id` INT PRIMARY KEY,
  `strength`     INT NOT NULL,
  `dexterity`    INT NOT NULL,
  `constitution` INT NOT NULL,
  `intelligence` INT NOT NULL,
  `wisdom`       INT NOT NULL,
  `charisma`     INT NOT NULL,
  FOREIGN KEY (`character_id`) REFERENCES `{$prefix}characters`(`character_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// spells
$create[] = "
CREATE TABLE `{$prefix}spells` (
  `spell_id`    INT AUTO_INCREMENT PRIMARY KEY,
  `character_id` INT NOT NULL,
  `name`        VARCHAR(100) NOT NULL,
  `level`       INT DEFAULT 0,
  `school`      VARCHAR(50),
  `casting_time` VARCHAR(50),
  `range`       VARCHAR(50),
  `components`  VARCHAR(50),
  `duration`    VARCHAR(50),
  `description` TEXT,
  INDEX(`character_id`),
  FOREIGN KEY (`character_id`) REFERENCES `{$prefix}characters`(`character_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// skills
$create[] = "
CREATE TABLE `{$prefix}skills` (
  `skill_id`     INT AUTO_INCREMENT PRIMARY KEY,
  `character_id` INT NOT NULL,
  `name`         VARCHAR(100) NOT NULL,
  `modifier`     INT DEFAULT 0,
  INDEX(`character_id`),
  FOREIGN KEY (`character_id`) REFERENCES `{$prefix}characters`(`character_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// campaign_shares
$create[] = "
CREATE TABLE `{$prefix}campaign_shares` (
  `campaign_id` INT NOT NULL,
  `user_id`     INT NOT NULL,
  PRIMARY KEY (`campaign_id`,`user_id`),
  FOREIGN KEY (`campaign_id`) REFERENCES `{$prefix}campaigns`(`campaign_id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`)     REFERENCES `{$prefix}users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// friendships
$create[] = "
CREATE TABLE `{$prefix}friendships` (
  `user_id`    INT NOT NULL,
  `friend_id`  INT NOT NULL,
  `status`     ENUM('pending','accepted') NOT NULL DEFAULT 'pending',
  `created_at` DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`friend_id`),
  FOREIGN KEY (`user_id`)   REFERENCES `{$prefix}users`(`user_id`)   ON DELETE CASCADE,
  FOREIGN KEY (`friend_id`) REFERENCES `{$prefix}users`(`user_id`)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

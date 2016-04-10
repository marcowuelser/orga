# System

# System
CREATE TABLE IF NOT EXISTS `s_system` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `maintenance` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB DEFAULT CHARSET=utf8;

# Users
CREATE TABLE IF NOT EXISTS `s_user` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(45) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `role_flags` INT NOT NULL,
  `token` VARCHAR(255) NULL,
  `token_expire` DATETIME NULL,
  `created` DATETIME NULL,
  `updated` DATETIME NULL,
  `default_order` INT NOT NULL,
  `active` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `username_UNIQUE` (`username` ASC))
ENGINE = InnoDB DEFAULT CHARSET=utf8;
INSERT INTO s_user (username, password, role_flags, defaultOrder, active) VALUES('root', PASSWORD('password'), 3, 0, 1);

# Ruleset

# Ruleset
CREATE TABLE IF NOT EXISTS `r_ruleset` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `caption` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `created` DATETIME NULL,
  `updated` DATETIME NULL,
  `default_order` INT NOT NULL,
  `active` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB DEFAULT CHARSET=utf8;

# Entity
CREATE TABLE IF NOT EXISTS `r_entity` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `ruleset_id` INT NOT NULL,
  `entity_type_id` INT NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `description` TEXT NOT NULL,
  `created` DATETIME NULL,
  `updated` DATETIME NULL,
  `default_order` INT NOT NULL,
  `active` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB DEFAULT CHARSET=utf8;

# Field
CREATE TABLE IF NOT EXISTS `r_entity_field` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `entity_id` INT NOT NULL,
  `field_type_id` INT NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `description` TEXT NOT NULL,
  `created` DATETIME NULL,
  `updated` DATETIME NULL,
  `default_order` INT NOT NULL,
  `active` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB DEFAULT CHARSET=utf8;

# Instance
CREATE TABLE IF NOT EXISTS `r_entity_instance` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `entity_id` INT NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `created` DATETIME NULL,
  `updated` DATETIME NULL,
  `default_order` INT NOT NULL,
  `active` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB DEFAULT CHARSET=utf8;

# Value
CREATE TABLE IF NOT EXISTS `r_entity_instance_value` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `entity_instance_id` INT NOT NULL,
  `entity_field_id` INT NOT NULL,
  `value` VARCHAR(45) NOT NULL,
  `created` DATETIME NULL,
  `updated` DATETIME NULL,
  `default_order` INT NOT NULL,
  `active` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB DEFAULT CHARSET=utf8;

# Game

# Game
CREATE TABLE IF NOT EXISTS `g_game` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `ruleset_id` INT NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `caption` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `created` DATETIME NULL,
  `updated` DATETIME NULL,
  `default_order` INT NOT NULL,
  `active` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB DEFAULT CHARSET=utf8;

# Player
CREATE TABLE IF NOT EXISTS `g_player` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `game_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `role_flags` INT NOT NULL,
  `created` DATETIME NULL,
  `updated` DATETIME NULL,
  `default_order` INT NOT NULL,
  `active` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB DEFAULT CHARSET=utf8;

# Character
CREATE TABLE IF NOT EXISTS `g_character` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `game_id` INT NOT NULL,
  `player_id` INT NOT NULL,
  `name_short` VARCHAR(45) NOT NULL,
  `name_full` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `created` DATETIME NULL,
  `updated` DATETIME NULL,
  `default_order` INT NOT NULL,
  `active` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB DEFAULT CHARSET=utf8;

# Boards

# Board
CREATE TABLE IF NOT EXISTS `s_board` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `scope_id` INT NOT NULL,
  `relation_id` INT NULL,
  `parent_id` INT NULL,
  `name` VARCHAR(45) NOT NULL,
  `caption` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `created` DATETIME NULL,
  `updated` DATETIME NULL,
  `default_order` INT NOT NULL,
  `active` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB DEFAULT CHARSET=utf8;

# Board Entry
CREATE TABLE IF NOT EXISTS `s_board_entry` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `board_id` INT NOT NULL,
  `parent_id` INT NULL,
  `creator_id` INT NOT NULL,
  `updater_id` INT NULL,
  `caption` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `locked` BOOLEAN NOT NULL,
  `created` DATETIME NULL,
  `updated` DATETIME NULL,
  `default_order` INT NOT NULL,
  `active` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB DEFAULT CHARSET=utf8;

# Messages

# Message
CREATE TABLE IF NOT EXISTS `s_message` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `game_id` INT NOT NULL,
  `scope_id` INT NOT NULL,
  `creator_id` INT NOT NULL,
  `destination_id` INT NOT NULL,
  `caption` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `created` DATETIME NULL,
  `updated` DATETIME NULL,
  `default_order` INT NOT NULL,
  `active` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB DEFAULT CHARSET=utf8;

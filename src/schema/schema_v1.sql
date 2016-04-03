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
  `role_id` INT NOT NULL,
  `defaultOrder` INT NOT NULL,
  `active` BOOLEAN NOT NULL,
  `token` VARCHAR(255) NULL,
  `token_expire` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `username_UNIQUE` (`username` ASC))
ENGINE = InnoDB DEFAULT CHARSET=utf8;
INSERT INTO s_user (username, password, role_id, defaultOrder, active) VALUES('root', PASSWORD('password'), 3, 0, 1);

# Ruleset

# Ruleset
CREATE TABLE IF NOT EXISTS `r_ruleset` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `caption` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `created` DATETIME NULL,
  `updated` DATETIME NULL,
  `defaultOrder` INT NOT NULL,
  `active` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `r_entity` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `ruleset_id` INT NOT NULL,
  `entity_type_id` INT NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `description` TEXT NOT NULL,
  `created` DATETIME NULL,
  `updated` DATETIME NULL,
  `defaultOrder` INT NOT NULL,
  `active` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `r_entity_field` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `entity_id` INT NOT NULL,
  `field_type_id` INT NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `description` TEXT NOT NULL,
  `created` DATETIME NULL,
  `updated` DATETIME NULL,
  `defaultOrder` INT NOT NULL,
  `active` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `r_entity_instance` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `entity_id` INT NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `created` DATETIME NULL,
  `updated` DATETIME NULL,
  `defaultOrder` INT NOT NULL,
  `active` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `r_entity_instance_value` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `entity_instance_id` INT NOT NULL,
  `entity_field_id` INT NOT NULL,
  `value` VARCHAR(45) NOT NULL,
  `created` DATETIME NULL,
  `updated` DATETIME NULL,
  `defaultOrder` INT NOT NULL,
  `active` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB DEFAULT CHARSET=utf8;

# Game

# Game
CREATE TABLE IF NOT EXISTS `g_game` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `ruleset_id` INT NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `description` TEXT NOT NULL,
  `created` DATETIME NULL,
  `updated` DATETIME NULL,
  `defaultOrder` INT NOT NULL,
  `active` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB DEFAULT CHARSET=utf8;

# Player
CREATE TABLE IF NOT EXISTS `g_player` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `game_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `role_id` INT NOT NULL,
  `created` DATETIME NULL,
  `updated` DATETIME NULL,
  `defaultOrder` INT NOT NULL,
  `active` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB DEFAULT CHARSET=utf8;


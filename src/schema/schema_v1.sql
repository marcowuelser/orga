CREATE TABLE IF NOT EXISTS `s_user` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(45) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `token` VARCHAR(255) NULL,
  `token_expire` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `username_UNIQUE` (`username` ASC))
ENGINE = InnoDB;

INSERT INTO s_user (username, password) VALUES('root', PASSWORD('password'));

CREATE TABLE IF NOT EXISTS `r_ruleset` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `description` TEXT NOT NULL,
  `created` DATETIME NULL,
  `updated` DATETIME NULL,
  `order` INT NOT NULL,
  `active` BOOL NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `s_system` (
  `maintenance` BOOL NOT NULL)
ENGINE = InnoDB;


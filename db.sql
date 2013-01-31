SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';


-- -----------------------------------------------------
-- Table `users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `email` VARCHAR(45) NULL ,
  `password` VARCHAR(32) NULL ,
  `admin` TINYINT(1) NOT NULL DEFAULT 0 ,
  `active` TINYINT(1) NOT NULL DEFAULT 1 ,
  `real` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'real user id. 0 for unknown' ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `photos`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `photos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `path` VARCHAR(45) NOT NULL ,
  `user` INT UNSIGNED NULL ,
  `approved` TINYINT(1) NOT NULL DEFAULT 0 ,
  `added` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) ,
  INDEX `fk_photos_1_idx` (`user` ASC) ,
  CONSTRAINT `fk_photos_1`
    FOREIGN KEY (`user` )
    REFERENCES `users` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `votes`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `votes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `user` INT UNSIGNED NOT NULL ,
  `photo` INT UNSIGNED NOT NULL ,
  `vote` TINYINT NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) ,
  UNIQUE INDEX `user_photo_index` (`user` ASC, `photo` ASC) ,
  INDEX `fk_votes_1_idx` (`user` ASC) ,
  INDEX `fk_votes_2_idx` (`photo` ASC) ,
  CONSTRAINT `fk_votes_1`
    FOREIGN KEY (`user` )
    REFERENCES `users` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_votes_2`
    FOREIGN KEY (`photo` )
    REFERENCES `photos` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `similar_photos`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `similar_photos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `photo1` INT UNSIGNED NOT NULL ,
  `photo2` INT UNSIGNED NOT NULL ,
  `score` DOUBLE NOT NULL DEFAULT 0 ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) ,
  UNIQUE INDEX `photos_indexes` (`photo1` ASC, `photo2` ASC) ,
  INDEX `fk_similar_photos_1_idx` (`photo1` ASC) ,
  INDEX `fk_similar_photos_2_idx` (`photo2` ASC) ,
  CONSTRAINT `fk_similar_photos_1`
    FOREIGN KEY (`photo1` )
    REFERENCES `photos` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_similar_photos_2`
    FOREIGN KEY (`photo2` )
    REFERENCES `photos` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

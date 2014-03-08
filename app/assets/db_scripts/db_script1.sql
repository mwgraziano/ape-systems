SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `apesystem` DEFAULT CHARACTER SET latin1 ;
USE `apesystem` ;

-- -----------------------------------------------------
-- Table `apesystem`.`account_metric_categories`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`account_metric_categories` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `account_id` BIGINT(20) NOT NULL,
  `metric_category_id` BIGINT(20) NOT NULL DEFAULT '0',
  `name` VARCHAR(255) NOT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 21
DEFAULT CHARACTER SET = utf8;

CREATE INDEX `acct_idx` ON `apesystem`.`account_metric_categories` (`account_id` ASC, `metric_category_id` ASC);


-- -----------------------------------------------------
-- Table `apesystem`.`account_metric_categories_metrics`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`account_metric_categories_metrics` (
  `account_id` BIGINT(20) NOT NULL,
  `metric_category_id` BIGINT(20) NOT NULL DEFAULT '0',
  `account_metric_category_id` BIGINT(20) NOT NULL DEFAULT '0',
  `metric_id` BIGINT(20) NOT NULL DEFAULT '0',
  `account_metric_id` BIGINT(20) NOT NULL DEFAULT '0',
  `unit_id` BIGINT(20) NOT NULL)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE UNIQUE INDEX `acct_idx` ON `apesystem`.`account_metric_categories_metrics` (`account_id` ASC, `metric_category_id` ASC, `account_metric_category_id` ASC, `metric_id` ASC, `account_metric_id` ASC);


-- -----------------------------------------------------
-- Table `apesystem`.`account_metric_categories_view`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`account_metric_categories_view` (
  `account_id` TINYINT(4) NOT NULL,
  `account_metric_category_id` TINYINT(4) NOT NULL,
  `metric_category_id` TINYINT(4) NOT NULL,
  `name` TINYINT(4) NOT NULL)
ENGINE = MyISAM
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `apesystem`.`account_metrics`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`account_metrics` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `account_id` BIGINT(20) NOT NULL,
  `metric_id` BIGINT(20) NOT NULL DEFAULT '0',
  `name` VARCHAR(255) NOT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 49
DEFAULT CHARACTER SET = utf8;

CREATE INDEX `account_idx` ON `apesystem`.`account_metrics` (`account_id` ASC, `metric_id` ASC);


-- -----------------------------------------------------
-- Table `apesystem`.`account_metrics_view`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`account_metrics_view` (
  `account_id` TINYINT(4) NOT NULL,
  `account_metric_id` TINYINT(4) NOT NULL,
  `metric_id` TINYINT(4) NOT NULL,
  `name` TINYINT(4) NOT NULL)
ENGINE = MyISAM
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `apesystem`.`account_style`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`account_style` (
  `account_id` INT(11) NOT NULL,
  `style` TEXT NULL DEFAULT NULL,
  `active` TINYINT(4) NOT NULL DEFAULT '1',
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`account_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `apesystem`.`accounts`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`accounts` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `mascot` VARCHAR(255) NOT NULL,
  `domain` VARCHAR(50) NOT NULL,
  `image` VARCHAR(255) NULL DEFAULT NULL,
  `address` VARCHAR(255) NULL DEFAULT NULL,
  `city` VARCHAR(100) NULL DEFAULT NULL,
  `state` VARCHAR(100) NULL DEFAULT NULL,
  `zip` VARCHAR(20) NULL DEFAULT NULL,
  `contact` VARCHAR(255) NULL DEFAULT NULL,
  `phone` VARCHAR(45) NULL DEFAULT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 37
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `apesystem`.`athlete_profile`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`athlete_profile` (
  `athlete_id` BIGINT(20) UNSIGNED NOT NULL,
  `prop_name` VARCHAR(50) NOT NULL,
  `prop_value` BLOB NOT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`athlete_id`, `prop_name`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `apesystem`.`athletes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`athletes` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` BIGINT(20) NOT NULL,
  `pin` VARCHAR(45) NOT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 314
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `apesystem`.`auth_log`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`auth_log` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT(20) UNSIGNED NOT NULL,
  `login_time` DATETIME NOT NULL,
  `logout_time` DATETIME NULL DEFAULT NULL,
  `browser_signature` VARCHAR(45) NULL DEFAULT NULL,
  `ip_address` BIGINT(20) NULL DEFAULT NULL,
  `php_session_id` TEXT NULL DEFAULT NULL,
  `login_via` VARCHAR(45) NOT NULL DEFAULT 'Web',
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 482
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `apesystem`.`coach_profile`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`coach_profile` (
  `coach_id` BIGINT(20) UNSIGNED NOT NULL,
  `prop_name` VARCHAR(50) NOT NULL,
  `prop_value` BLOB NOT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`coach_id`, `prop_name`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `apesystem`.`coach_users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`coach_users` (
  `coach_id` BIGINT(20) NOT NULL,
  `user_id` BIGINT(20) NOT NULL,
  PRIMARY KEY (`coach_id`, `user_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `apesystem`.`coaches`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`coaches` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` BIGINT(20) NOT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 59
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `apesystem`.`genders`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`genders` (
  `gender` VARCHAR(45) NOT NULL,
  `display` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`gender`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `apesystem`.`measurements`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`measurements` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `athlete_id` BIGINT(20) UNSIGNED NOT NULL,
  `metric_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
  `account_metric_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
  `unit_id` BIGINT(20) UNSIGNED NOT NULL,
  `trial` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `data_date` DATETIME NOT NULL,
  `data_value` FLOAT(12,3) NOT NULL DEFAULT '0.000',
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 1467
DEFAULT CHARACTER SET = utf8;

CREATE INDEX `user_date` ON `apesystem`.`measurements` (`athlete_id` ASC, `data_date` ASC);

CREATE INDEX `user_metric` ON `apesystem`.`measurements` (`athlete_id` ASC, `metric_id` ASC, `account_metric_id` ASC, `unit_id` ASC);


-- -----------------------------------------------------
-- Table `apesystem`.`metric_categories`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`metric_categories` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `apesystem`.`metric_categories_metrics`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`metric_categories_metrics` (
  `metric_category_id` BIGINT(20) NOT NULL,
  `metric_id` BIGINT(20) NOT NULL,
  `unit_id` BIGINT(20) NOT NULL,
  PRIMARY KEY (`metric_category_id`, `metric_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `apesystem`.`metric_units`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`metric_units` (
  `metric_id` BIGINT(20) UNSIGNED NOT NULL,
  `unit_id` BIGINT(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`metric_id`, `unit_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `apesystem`.`metrics`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`metrics` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `unit_category` VARCHAR(45) NULL DEFAULT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 52
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `apesystem`.`recent_measurements`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`recent_measurements` (
  `athlete_id` BIGINT(20) UNSIGNED NOT NULL,
  `metric_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
  `account_metric_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
  `unit_id` BIGINT(20) UNSIGNED NOT NULL,
  `trial` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `data_date` DATETIME NOT NULL,
  `data_value` FLOAT(12,3) NOT NULL DEFAULT '0.000',
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`athlete_id`, `metric_id`, `account_metric_id`, `unit_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `apesystem`.`sports`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`sports` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 37
DEFAULT CHARACTER SET = utf8
COMMENT = 'Standard Sports';


-- -----------------------------------------------------
-- Table `apesystem`.`team_athletes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`team_athletes` (
  `team_id` BIGINT(20) NOT NULL,
  `athlete_id` BIGINT(20) NOT NULL,
  PRIMARY KEY (`team_id`, `athlete_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `apesystem`.`team_coaches`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`team_coaches` (
  `team_id` BIGINT(20) UNSIGNED NOT NULL,
  `coach_id` BIGINT(20) UNSIGNED NOT NULL,
  `position` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`team_id`, `coach_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `apesystem`.`team_metrics`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`team_metrics` (
  `team_id` BIGINT(20) UNSIGNED NOT NULL,
  `metric_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
  `account_metric_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
  `key_metric` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`team_id`, `metric_id`, `account_metric_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE UNIQUE INDEX `pri_idx` ON `apesystem`.`team_metrics` (`team_id` ASC, `metric_id` ASC, `account_metric_id` ASC);


-- -----------------------------------------------------
-- Table `apesystem`.`team_recent_measurements`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`team_recent_measurements` (
  `team_id` BIGINT(20) UNSIGNED NOT NULL,
  `metric_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
  `account_metric_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
  `unit_id` BIGINT(20) UNSIGNED NOT NULL,
  `avg_data_value` FLOAT(12,3) NOT NULL DEFAULT '0.000',
  `max_data_value` FLOAT(12,3) NOT NULL DEFAULT '0.000',
  `min_data_value` FLOAT(12,3) NOT NULL DEFAULT '0.000',
  `sum_data_value` FLOAT(12,3) NOT NULL DEFAULT '0.000',
  PRIMARY KEY (`team_id`, `metric_id`, `account_metric_id`, `unit_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `apesystem`.`teams`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`teams` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` BIGINT(20) UNSIGNED NOT NULL,
  `sport_id` BIGINT(20) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `gender` VARCHAR(3) NOT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 66
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `apesystem`.`units`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`units` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `label` VARCHAR(45) NOT NULL,
  `metric_name` VARCHAR(45) NULL DEFAULT NULL,
  `metric_label` VARCHAR(45) NULL DEFAULT NULL,
  `metric_normalization` TEXT NULL DEFAULT NULL,
  `normalization` TEXT NULL DEFAULT NULL,
  `normalized_unit_id` BIGINT(20) NULL DEFAULT NULL,
  `unit_category` VARCHAR(45) NULL DEFAULT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 11
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `apesystem`.`users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`users` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` BIGINT(20) UNSIGNED NOT NULL,
  `email` BLOB NOT NULL,
  `passwd` VARCHAR(32) NOT NULL,
  `allowed` TINYINT(4) NOT NULL DEFAULT '1',
  `timezone` INT(11) NULL DEFAULT '0',
  `timezone_text` VARCHAR(45) NULL DEFAULT NULL,
  `auth_level` TINYINT(2) NOT NULL DEFAULT '1',
  `last_update` DATETIME NULL DEFAULT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 51
DEFAULT CHARACTER SET = utf8;

CREATE UNIQUE INDEX `email` ON `apesystem`.`users` (`email`(256) ASC);

USE `apesystem` ;

-- -----------------------------------------------------
-- Placeholder table for view `apesystem`.`account_metric_categories_metrics_personal_view`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `apesystem`.`account_metric_categories_metrics_personal_view` (`account_id` INT, `metric_category_id` INT, `account_metric_category_id` INT, `metric_id` INT, `account_metric_id` INT, `unit_id` INT);

-- -----------------------------------------------------
-- View `apesystem`.`account_metric_categories_metrics_personal_view`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `apesystem`.`account_metric_categories_metrics_personal_view`;
USE `apesystem`;
CREATE  OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`127.0.0.1` SQL SECURITY DEFINER VIEW `apesystem`.`account_metric_categories_metrics_personal_view` AS select `a`.`id` AS `account_id`,`amc`.`metric_category_id` AS `metric_category_id`,`amc`.`account_metric_category_id` AS `account_metric_category_id`,`amc`.`metric_id` AS `metric_id`,`amc`.`account_metric_id` AS `account_metric_id`,`amc`.`unit_id` AS `unit_id` from (`apesystem`.`account_metric_categories_metrics` `amc` join `apesystem`.`accounts` `a` on((`a`.`id` = `amc`.`account_id`))) where ((`a`.`active` = 1) and ((`amc`.`metric_id` = 0) or (`amc`.`metric_category_id` = 0)));

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
USE `apesystem`;

DELIMITER $$
USE `apesystem`$$
CREATE
DEFINER=`root`@`127.0.0.1`
TRIGGER `apesystem`.`measurement_after_delete`
AFTER DELETE ON `apesystem`.`measurements`
FOR EACH ROW
begin

	SET @team_id = (SELECT team_id FROM team_athletes WHERE athlete_id = OLD.athlete_id LIMIT 1);

	DELETE FROM team_recent_measurements WHERE team_id = @team_id LIMIT 1;

	INSERT INTO team_recent_measurements
	(SELECT ta.team_id, metric_id, account_metric_id, unit_id, 
		AVG(data_value), MAX(data_value), MIN(data_value), SUM(data_value)
	 FROM recent_measurements rm JOIN team_athletes ta ON ta.athlete_id = rm.athlete_id
	 WHERE ta.team_id = @team_id
	 GROUP BY metric_id, account_metric_id, unit_id)
	ON DUPLICATE KEY UPDATE avg_data_value = VALUES(avg_data_value), 
		max_data_value = VALUES(max_data_value), 
		min_data_value = VALUES(min_data_value),
		sum_data_value = VALUES(sum_data_value);


end$$

USE `apesystem`$$
CREATE
DEFINER=`root`@`127.0.0.1`
TRIGGER `apesystem`.`measurement_after_insert`
AFTER INSERT ON `apesystem`.`measurements`
FOR EACH ROW
begin
	SET @last_date = (
		SELECT MAX(data_date) FROM recent_measurements 
		WHERE athlete_id = new.athlete_id
			AND metric_id = new.metric_id
			AND account_metric_id = new.account_metric_id
			AND unit_id = new.unit_id
		LIMIT 1
	);

	IF (@last_date IS NOT NULL AND @last_date < new.data_date) OR @last_date IS NULL THEN

		INSERT INTO recent_measurements
		SET athlete_id = new.athlete_id,
			metric_id = new.metric_id, 
			account_metric_id = new.account_metric_id,
			unit_id = new.unit_id,
			trial = new.trial,
			data_date = new.data_date,
			data_value = new.data_value,
			created = new.created,
			active = 1
		ON DUPLICATE KEY UPDATE
			trial = VALUES(trial),
			data_date = VALUES(data_date),
			data_value = VALUES(data_value),
			created = VALUES(created),
			active = VALUES(active);

		SET @team_id = (SELECT team_id FROM team_athletes WHERE athlete_id = NEW.athlete_id LIMIT 1);

		DELETE FROM team_recent_measurements WHERE team_id = @team_id LIMIT 1;

		INSERT INTO team_recent_measurements
		(SELECT ta.team_id, metric_id, account_metric_id, unit_id, 
			AVG(data_value), MAX(data_value), MIN(data_value), SUM(data_value)
		 FROM recent_measurements rm JOIN team_athletes ta ON ta.athlete_id = rm.athlete_id
		 WHERE ta.team_id = @team_id
		 GROUP BY metric_id, account_metric_id, unit_id)
		ON DUPLICATE KEY UPDATE avg_data_value = VALUES(avg_data_value), 
			max_data_value = VALUES(max_data_value), 
			min_data_value = VALUES(min_data_value),
			sum_data_value = VALUES(sum_data_value);

	END IF;
end$$

USE `apesystem`$$
CREATE
DEFINER=`root`@`127.0.0.1`
TRIGGER `apesystem`.`measurement_after_update`
AFTER UPDATE ON `apesystem`.`measurements`
FOR EACH ROW
begin
	SET @last_date = (
		SELECT MAX(data_date) FROM recent_measurements 
		WHERE athlete_id = new.athlete_id
			AND metric_id = new.metric_id
			AND account_metric_id = new.account_metric_id
			AND unit_id = new.unit_id
		LIMIT 1
	);

	IF (@last_date IS NOT NULL AND @last_date < new.data_date) OR @last_date OR old.id = new.id IS NULL THEN

		INSERT INTO recent_measurements
		SET athlete_id = new.athlete_id,
			metric_id = new.metric_id, 
			account_metric_id = new.account_metric_id,
			unit_id = new.unit_id,
			trial = new.trial,
			data_date = new.data_date,
			data_value = new.data_value,
			created = new.created,
			active = 1
		ON DUPLICATE KEY UPDATE
			trial = VALUES(trial),
			data_date = VALUES(data_date),
			data_value = VALUES(data_value),
			created = VALUES(created),
			active = VALUES(active);

		SET @team_id = (SELECT team_id FROM team_athletes WHERE athlete_id = NEW.athlete_id LIMIT 1);

		DELETE FROM team_recent_measurements WHERE team_id = @team_id LIMIT 1;

		INSERT INTO team_recent_measurements
		(SELECT ta.team_id, metric_id, account_metric_id, unit_id, 
			AVG(data_value), MAX(data_value), MIN(data_value), SUM(data_value)
		 FROM recent_measurements rm JOIN team_athletes ta ON ta.athlete_id = rm.athlete_id
		 WHERE ta.team_id = @team_id
		 GROUP BY metric_id, account_metric_id, unit_id)
		ON DUPLICATE KEY UPDATE avg_data_value = VALUES(avg_data_value), 
			max_data_value = VALUES(max_data_value), 
			min_data_value = VALUES(min_data_value),
			sum_data_value = VALUES(sum_data_value);

	END IF;

end$$

USE `apesystem`$$
CREATE
DEFINER=`root`@`127.0.0.1`
TRIGGER `apesystem`.`measurement_before_delete`
BEFORE DELETE ON `apesystem`.`measurements`
FOR EACH ROW
begin
	DELETE FROM recent_measurements
	WHERE athlete_id = OLD.athlete_id
		AND metric_id = OLD.metric_id
		AND account_metric_id = OLD.account_metric_id
		AND unit_id = OLD.unit_id;

	SET @last_date = (
		SELECT MAX(data_date) FROM measurements
		WHERE athlete_id = OLD.athlete_id
			AND metric_id = OLD.metric_id
			AND account_metric_id = OLD.account_metric_id
			AND unit_id = OLD.unit_id
			AND id != OLD.id
		LIMIT 1
	);

	IF @last_date IS NOT NULL THEN

		INSERT INTO recent_measurements
		SELECT athlete_id,
			metric_id,
			account_metric_id,
			unit_id,
			trial,
			data_date,
			data_value,
			created,
			active
		FROM measurements
		WHERE athlete_id = OLD.athlete_id
			AND metric_id = OLD.metric_id
			AND account_metric_id = OLD.account_metric_id
			AND unit_id = OLD.unit_id
			AND id != OLD.id
			AND data_date = @last_date
		LIMIT 1;

	END IF;

end$$


DELIMITER ;

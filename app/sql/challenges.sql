CREATE TABLE IF NOT EXISTS `challenges` (
  `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `Uid` VARCHAR(27) NOT NULL,
  `Name` VARCHAR(100) NOT NULL,
  `Author` VARCHAR(30) NOT NULL,
  `Environment` VARCHAR(10) NOT NULL,
  UNIQUE KEY `Uid` (`Uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
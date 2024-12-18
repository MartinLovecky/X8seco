CREATE TABLE IF NOT EXISTS `players` (
  `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `Login` VARCHAR(50) NOT NULL,
  `Game` CHAR(3) NOT NULL,
  `NickName` VARCHAR(100) NOT NULL,
  `playerID` VARCHAR(255) NOT NULL, 
  `Nation` CHAR(3) NOT NULL,
  `UpdatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Wins` MEDIUMINT(9) NOT NULL DEFAULT 0,
  `TimePlayed` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `TeamName` CHAR(60) NOT NULL,
  UNIQUE KEY `Login` (`Login`),
  KEY `Game` (`Game`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
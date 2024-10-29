CREATE TABLE IF NOT EXISTS `match_details` (
  `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `matchID` mediumint(9) NOT NULL,
  `playerID` VARCHAR(255) NOT NULL,
  `teamname` VARCHAR(40),
  `points` tinyint default 0,
  `score` mediumint(9),
  UNIQUE KEY (`matchID`, `playerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
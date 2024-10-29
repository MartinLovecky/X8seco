CREATE TABLE IF NOT EXISTS `records` (
  `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ChallengeId` MEDIUMINT(9) UNSIGNED NOT NULL,
  `playerID` VARCHAR(255) NOT NULL,
  `Score` INT(11) NOT NULL,
  `Date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Checkpoints` TEXT NOT NULL,
  UNIQUE KEY `Player_Challenge` (`playerID`, `ChallengeId`),
  KEY `ChallengeId` (`ChallengeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
CREATE TABLE IF NOT EXISTS `rs_karma` (
    `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `ChallengeId` INT NOT NULL,
    `playerID` VARCHAR(255) NOT NULL,
    `Score` TINYINT NOT NULL DEFAULT 0,
    UNIQUE (`playerID`, `ChallengeId`),
    INDEX (`ChallengeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

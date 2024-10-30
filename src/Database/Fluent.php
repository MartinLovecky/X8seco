<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Database;

use PDO;
use PDOException;
use Envms\FluentPDO\Query;
use Yuhzel\X8seco\Services\Basic;
use Yuhzel\X8seco\Services\Log;

/**
 * Class Fluent
 *
 * This class handles database connections using PDO and FluentPDO query builder.
 * It includes methods for initializing tables, validating their structure, and executing SQL files.
 * @package Yuhzel\X8seco\Database
 * @author Yuhzel
 */
class Fluent
{
    /**
     * PDO instance for database connection.
     * @var PDO|null
     */
    public ?PDO $pdo = null;

    /**
     * FluentPDO Query instance for database interactions.
     * @var Query|null
     */
    public ?Query $query = null;

    /**
     * Directory path to the base of the project, used for locating SQL files.
     * @var string
     */
    public string $dir = '';

    /**
     * Fluent constructor.
     *
     * Establishes a PDO database connection using environment variables for configuration.
     * If the connection fails, it logs the error and exits the script.
     */
    public function __construct()
    {
        $this->dir = Basic::path();
        $charset = !empty($_ENV['db_charset']) ? ";charset={$_ENV['db_charset']}" : ';';

        try {
            $conn = "mysql:host={$_ENV['db_host']};port={$_ENV['db_port']};dbname={$_ENV['db_database']};sslmode=verify-ca;sslrootcert=ca.pem{$charset}";
            $this->pdo = new PDO($conn, $_ENV['db_login'], $_ENV['db_password']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $code = $e->getCode();
            if ($code === 2002) {
                Basic::console("Database connection failed: " . $e->getMessage());
                Log::error("Database connection failed: " . $e->getMessage());
                exit;
            }
            Basic::console("Database connection failed: " . $e->getMessage());
            Log::error("Database connection failed: " . $e->getMessage());
            throw new PDOException("Database connection failed.");
        } finally {
            $this->query = $this->pdo ? new Query($this->pdo) : null;
        }
    }

    /**
     * Executes an SQL file located in the `app/sql/` directory.
     *
     * @param string $name The name of the SQL file (without the .sql extension).
     * @return bool True on success, false if the file doesn't exist or execution fails.
     */
    public function execSQLFile(string $name): bool
    {
        $filePath = $this->dir . "app/sql/{$name}.sql";
        if (!file_exists($filePath)) {
            Log::error("File {$name}.sql doesn't exist in app/sql" . E_USER_WARNING);
            return false;
        }
        $sql = file_get_contents($filePath);
        return (bool)$this->pdo->exec($sql);
    }

    /**
     * Validates the structure of a given table.
     *
     * @param string $table The name of the table to validate.
     * @return bool True if the structure is valid, false otherwise.
     */
    public function validStructure(string $table): bool
    {
        try {
            $tableStructure = $this->pdo->query("DESCRIBE {$table}")->fetchAll();
        } catch (PDOException $e) {
            Log::error("Error describing {$table}: " . $e->getMessage());
            return false;
        }
        // Check if the query result is empty or invalid
        if (!$tableStructure) {
            Log::error("Error describing {$table}. Check if it exists and PDO is initialized.");
            return false;
        }

        return match ($table) {
            'challenges' => $this->validateChallenges($tableStructure),
            'players' => $this->validatePlayers($tableStructure),
            'records' => $this->validateRecords($tableStructure),
            'players_extra' => $this->validatePlayersExtra($tableStructure),
            'rs_karma' => $this->validateRsKarma($tableStructure),
            'rs_times' => $this->validateRsTimes($tableStructure),
            'rs_rank' => $this->validateRsRank($tableStructure),
            'match_main' => $this->validateMatchMain($tableStructure),
            'match_details' => $this->validateMatchDetails($tableStructure),
            default => false
        };
    }

    private function validateChallenges(array $structure): bool
    {
        if (count($structure) !== 5) {
            $requiredFields = [
                0 => ['Id', 'INT UNSIGNED NOT NULL AUTO_INCREMENT'],
                1 => ['Uid', 'VARCHAR(27) NOT NULL'],
                2 => ['Name', 'CHAR(100) NOT NULL'],
                3 => ['Author', 'VARCHAR(30) NOT NULL'],
                4 => ['Environment', 'VARCHAR(10) NOT NULL']
            ];

            $this->handleMissingFields('challenges', $requiredFields, $structure);
        }
        $this->checkAndAlter($structure[0], 'Id', 'int unsigned', 'ALTER TABLE challenges MODIFY Id INT UNSIGNED NOT NULL AUTO_INCREMENT', 'challenges');
        $this->checkAndAlter($structure[2], 'Name', 'varchar(100)', 'ALTER TABLE challenges MODIFY Name VARCHAR(100) NOT NULL', 'challenges');
        $this->checkAndAlter($structure[4], 'Environment', 'varchar(10)', 'ALTER TABLE challenges MODIFY Environment VARCHAR(10) NOT NULL', 'challenges');

        return true;
    }

    private function validatePlayers(array $structure): bool
    {
        if (count($structure) !== 10) {
            $requiredFields = [
                0 => ['Id', 'INT UNSIGNED NOT NULL AUTO_INCREMENT'],
                1 => ['Login', 'VARCHAR(50) NOT NULL'],
                2 => ['Game', 'CHAR(3) NOT NULL'],
                3 => ['NickName', 'VARCHAR(100) NOT NULL'],
                4 => ['playerID', 'VARCHAR(255) NOT NULL'],
                5 => ['Nation', 'CHAR(3) NOT NULL'],
                6 => ['UpdatedAt', 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'],
                7 => ['Wins', 'MEDIUMINT(9) NOT NULL DEFAULT 0'],
                8 => ['TimePlayed', 'INT(10) UNSIGNED NOT NULL DEFAULT 0'],
                9 => ['TeamName', 'CHAR(60) NOT NULL'],
            ];

            $this->handleMissingFields('players', $requiredFields, $structure);
        }
        $this->checkAndAlter($structure[0], 'Id', 'int unsigned', 'ALTER TABLE players MODIFY Id INT UNSIGNED NOT NULL AUTO_INCREMENT', 'players');
        $this->checkAndAlter($structure[3], 'NickName', 'varchar(100)', 'ALTER TABLE players MODIFY NickName VARCHAR(100) NOT NULL', 'players');
        $this->checkAndAlter($structure[4], 'playerID', 'varchar(255)', 'ALTER TABLE players MODIFY playerID VARCHAR(255) NOT NULL', 'players');
        $this->checkAndAlter($structure[6], 'UpdatedAt', 'datetime', 'ALTER TABLE players MODIFY UpdatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 'players', 'CURRENT_TIMESTAMP');
        $this->checkAndAlter($structure[8], 'TimePlayed', 'int unsigned', 'ALTER TABLE players MODIFY TimePlayed int unsigned', 'players');

        return true;
    }

    private function validateRecords(array $structure): bool
    {
        if (count($structure) !== 6) {
            $requiredFields = [
                0 => ['Id', 'INT UNSIGNED NOT NULL AUTO_INCREMENT'],
                1 => ['ChallengeId', 'MEDIUMINT(9) UNSIGNED NOT NULL'],
                2 => ['playerID', 'VARCHAR(255) NOT NULL'],
                3 => ['Score', 'INT(11) NOT NULL'],
                4 => ['Date', 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'],
                5 => ['Checkpoints', 'TEXT NOT NULL']
            ];

            $this->handleMissingFields('records', $requiredFields, $structure);
        }
        $this->checkAndAlter($structure[0], 'Id', 'int unsigned', 'ALTER TABLE records MODIFY Id INT UNSIGNED NOT NULL AUTO_INCREMENT', 'records');
        $this->checkAndAlter($structure[2], 'playerID', 'varchar(255)', 'ALTER TABLE records MODIFY playerID VARCHAR(255) NOT NULL', 'records');
        $this->checkAndAlter($structure[4], 'Date', 'datetime', 'ALTER TABLE records MODIFY Date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 'records', 'CURRENT_TIMESTAMP');
        $this->checkAndAlter($structure[5], 'Checkpoints', null, 'ALTER TABLE records ADD Checkpoints text NOT NULL', 'records');

        return true;
    }

    private function validatePlayersExtra(array $structure): bool
    {
        if (count($structure) !== 7) {
            $requiredFields = [
                0 => ['Id', 'INT UNSIGNED NOT NULL AUTO_INCREMENT'],
                1 => ['cps', 'SMALLINT(5) NOT NULL DEFAULT -1'],
                2 => ['dedicps', 'SMALLINT(5) NOT NULL DEFAULT -1'],
                3 => ['donations', 'MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT 0'],
                4 => ['style', 'VARCHAR(20) NOT NULL'],
                5 => ['panels', 'VARCHAR(255) NOT NULL'],
                6 => ['playerID', 'VARCHAR(255) NOT NULL']
            ];

            $this->handleMissingFields('players_extra', $requiredFields, $structure);
        }
        $this->checkAndAlter($structure[0], 'Id', 'int unsigned', 'ALTER TABLE players_extra CHANGE playerID Id INT UNSIGNED NOT NULL AUTO_INCREMENT', 'players_extra');
        $this->checkAndAlter($structure[0], 'Id', 'int unsigned', 'UPDATE players_extra MODIFY Id INT UNSIGNED NOT NULL AUTO_INCREMENT', 'players_extra');
        $this->checkAndAlter($structure[3], 'donations', null, 'ALTER TABLE players_extra ADD KEY donations (donations)', 'players_extra');
        $this->checkAndAlter($structure[6], 'playerID', 'varchar(255)', 'ALTER TABLE players_extra MODIFY playerID VARCHAR(255) NOT NULL', 'players_extra');
        return true;
    }

    private function validateRsKarma(array $structure): bool
    {
        if (count($structure) !== 4) {
            $requiredFields = [
                0 => ['Id', 'INT UNSIGNED NOT NULL AUTO_INCREMENT'],
                1 => ['ChallengeId', 'INT NOT NULL'],
                2 => ['playerID', 'VARCHAR(255) NOT NULL'],
                3 => ['Score', 'TINYINT NOT NULL DEFAULT 0']
            ];

            $this->handleMissingFields('rs_karma', $requiredFields, $structure);
        }
        $this->checkAndAlter($structure[0], 'Id', 'int unsigned', 'ALTER TABLE rs_karma MODIFY Id INT UNSIGNED NOT NULL AUTO_INCREMENT', 'rs_karma');
        $this->checkAndAlter($structure[2], 'playerID', 'varchar(255)', 'ALTER TABLE rs_karma MODIFY playerID VARCHAR(255) NOT NULL', 'rs_karma');
        $this->checkAndAlter($structure[3], 'Score', 'tinyint', 'ALTER TABLE rs_karma MODIFY Score TINYINT NOT NULL DEFAULT 0', 'rs_karma');

        return true;
    }

    private function validateRsTimes(array $structure): bool
    {
        if (count($structure) !== 6) {
            $requiredFields = [
                0 => ['Id', 'INT UNSIGNED NOT NULL AUTO_INCREMENT'],
                1 => ['challengeID', 'INT NOT NULL'],
                2 => ['playerID', 'VARCHAR(255) NOT NULL'],
                3 => ['score', 'INT NOT NULL'],
                4 => ['date', 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'],
                5 => ['checkpoints', 'TEXT NOT NULL']
            ];

            $this->handleMissingFields('rs_times', $requiredFields, $structure);
        }

        $this->checkAndAlter($structure[0], 'Id', 'int unsigned', 'ALTER TABLE rs_times MODIFY Id INT UNSIGNED NOT NULL AUTO_INCREMENT', 'rs_times');
        $this->checkAndAlter($structure[1], 'challengeID', null, 'ALTER TABLE rs_times CHANGE trackID challengeID INT NOT NULL', 'rs_times');
        $this->checkAndAlter($structure[1], 'challengeID', 'int', 'ALTER TABLE rs_times MODIFY challengeID INT NOT NULL', 'rs_times');
        $this->checkAndAlter($structure[2], 'playerID', 'varchar(255)', 'ALTER TABLE rs_times MODIFY playerID VARCHAR(255) NOT NULL', 'rs_times');
        $this->checkAndAlter($structure[3], 'score', 'int', 'ALTER TABLE rs_times MODIFY score INT NOT NULL default 0', 'rs_times');
        $this->checkAndAlter($structure[4], 'date', 'timestamp', 'ALTER TABLE rs_times MODIFY date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 'rs_times', 'CURRENT_TIMESTAMP');
        $this->checkAndAlter($structure[5], 'checkpoints', null, 'ALTER TABLE rs_times ADD checkpoints text NOT NULL', 'rs_times');

        return true;
    }

    private function validateRsRank(array $structure): bool
    {
        if (count($structure) !== 3) {
            $requiredFields = [
                0 => ['Id', 'INT UNSIGNED NOT NULL AUTO_INCREMENT'],
                1 => ['playerID', 'VARCHAR(255) NOT NULL'],
                2 => ['avg_score', 'FLOAT NOT NULL']
            ];

            $this->handleMissingFields('rs_rank', $requiredFields, $structure);
        }
        $this->checkAndAlter($structure[0], 'Id', 'int unsigned', 'ALTER TABLE rs_rank MODIFY Id INT UNSIGNED NOT NULL AUTO_INCREMENT', 'rs_rank');
        $this->checkAndAlter($structure[1], 'playerID', 'varchar(255)', 'ALTER TABLE rs_rank MODIFY playerID VARCHAR(255) NOT NULL', 'rs_rank');
        return true;
    }

    private function validateMatchMain(array $structure): bool
    {
        if (count($structure) !== 3) {
            $requiredFields = [
                0 => ['Id', 'INT UNSIGNED NOT NULL AUTO_INCREMENT'],
                1 => ['trackID', 'mediumint(9) NOT NULL default 0'],
                2 => ['dttmrun', 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
            ];

            $this->handleMissingFields('match_main', $requiredFields, $structure);
        }
        $this->checkAndAlter($structure[0], 'Id', 'int unsigned', 'ALTER TABLE match_main MODIFY Id INT UNSIGNED NOT NULL AUTO_INCREMENT', 'match_main');
        $this->checkAndAlter($structure[2], 'dttmrun', 'timestamp', 'ALTER TABLE match_main MODIFY dttmrun TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 'match_main', 'CURRENT_TIMESTAMP');

        return true;
    }

    private function validateMatchDetails(array $structure): bool
    {
        if (count($structure) !== 6) {
            $requiredFields = [
                0 => ['Id', 'INT UNSIGNED NOT NULL AUTO_INCREMENT'],
                1 => ['matchID', 'mediumint(9) NOT NULL'],
                2 => ['playerID', 'varchar(255) NOT NULL'],
                3 => ['teamname', 'varchar(40)'],
                4 => ['points', 'tinyint default 0'],
                5 => ['score', ' mediumint(9)']
            ];

            $this->handleMissingFields('match_details', $requiredFields, $structure);

            return false;
        }
        $this->checkAndAlter($structure[0], 'id', 'int unsigned', 'ALTER TABLE match_details MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT', 'match_details');
        $this->checkAndAlter($structure[2], 'playerID', 'varchar(255)', 'ALTER TABLE match_details MODIFY playerID VARCHAR(255) NOT NULL', 'playerID');
        return true;
    }

    /**
     * Checks and alters a database column if necessary, based on expected field name, type, and default value.
     *
     * This method allows flexible validation of database column structure by optionally checking the field name,
     * column type, and default value. If any of the checks fail, it will execute the provided ALTER TABLE query
     * to modify the column.
     *
     * @param array $column The actual column definition from the database, including at least 'Field', 'Type', and optionally 'Default'.
     * @param string|null $expectedField The expected field name to validate against. If null, no check will be performed for field name.
     * @param string|null $expectedType The expected column type (e.g., 'int unsigned'). If null, no check will be performed for type.
     * @param string|null $alterQuery The ALTER TABLE SQL query to execute if the column needs to be altered. If null, no query will be executed.
     * @param string|null $expectedDefault The expected default value for the column. If null, no check will be performed for default value.
     * @param string|null $tableName The name of the table for logging purposes. If null, logging will omit the table name.
     *
     * @return void
     *
     * Logs:
     * - Logs warnings when the actual field name doesn't match the expected field name.
     * - Logs informational messages when the actual type or default value doesn't match the expected values.
     * - Executes the provided ALTER TABLE query if any of the checks fail, and logs the result.
     *
     * Example usage:
     * $this->checkAndAlter($column, 'challengeID', 'int', 'ALTER TABLE rs_times CHANGE trackID challengeID INT NOT NULL', 'rs_times', null);
     */
    private function checkAndAlter(
        array $column,
        ?string $expectedField =  null,
        ?string $expectedType = null,
        ?string $alterQuery = null,
        ?string $tableName = null,
        ?string $expectedDefault = null,
    ): void {

        $needsAlteration = false;

        // Check the field name if provided
        if ($expectedField !== null) {
            if (!isset($column['Field']) || $column['Field'] !== $expectedField) {
                Log::warning("Field name mismatch in table {$tableName}: expected {$expectedField}, found {$column['Field']}");
                $needsAlteration = true;
            }
        }

        // Check the type if provided
        if ($expectedType !== null) {
            if (!isset($column['Type']) || $column['Type'] !== $expectedType) {
                Log::info("Type mismatch for field {$expectedField} in table {$tableName}: expected {$expectedType}, found {$column['Type']}");
                $needsAlteration = true;
            }
        }

        // Check the default value if provided
        if ($expectedDefault !== null) {
            if (!isset($column['Default']) || $column['Default'] !== $expectedDefault) {
                Log::info("Default value mismatch for field {$expectedField} in table {$tableName}: expected {$expectedDefault}, found {$column['Default']}");
                $needsAlteration = true;
            }
        }

        // If any checks failed, apply the ALTER TABLE query
        if ($needsAlteration) {
            try {
                $this->pdo->query($alterQuery);
            } catch (PDOException $e) {
                Log::error("Failed to alter field {$expectedField}: " . $e->getMessage());
            }
        }
    }

    private function handleMissingFields(
        string $table,
        array $requiredFields,
        array $structure
    ): void {
        $missingFields = [];
        foreach ($requiredFields as $index => $field) {
            if (!isset($structure[$index]['Field'])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            foreach ($missingFields as $field) {
                $this->addFieldToTable($table, $field[0], $field[1]);
            }
        }
    }

    /**
     * Helper function to add missing fields to the table
     */
    private function addFieldToTable(
        string $table,
        string $fieldName,
        string $fieldType
    ): void {
        $query = "ALTER TABLE $table ADD COLUMN $fieldName $fieldType";
        $this->pdo->exec($query);
    }
}

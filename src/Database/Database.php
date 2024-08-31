<?php

declare(strict_types=1);

namespace LWP\Database;

use LWP\Common\Exceptions\EmptyStringException;
use LWP\Common\Exceptions\ReservedException;
use LWP\Components\Datasets\AbstractDatabase;
use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Components\Datasets\Interfaces\DatabaseDescriptorInterface;
use LWP\Components\Datasets\Interfaces\DatabaseInterface;
use LWP\Database\Exceptions\DatabaseSelectError;

class Database extends AbstractDatabase implements DatabaseInterface
{
    public readonly \mysqli $server_link;
    protected array $table_abbreviations = [];


    public function __construct(
        public readonly Server $server,
        public readonly string $database_name
    ) {

        $this->server_link = $server->link;

        if (!$this->server_link->select_db($database_name)) {
            throw new DatabaseSelectError(sprintf(
                "Could not select database \"%s\": %s",
                $database_name,
                $this->server_link->error
            ));
        }

        parent::__construct();
    }


    // Tells if table (a.k.a. dataset) exists by given address name

    public function hasAddress(string $address_name): bool
    {

        return $this->hasTable($address_name);
    }


    //

    public function initDataset(string $address_name, array $extra_params = []): DatasetInterface
    {

        return $this->getTable($address_name);
    }


    // Tells if this database supports multi-queries

    public function supportsMultiQuery(): true
    {

        return true;
    }


    // Validates table name identifier
    // $table_name - apparently, any character is allowed (even backticks that can be escaped with other backticks)
    // $check_reserved - table names with reserved word are also allowed and can be escaped with backticks

    public function validateTableName(string $table_name, bool $check_reserved = true): true
    {

        if ($table_name === '') {
            throw new EmptyStringException("Table name must not be empty");
        }

        $char_limit = 64;
        $table_name_length = strlen($table_name);

        if ($table_name_length > $char_limit) {
            throw new \LengthException(sprintf(
                "Table name cannot contain more than %d characters, got %d",
                $char_limit,
                $table_name_length
            ));
        }

        if ($check_reserved) {

            $result = $this->server->statement(
                "SELECT `WORD`"
                . " FROM `INFORMATION_SCHEMA`.`KEYWORDS`"
                . " WHERE `WORD` = ?"
                    . " AND `RESERVED` = '1'",
                [
                    strtoupper($table_name)
                ]
            );

            if ($result->count() !== 0) {
                throw new ReservedException(sprintf(
                    "Table name \"%s\" is a reserved identifier",
                    $table_name
                ));
            }
        }

        return true;
    }


    //

    public function grantTableAbbreviation(string $table_name): string
    {

        $len = $counter = 1;
        $table_name_len = strlen($table_name);

        do {

            if ($len < $table_name_len) {
                $abbreviation = substr($table_name, 0, $len);
                $len++;
            } elseif ($len === $table_name_len) {
                $abbreviation = $table_name;
                $len++;
            } else {
                $abbreviation = ($table_name . $counter);
                $counter++;
            }

        } while (in_array($abbreviation, $this->table_abbreviations));

        $this->table_abbreviations[] = $abbreviation;

        return $abbreviation;
    }


    //

    public function getDescriptor(): DatabaseDescriptorInterface
    {

        return new DatabaseDescriptor($this);
    }


    //

    public function getStoreFieldValueFormatter(): DatabaseStoreFieldValueFormatter
    {

        return new DatabaseStoreFieldValueFormatter($this);
    }


    // Checks if table exists

    public function hasTable(string $table_name): bool
    {

        $result = $this->server->statement(
            "SELECT `table_name`"
            . " FROM `INFORMATION_SCHEMA`.`tables`"
            . " WHERE `table_schema` = ? AND `table_name` = ?"
            . " LIMIT 1",
            [$this->database_name, $table_name]
        );

        // Field count will not work here, because it returns a single field with "null" value, when table does not exist.
        return ($result && !empty($result->getOne()));
    }


    // Gets table instance

    public function getTable(string $table_name): Table
    {

        return new Table($this, $table_name);
    }


    // Gets database query object for the tables query

    public function getTablesQuery(): ?Result
    {

        return $this->server->statement(
            "SELECT `TABLE_NAME` table_name"
            . " FROM `INFORMATION_SCHEMA`.`tables`"
            . " WHERE `TABLE_SCHEMA` = ?",
            [$this->database_name]
        );
    }


    // Loops through all tables and returns database table object for each table entry.

    public function loopThroughTables(callable $callback): void
    {

        $this->getTablesQuery()->each(function (\stdClass $row) use ($callback): void {
            $callback(new Table($this, $row->table_name));
        });
    }


    // Gets information about table sizes.

    public function getTableSizes(string $table_prefix = null): array
    {

        $result = [
            '__index' => [
                'total_size' => 0
            ],
        ];

        $this->server->query("SHOW TABLE STATUS")->each(function (\stdClass $row) use (&$result, $table_prefix) {

            if (!$table_prefix || str_starts_with($row->Name, $table_prefix)) {

                $size = ($row->Data_length + $row->Index_length);
                $result['__index']['total_size'] += $size;
                $result[$row->Name] = $size;
            }

        });

        return $result;
    }


    // Drops tables filtered by a prefix name

    public function dropTablesWithPrefix(string $table_prefix): ?bool
    {

        $result = $this->server->statement(
            // Get all table names starting with the chosen prefix name and compose the DROP TABLE statement inside the query.
            "SELECT CONCAT( 'DROP TABLE ', GROUP_CONCAT('`', `TABLE_NAME`, '`'), ';' ) AS `statement`"
            . " FROM `INFORMATION_SCHEMA`.`tables`"
            . " WHERE `TABLE_SCHEMA` = ? AND `TABLE_NAME` LIKE CONCAT(?, '%')",
            [$this->database_name, $table_prefix]
        );

        $row = $result->getOne();

        if (empty($row->statement)) {
            return null;
        }

        $this->server->query($row->statement);

        return true;
    }


    // Drops a single table

    public function dropTable(string $table_name): void
    {

        $this->server->query(
            "DROP TABLE IF EXISTS `%s`",
            [$table_name]
        );
    }


    // Drops all tables

    public function truncate(): void
    {

        $this->getTablesQuery()->each(function (\stdClass $row): void {
            $this->dropTable($row->table_name);
        });
    }


    // Unlocks tables

    public function unlockTables(): void
    {

        $this->server->query("UNLOCK TABLES");
    }


    // Makes a simple transaction and runs custom callback inside it

    public function makeTransaction(callable $callback): void
    {

        try {

            $this->server_link->begin_transaction();

            // Callback inside the transaction.
            $callback();

            $this->server_link->commit();

        } catch (\mysqli_sql_exception $exception) {

            // Intercept the exception primarily for the rollback.
            $this->server_link->rollback();

            throw $exception;
        }
    }
}

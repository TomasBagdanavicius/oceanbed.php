<?php

declare(strict_types=1);

namespace LWP\Database;

use LWP\Common\Exceptions\DuplicateException;
use LWP\Common\FileLogger;
use LWP\Common\FileLoggerTrait;
use LWP\Database\Exceptions\ServerConnectError;
use LWP\Database\Exceptions\DatabaseStatementException;

class Server
{
    use FileLoggerTrait;


    public readonly \mysqli $link;
    private ?string $database_name = null;


    public function __construct(
        public readonly string $hostname,
        #[\SensitiveParameter]
        public readonly string $username,
        #[\SensitiveParameter]
        public readonly string $password,
        public readonly string $charset = 'utf8mb4',
        public readonly string $timezone = '+00:00',
        public readonly ?FileLogger $file_logger = null
    ) {

        // Instructs mysqli to throw exceptions when errors occur
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $this->link = new \mysqli($hostname, $username, $password);

        if ($this->link->connect_errno) {
            throw new ServerConnectError(sprintf(
                "MySQL server connection error: %s",
                $this->link->connect_error
            ));
        }

        $this->log("Connected to server");
        $this->setCharset($charset);
        $this->setTimezone($timezone);
    }


    //

    public function __destruct()
    {

        // After connection is closed, the link object is not emptied, however, such parameters as "thread_id" will be missing.
        if (is_object($this->link) && isset($this->link->thread_id)) {
            $this->link->close();
        }
    }


    // Sets charset.

    public function setCharset(string $charset): self
    {

        $this->link->set_charset($charset);

        return $this;
    }


    // Sets system timezone.

    public function setTimezone(string $timezone): self
    {

        $this->statement("SET `time_zone` = ?", [
            $timezone
        ]);

        return $this;
    }


    // Shorthand for "real_escape_string".

    public function escape(string $str): string
    {

        return $this->link->real_escape_string($str);
    }


    // Escape an array of values.

    public function escapeMulti(array $data): array
    {

        foreach ($data as $key => $value) {

            if (is_string($value) || is_int($value) || is_float($value)) {
                $data[$key] = $this->escape((string)$value);
            }
        }

        return $data;
    }


    // Formats a variable.

    public function formatVariable(string|int|float|bool|null $var): string|int|float
    {

        if (is_null($var)) {
            $var = 'NULL';
        } elseif ($var === true) {
            $var = 1;
        } elseif ($var === false) {
            $var = 0;
        } elseif (is_int($var) || ctype_digit($var)) {
            $var = intval($var);
        } elseif (is_string($var)) {
            $var = ('\'' . $this->escape($var) . '\'');
        }

        return $var;
    }


    // Runs a query on the MySQL server and returns a Result wrapper, or null when the query does not yield any results.

    public function query(string $query, array $params = []): Result|int
    {

        $this->log($query);

        if ($params) {
            $query = sprintf($query, ...$this->escapeMulti($params));
        }

        $result = $this->link->query($query);
        $affected_rows = $this->link->affected_rows;

        if (!$result) {
            throw new Exceptions\DatabaseQueryException(sprintf(
                "Database query error: %s; using %s",
                $this->link->error,
                $query
            ));
        }

        return ($result instanceof \mysqli_result)
            ? new Result($result, query: $query)
            : $affected_rows;
    }


    // Runs a query by using the prepare and execute statements and returns a Result wrapper, or null when the query does not yield any results.

    public function statement(string $prepare_statement, array $params): Result|int
    {

        $this->log($prepare_statement);

        $statement = $this->link->prepare($prepare_statement);

        if ($statement === false) {
            throw new DatabaseStatementException(sprintf("Database statement error: %s; using prepared statement: %s", $this->link->error, $prepare_statement));
        }

        $statement->execute($params);

        // Making sure to catch any other errors. Returns an integer, where zero means no error.
        if ($statement->errno) {
            throw new DatabaseStatementException(sprintf("Database statement error: %s; using prepared statement: %s", $statement->error, $prepare_statement));
        }

        // Note: this will return "false" with successful queries that do not yield any results, therefore thorough error management is required above.
        $result = $statement->get_result();

        $affected_rows = $statement->affected_rows;

        $statement->close();

        return ($result instanceof \mysqli_result)
            ? new Result($result, $prepare_statement, $params)
            : $affected_rows;
    }


    //

    public function multiQuery(string $multi_query): ResultCollection
    {

        $this->link->multi_query($multi_query);

        $result_collection = new ResultCollection();

        do {

            if ($result = $this->link->store_result()) {
                $result_collection->add(new Result($result));
            }

            #debug
            #var_dump($this->link->affected_rows);
            #var_dump($this->link->insert_id); // int(0) when nothing.
            #var_dump($this->link->info); // NULL when nothing.
            #end debug

        } while ($this->link->next_result());

        return $result_collection;
    }


    // Checks if database exists.

    public function databaseExists(string $database_name): bool
    {

        $result = $this->statement("SELECT `SCHEMA_NAME` FROM `INFORMATION_SCHEMA`.SCHEMATA WHERE `SCHEMA_NAME` = ? LIMIT 1", [
            $database_name,
        ]);

        // Field count will not suit here, because it returns a single field with NULL value, when database doesn't exist.
        return ($result && !empty($result->getOne()));
    }


    // Gets a database instance.

    public function getDatabase(string $database_name): Database
    {

        return new Database($this, $database_name);
    }


    // Creates a new database.

    public function createDatabase(string $database_name, string $character_set = 'utf8mb4', string $collate = 'utf8mb4_unicode_520_ci'): Database
    {

        if ($this->databaseExists($database_name)) {
            throw new DuplicateException(sprintf(
                "Database \"%\" already exists",
                $database_name
            ));
        }

        // Apparently, this SQL statement doesn't support prepared statements, hence regular query.
        $this->query(
            "CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET `%s` COLLATE `%s`",
            [$database_name, $character_set, $collate]
        );

        return $this->getDatabase($database_name);
    }


    // Drops a database.

    public function dropDatabase(string $database_name): void
    {

        if (!$this->databaseExists($database_name)) {
            throw new \Exception(
                "Database \"$database_name\" does not exist and thus cannot be deleted"
            );
        }

        $this->query(
            "DROP DATABASE `%s`",
            [$database_name],
        );
    }


    // Gets a list of all databases.

    public function getDatabaseList(): array
    {

        // Should returns rows containing one field "Database".
        return $this->query("SHOW DATABASES")->toArray(first_elem_only: true);
    }


    // Fetches the current session ID.

    public function getSessionId(): int
    {

        return (int)$this->query("SELECT CONNECTION_ID() AS `session_id`")->getOne()->session_id;
    }


    // Fetches the last insert ID.

    public function getLastInsertId(): ?int
    {

        return (int)$this->query("SELECT LAST_INSERT_ID() AS `last_insert_id`")?->getOne()->last_insert_id;
    }


    // Encloses the given string with backtick quotes.

    public static function formatAsQuotedIdentifier(string $name, bool $first_dot_as_abbr_mark = false): string
    {

        return ($first_dot_as_abbr_mark && ($pos = strpos($name, '.')) !== false)
            ? ('`' . substr($name, 0, $pos) . '`.`' . substr($name, ($pos + 1)) . '`')
            : ('`' . $name . '`');
    }


    // Builds and formats a column identifier string.

    public static function formatColumnIdentifierSyntax(string $column_name, ?string $prefix = null): string
    {

        $result = '';

        if ($prefix) {
            $result .= (self::formatAsQuotedIdentifier($prefix) . '.');
        }

        $result .= self::formatAsQuotedIdentifier($column_name);

        return $result;
    }


    //

    public static function formatColumnList(array $column_list, ?string $prefix = null): array
    {

        array_walk($column_list, static function (string &$element) use ($prefix): void {
            $element = self::formatColumnIdentifierSyntax($element, $prefix);
        });

        return $column_list;
    }


    // Builds and formats a table identifier string.

    public static function formatTableIdentifierSyntax(string $table_name, ?string $table_abbreviation = null): string
    {

        $result = (self::formatAsQuotedIdentifier($table_name));

        if ($table_abbreviation) {
            $result .= (' ' . self::formatAsQuotedIdentifier($table_abbreviation));
        }

        return $result;
    }


    //

    public static function formatColumnStringFromMetaData(array $meta, bool $use_as = false): string
    {

        if (empty($meta['column'])) {
            throw new \Exception("Column element is required");
        }

        $params = [
            $meta['column']
        ];

        if (!empty($meta['table_reference'])) {
            $params[] = $meta['table_reference'];
        }

        $sql_str = self::formatColumnIdentifierSyntax(...$params);

        if (!empty($meta['alias_name'])) {

            if ($use_as) {
                $sql_str .= " AS";
            }

            $sql_str .= (" " . self::formatAsQuotedIdentifier($meta['alias_name']));
        }

        return $sql_str;
    }
}

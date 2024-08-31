<?php

declare(strict_types=1);

namespace LWP\Database;

use LWP\Common\String\Str;
use LWP\Common\Collectable;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Enums\NamedOperatorsEnum;
use LWP\Common\Indexable;
use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Components\Datasets\Interfaces\DatabaseInterface;
use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Datasets\Relationships\RelationshipCollection;
use LWP\Components\Properties\BaseProperty;
use LWP\Database\SyntaxBuilders\BasicSqlQueryBuilder;
use LWP\Components\Datasets\AbstractDataset;
use LWP\Database\Exceptions\TableNotFoundException;
use LWP\Components\Model\BasePropertyModel;
use LWP\Components\Datasets\Exceptions\CreateEntryException;
use LWP\Database\Exceptions\ColumnNotFoundException;
use LWP\Common\Enums\ReadWriteModeEnum;
use LWP\Database\Exceptions\AutoColumnNotFoundException;
use LWP\Filesystem\Path\FilePath;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\generateStringNotIn;

class Table extends AbstractDataset implements DatasetInterface, Indexable, Collectable
{
    use \LWP\Common\IndexableTrait;
    use \LWP\Common\CollectableTrait;


    /* Main options */
    public const CHECK_IF_TABLE_EXISTS = 1;
    public const VALIDATE_DATASET_NAME = 2;

    public readonly Server $server;
    public readonly \mysqli $server_link;
    private bool $lock_status = false;
    protected string $abbreviation;
    public readonly array $column_list;
    public readonly string $default_abbreviation;
    private array $column_definition_data_array;
    public readonly ?int $table_options;


    public function __construct(
        Database $database,
        public readonly string $table_name,
        ?int $options = self::CHECK_IF_TABLE_EXISTS | self::VALIDATE_DATASET_NAME
    ) {

        $this->table_options = $options;
        $check_table_exists = ($options & self::CHECK_IF_TABLE_EXISTS);

        if ($check_table_exists && !$database->hasTable($table_name)) {
            throw new TableNotFoundException(sprintf(
                "SQL database table \"%s\" was not found",
                $table_name
            ));
        }

        $this->server = $database->server;
        $this->server_link = $database->server_link;

        parent::__construct(
            $table_name,
            $database,
            ($check_table_exists
                ? null
                : (($options & self::VALIDATE_DATASET_NAME)
                    ? parent::VALIDATE_NAME
                    : null))
        );

        $this->column_list = $this->own_container_list;
        $this->default_abbreviation = $database->grantTableAbbreviation($table_name);
    }


    // Returns dataset name abbreviation.

    public function getAbbreviation(array $taken = []): string
    {

        $abbreviation = ($this->abbreviation ?? $this->default_abbreviation);

        if ($taken) {
            $abbreviation = generateStringNotIn($taken, $abbreviation);
        }

        return $abbreviation;
    }


    // Validates column name

    public function validateColumnName(string $column_name): true
    {

        return $this->database->validateTableName($column_name, check_reserved: false);
    }


    // Returns table fetch manager

    public function getFetchManager(): TableDatasetFetchManager
    {

        $class_name = TableDatasetFetchManager::class;

        return new ($class_name)($this);
    }


    // Throws the "column not found" exception

    protected function throwColumnNotFoundException(string $column_name): void
    {

        throw new ColumnNotFoundException(sprintf(
            "Column \"%s\" was not found",
            $column_name
        ));
    }


    // Checks if given column name is valid

    public function assertColumn(string $column_name, bool $strict = false): void
    {

        // From preloaded
        if (isset($this->column_list)) {
            $is_valid = in_array($column_name, $this->column_list);
            // Strict
        } elseif ($strict) {
            $is_valid = $this->hasColumn($column_name, force: true);
            // Name validation
        } else {
            $is_valid = $this->validateColumnName($column_name);
        }

        if (!$is_valid) {
            $this->throwColumnNotFoundException($column_name);
        }
    }


    // Checks if given multiple column names are valid

    public function assertColumns(array $column_names, bool $strict = false): void
    {

        foreach ($column_names as $column_name) {
            $this->assertColumn($column_name, $strict);
        }
    }


    // Parses a MySQL data type declaration string (eg. "int unsigned", "varchar(50)", etc.).

    public static function parseFieldString(string $field_string): array
    {

        $result = [];
        $whitespace_or_bracket_closest = Str::posMultipleClosest($field_string, [" ", '(']);

        $name = (empty($whitespace_or_bracket_closest))
            ? $field_string
            : substr($field_string, 0, $whitespace_or_bracket_closest[0]);

        if (!ctype_alpha($name)) {
            throw new \RangeException("Data type name must contain alpha characters only.");
        }

        $result['name'] = $name;

        if (!empty($whitespace_or_bracket_closest)) {

            $current_pos = $whitespace_or_bracket_closest[0];

            if ($whitespace_or_bracket_closest[1] == "(") {

                // Opening quote position. Expecting no whitespace chars between the opening bracket and quote.
                $current_pos = $open = ($current_pos + 1);
                // Move to char succeeding the opening bracket.
                $current_pos++;

                $quote_or_bracket_closest = Str::posMultipleClosest($field_string, ["'", ')'], $current_pos);

                // Quoted params.
                if ($quote_or_bracket_closest[1] == "'") {

                    $next_pos = $quote_or_bracket_closest[0];

                    while ($next_pos !== false) {

                        if ($next_pos !== false) {

                            // Open state.
                            if ($open !== false) {

                                // Succeeded by another quote, meaning escaped.
                                if ($field_string[($next_pos + 1)] == "'") {

                                    $current_pos = ($next_pos + 2); // Skip.

                                    // Closing quote.
                                } else {

                                    $current_pos = ($next_pos + 1);
                                    $result['params'][] = substr($field_string, ($open + 1), ($next_pos - ($open + 1)));
                                    $open = false;
                                }

                            } else {

                                $current_pos = $open = $next_pos;
                                $current_pos++;
                            }
                        }

                        // Find next quote.
                        $next_pos = strpos($field_string, "'", $current_pos);
                    }

                    // Unquoted params.
                } else {

                    $current_pos = $quote_or_bracket_closest[0];

                    $result['params'] = explode(',', substr($field_string, $open, ($current_pos - $open)));
                }
            }
        }

        if (isset($current_pos)) {

            $suffix_part = substr($field_string, ($current_pos + 1));

            if ($suffix_part) {

                $result['options'] = explode(' ', trim($suffix_part));
            }
        }

        return $result;
    }


    // Converts a MySQL data type declaration string (eg. "int unsigned", "varchar(50)", etc.) to definition data array.

    public static function fieldTypeStringToDefinitionDataArray(string $field_type): array
    {

        $result = [];
        $parse_data = self::parseFieldString($field_type);

        switch ($parse_data['name']) {

            case 'int':
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'bigint':

                $result['type'] = 'integer';

                if (isset($parse_data['params'])) {

                    $length = (int)$parse_data['params'][0];

                    // If "length" is ever supported in definitions, this could be assigned directly.
                    if ($length < 10) {
                        $result['max'] = (int)str_repeat('9', $length);
                    }
                }

                if (isset($parse_data['options'])) {

                    if (in_array('unsigned', $parse_data['options'])) {
                        $result['min'] = 0;
                    }
                }

                break;

            case 'varchar':
            case 'char':
            case 'text':

                $result['type'] = 'string';

                if (isset($parse_data['params'])) {
                    $result['max'] = (int)$parse_data['params'][0];
                }

                if ($parse_data['name'] === 'varchar') {
                    $result['searchable'] = true;
                }

                break;

            case 'decimal':

                $result['type'] = 'number';
                # Could also support main part length.
                $result['number_format'] = [
                    'fractional_part_length' => (int)$parse_data['params'][1],
                ];

                break;

            case 'json':

                $result['type'] = 'json';

                break;

            case 'datetime':

                $result['type'] = 'datetime';

                break;
        }

        return $result;
    }


    // Builds definition array from MySQL column metadata.

    public function getColumnDefinitionDataArray(): array
    {

        // Run multi to save on round trips.
        $result = $this->server->multiQuery(
            // Keyword "FULL" enables visibility of "Collation", "Privileges", "Comment" columns.
            sprintf("SHOW FULL COLUMNS FROM `%s`", $this->table_name)
            . ';'
            . sprintf("SHOW INDEXES FROM `%s`", $this->table_name)
        );

        // Always expected.
        $first_result = $result->get(0);
        $data_array = [];

        foreach ($first_result as $data) {

            $definitions = self::fieldTypeStringToDefinitionDataArray($data['Type']);

            // Trace if current column value can be autofilled, eg. NULL, current date-time, etc.
            $autofill = false;

            // When it's none or NULL in the database, this will be NULL.
            if (isset($data['Default']) && $data['Default'] !== 'CURRENT_TIMESTAMP') {
                $definitions['default'] = $data['Default'];
                $autofill = true;
            }

            if ($data['Null'] === 'YES') {
                $definitions['nullable'] = true;
                $autofill = true;
            }

            if ($data['Extra'] === 'auto_increment' || $data['Extra'] === 'DEFAULT_GENERATED') {
                $autofill = true;
            }

            if (!$autofill) {
                $definitions['required'] = true;
            }

            if (isset($data['Comment'])) {
                $definitions['description'] = $data['Comment'];
            }

            $data_array[$data['Field']] = $definitions;
        }

        // Always expected.
        $second_result = $result->get(1);

        foreach ($second_result as $index => $data) {

            $column_name = $data['Column_name'];

            if (isset($data_array[$column_name])) {

                if ($data['Non_unique'] == 0) {
                    $data_array[$column_name]['unique'] = true;
                }
            }
        }

        return $data_array;
    }


    // Gets the reusable definition data array for all columns

    public function getReusableColumnDefinitionDataArray(): array
    {

        return $this->column_definition_data_array ??= self::getColumnDefinitionDataArray();
    }


    // Gets column definition collection set.

    public function getColumnDefinitionCollectionSet(): DefinitionCollectionSet
    {

        return new DefinitionCollectionSet(self::getColumnDefinitionDataArray());
    }


    // Gets a result instance for the most abstract select all query.

    public function getAllResult(): Result
    {

        return $this->server->query("SELECT * FROM `%s`", [$this->table_name]);
    }


    // Run a query that selects only given columns.

    public function getSelectedAllResult(array $selected_fields): Result
    {

        return $this->server->query("SELECT %s FROM `%s`", [
            $this->formatFieldList($selected_fields),
            $this->table_name,
        ]);
    }


    // Gets single column field value(s) by single clause.

    public function getColumnBy(string $column_name, string $field_name, string|int|float $field_value): Result
    {

        $this->assertColumn($column_name);
        $this->assertColumn($field_name);

        return $this->server->statement(
            sprintf(
                "SELECT `%s` FROM `%s` WHERE `%s` = ?",
                $column_name,
                $this->table_name,
                $field_name
            ),
            [
                $field_value
            ]
        );
    }


    // Gets multiple column field values by single clause.

    public function getColumnsBy(array $column_names, string $field_name, string|int|float $field_value): Result
    {

        $this->assertColumns($column_names);
        $this->assertColumn($field_name);

        return $this->server->statement(
            sprintf(
                "SELECT %s FROM `%s` WHERE `%s` = ?",
                // It's safe to use a rather simple join function, because the columns have been verified
                self::joinFieldList($column_names),
                $this->table_name,
                $field_name
            ),
            [
                $field_value,
            ]
        );
    }


    // Update field values in multiple columns by a single column condition.

    public function updateBy(string $column_name, string|int|float $field_value, array $data): int
    {

        $this->validateColumnName($column_name);

        $column_strs = [];

        foreach ($data as $data_column_name => $data_field_value) {

            $this->validateColumnName($data_column_name);
            $column_strs[] = ('`' . $data_column_name . '` = ?');
        }

        return $this->server->statement(sprintf(
            "UPDATE `%s` SET %s WHERE `%s` = ?",
            $this->table_name,
            implode(',', $column_strs),
            $column_name
        ), [
            ...array_values($data),
            $field_value,
        ]);
    }


    //

    public function updateEntryBasic(string $container_name, string|int|float $field_value, array $data): int|string
    {

        $this->containers->assertUniqueContainer($container_name);

        #tbd
    }


    //

    public function updateIntegerContainerValue(string $container_name, int $field_value, int|string $primary_key): int
    {

        $this->assertColumnExistence($container_name);
        $primary_container_name = $this->getPrimaryContainerName();

        return $this->server->statement(
            sprintf(
                "UPDATE `%s` SET `%s` = ? WHERE `%s` = ?",
                $this->table_name,
                $container_name,
                $primary_container_name
            ),
            [
                $field_value,
                $primary_key
            ]
        );
    }


    // Checks if given column exists

    public function hasColumn(string $column_name, bool $force = false): bool
    {

        if (!$force && isset($this->column_list)) {
            return in_array($column_name, $this->column_list);
        }

        $result = $this->server->statement(
            "SELECT `COLUMN_NAME`"
            . " FROM `INFORMATION_SCHEMA`.`COLUMNS`"
            . " WHERE `TABLE_SCHEMA` = DATABASE()"
                . " AND `TABLE_NAME` = ?"
                . " AND `COLUMN_NAME` = ?",
            [$this->table_name, $column_name]
        );

        return ($result->mysqli_result->num_rows !== 0);
    }


    // Throws an exception if given column does not exist

    public function assertColumnExistence(string $column_name, bool $force = false): void
    {

        if (!$this->hasColumn($column_name, $force)) {
            throw new ColumnNotFoundException(sprintf(
                "Column \"%s\" was not found",
                $column_name
            ));
        }
    }


    // Looks for given columns

    public function findColumns(array $column_names, bool $force = false): array
    {

        if (!$force && isset($this->column_list)) {
            return array_intersect($this->column_list, $column_names);
        }

        $column_names_count = count($column_names);
        $result = $this->server->statement(
            sprintf(
                "SELECT `COLUMN_NAME` `name`"
                . " FROM `INFORMATION_SCHEMA`.`COLUMNS`"
                . " WHERE `TABLE_SCHEMA` = DATABASE()"
                    . " AND `TABLE_NAME` = ?"
                    . " AND `COLUMN_NAME` IN (%s)",
                implode(',', array_fill(0, $column_names_count, '?'))
            ),
            [
                $this->table_name,
                ...$column_names
            ]
        );

        // All columns were found
        if ($result->mysqli_result->num_rows === $column_names_count) {
            return $column_names;
        }

        $found_columns = [];

        foreach ($result as $row) {
            $found_columns[] = $row['name'];
        }

        return $found_columns;
    }


    // Checks whether all given columns exist

    public function hasColumns(array $column_names, bool $force = false): bool
    {

        $found_columns = $this->findColumns($column_names, $force);

        return (count($column_names) === count($found_columns));
    }


    // Throws if any of given columns does not exist

    public function assertColumnsExistence(array $column_names, bool $force = false): void
    {

        $found_columns = $this->findColumns($column_names, $force);

        if (count($column_names) !== count($found_columns)) {

            $diff = array_diff($column_names, $found_columns);

            throw new ColumnNotFoundException(sprintf(
                "Some columns were not found: %s",
                ('"' . implode('", ', $diff) . '"')
            ));
        }
    }


    // Gets auto increment value from table options.

    public function getAutoIncrement(): ?int
    {

        $result = $this->server->statement(
            "SELECT `AUTO_INCREMENT` `ai`"
            . " FROM `INFORMATION_SCHEMA`.`tables`"
            . " WHERE `TABLE_NAME` = ?"
            . " AND `TABLE_SCHEMA` = DATABASE()",
            [$this->table_name]
        );

        if (!$result) {
            return null;
        }

        $row = $result->getOne();

        return (!empty($row))
            ? (int)$row->ai
            : null;
    }


    // Locks this table

    public function lock(ReadWriteModeEnum $mode = ReadWriteModeEnum::WRITE): void
    {

        // "LOCK TABLES" does not produce a result
        $this->server->query(
            "LOCK TABLES `%s` %s",
            [$this->table_name, $mode->name]
        );
    }


    // Unlocks this table

    public function unlock(): void
    {

        $this->database->unlockTables();
    }


    // Truncates this table

    public function truncate(): void
    {

        $this->server->query("TRUNCATE `%s`", [
            $this->table_name
        ]);
    }


    // Returns the primary auto-increment container

    public function getPrimaryAutoContainer(): ?string
    {

        $result = $this->server->statement(
            "SELECT `COLUMN_NAME` `column_name`"
            . " FROM `INFORMATION_SCHEMA`.`COLUMNS`"
            . " WHERE `TABLE_NAME` = ?"
            . " AND `EXTRA` = 'auto_increment'",
            [$this->table_name]
        );

        return ($result->count() === 0)
            ? null
            : $result->getOne()->column_name;
    }


    // Gets maximum value in the given column.

    public function getMaxValueByColumn(string $column_name): null|int|string
    {

        $result = $this->server->query(
            "SELECT MAX(`%s`) `max_value`"
            . " FROM `%s`",
            [$column_name, $this->table_name]
        );

        $row = $result->getOne();

        return (!empty($row))
            // When no rows are found, "max_value" will be equal to "null"
            ? $row->max_value
            : null;
    }


    // Set auto increment value for a table based on the max value of the auto column field.

    public function resetAutoIncrement(): int
    {

        $auto_column = $this->getPrimaryAutoContainer();

        if (!$auto_column) {
            throw new AutoColumnNotFoundException(sprintf(
                "Table \"%s\" does not contain an auto column",
                $this->table_name
            ));
        }

        $new_increment = ((int)$this->getMaxValueByColumn($auto_column) + 1);

        $this->server->query(
            // Increment value should be left unquoted, otherwise it's triggering a MySQL error.
            "ALTER TABLE `%s` AUTO_INCREMENT = %d",
            [$this->table_name, $new_increment]
        );

        return $new_increment;
    }


    /**
     * Returns the query result for the column list
     *
     * @return Result The query result as a Result object.
     */
    public function getColumnListResult(): Result
    {

        return $this->server->statement(
            "SELECT `COLUMN_NAME`"
            . " FROM `INFORMATION_SCHEMA`.`COLUMNS`"
            . " WHERE `TABLE_SCHEMA` = DATABASE() AND `TABLE_NAME` = ?",
            [$this->table_name]
        );
    }

    /**
     * Returns the column list
     *
     * @param bool $force Whether to force fetching the column list again, even if it was already fetched.
     * @return array The column list as an array.
     */
    public function getColumnList(bool $force = false): array
    {

        if (!$force && isset($this->column_list)) {
            return $this->column_list;
        }

        return $this->getColumnListResult()->toArray(first_elem_only: true);
    }


    // Returns the column count

    public function getColumnCount(bool $force = false): int
    {

        if (!$force && isset($this->column_list)) {
            return count($this->column_list);
        }

        return iterator_count(
            $this->getColumnListResult()->getIterator()
        );
    }


    //

    protected function getUniqueFieldsFromDataStore(array $data): array
    {

        $definition_data_array = $this->getReusableColumnDefinitionDataArray();
        $col_diff = array_diff_key($data, $definition_data_array);

        if ($col_diff) {

            throw new ColumnNotFoundException(sprintf(
                "Some columns were not recognized: %s.",
                ('"' . implode('", "', array_keys($col_diff)) . '"')
            ));
        }

        // Collects unique fields data array.
        $unique_fields = [];

        foreach ($definition_data_array as $field_name => $data_array) {

            # Auto-increment fields are not excluded explicitly. Since only string types can be mutated, they won't be in the mix.
            if (
                // Is in fact unique.
                !empty($data_array['unique'])
                // Since this is modification oriented, it cannot accept readonly properties.
                && empty($data_array['readonly'])
                // Field is actually to be modified, and type is available.
                && isset($data_array['type'])
                // Accepts only string types to be mutated.
                && $data_array['type'] == 'string'
            ) {
                $unique_fields[$field_name] = $data_array;
            }
        }

        return $unique_fields;
    }


    // Attemps to insert given data without validating the values

    public function insertBasic(array $data): int
    {

        $sql_query_str = sprintf(
            "INSERT INTO `%s` %s VALUES (%s)",
            $this->table_name,
            $this->formatFieldListWithBrackets(array_keys($data)),
            implode(',', array_fill(0, count($data), '?'))
        );

        $this->server->statement($sql_query_str, array_values($data));

        return $this->server_link->insert_id;
    }


    //

    public function insert(array $data, bool $product = true): array
    {

        return $this->createEntry($data, $product);
    }


    //

    public function createEntryBasic(array $data): int|string
    {

        return $this->insertBasic($data);
    }


    // Locks all columns for update

    public function lockAllColumnsForUpdate(): void
    {

        $this->server->query(
            // Apparently, "FOR UPDATE" locking works only inside transaction.
            "SELECT * FROM `%s` FOR UPDATE",
            [$this->table_name]
        );
    }


    // Locks selected columns for update
    /* Should be used inside a transaction. */

    public function lockColumnsForUpdate(array $column_names): void
    {

        $this->server->query(
            // Apparently, "FOR UPDATE" locking works only inside a transaction
            "SELECT %s FROM `%s` FOR UPDATE",
            [$this->formatFieldList($column_names), $this->table_name]
        );
    }


    //

    public function countByConditionWithPrimaryExcluded(Condition $condition, array $exclude_primary = []): int
    {

        $query_print_params
            = $execute_params
            = [];

        $query_statement = "SELECT COUNT(*) `count` FROM `%s` WHERE";
        $query_print_params[] = $this->table_name;

        if ($exclude_primary) {

            $query_statement .= " `%s` NOT IN (%s) AND";
            $query_print_params[] = $this->getPrimaryColumnName();
            $query_print_params[] = implode(',', array_fill(0, count($exclude_primary), '?'));
            $execute_params = [...$execute_params, ...$exclude_primary];
        }

        $query_statement .= " `%s` = ?";
        $query_print_params[] = $condition->keyword;
        $execute_params[] = $condition->value;

        $result = $this->server->statement(
            sprintf($query_statement, ...$query_print_params),
            $execute_params
        );

        return $result->getOne()->count;
    }


    // Creates a list of fields.

    public function formatFieldList(array $fields): string
    {

        foreach ($fields as &$field) {
            if (is_string($field)) {
                $this->server->escape($field);
            }
        }

        return self::joinFieldList($fields);
    }


    // Joins a list of fields by wrapping each field into backticks

    public static function joinFieldList(array $fields): string
    {

        return ("`" . implode('`,`', $fields) . "`");
    }


    // Creates an enclosed list of fields.

    public function formatFieldListWithBrackets(array $fields): string
    {

        return ('(' . $this->formatFieldList($fields) . ')');
    }


    // Executes a multi dataset query string.
    /* If any of the rows to be inserted fails, the entire SQL sentence will not be executed. Having said that, it seems to be safe to return a range of ID numbers starting from the auto increment number. */

    public function executeMultiInsertQuery(string $query_string, array $statement_params = null): array
    {

        $auto_increment = null;
        $affected_rows_count = null;

        $this->database->makeTransaction(function () use ($query_string, $statement_params, &$auto_increment, &$affected_rows_count): void {

            /* Getting auto increment statically will not work, because since MySQL 8.0 auto increment is updated once every 24 hours. To overcome that I would need to run `SET PERSIST information_schema_stats_expiry = 0;`. The solution here is to reset auto increment. As a tradeoff, amending expiry seems to be more costly than doing a num column increment reset. */
            $auto_increment = $this->resetAutoIncrement();

            $result = (!$statement_params)
                // When statement params are not available, run as a typical query.
                ? $this->server->query($query_string)
                // Run as a statement query.
                : $this->server->statement($query_string, $statement_params);

            $affected_rows_count = $this->server_link->affected_rows;

        });

        return range($auto_increment, ($auto_increment + $affected_rows_count - 1));
    }


    // Puts data to a CSV file.

    public function putDataToCsvFile(FilePath $file_path, array $column_list = null, bool $add_quotes = true): bool
    {

        return $this->getAllResult()->putDataToCsvFile($file_path, $column_list, $add_quotes);
    }


    // Creates base property object for a given column.

    public function getColumnProperty(string $column_name): BaseProperty
    {

        $this->assertColumnExistence($column_name);

        return BaseProperty::fromDefinitionArray(
            $column_name,
            $this->getColumnDefinitionDataArray()[$column_name]
        );
    }


    // Gets primary column name. There can only be one primary column.

    public function getPrimaryColumnName(): ?string
    {

        $result = $this->server->statement(
            "SELECT `COLUMN_NAME` `column_name`"
            . " FROM `INFORMATION_SCHEMA`.`COLUMNS`"
            . " WHERE `TABLE_SCHEMA` = DATABASE()"
            . " AND `COLUMN_KEY` = 'PRI'"
            . " AND `TABLE_NAME` = ?",
            [$this->table_name]
        );

        if (!$result->mysqli_result->num_rows) {
            return null;
        }

        return $result->getOne()->column_name;
    }


    //

    public function getIndexablePropertyList(): array
    {

        return [
            'name',
            'primary_column_name',
        ];
    }


    //

    public function getIndexablePropertyValue(string $property_name): mixed
    {

        return match ($property_name) {
            'name' => $this->table_name,
            'primary_column_name' => $this->getPrimaryColumnName(),
        };
    }


    //

    public function containsPrimaryColumnValue(int $id): bool
    {

        $primary_column_name = $this->getPrimaryColumnName();

        $result = $this->server->statement(sprintf(
            "SELECT COUNT(`%s`) `count`"
            . " FROM `%s`"
            . " WHERE `%1$s` = ?",
            $primary_column_name,
            $this->table_name
        ), [
                $id,
            ]);

        return ((int)$result->getOne()->count > 0);
    }


    //

    public function getIdentifierSyntaxString(): string
    {

        return Server::formatTableIdentifierSyntax($this->table_name, $this->getAbbreviation());
    }


    //

    public function deleteBySingleColumn(string $column_name, string|int|float $field_value): int
    {

        $this->assertColumnExistence($column_name);

        return $this->server->statement(sprintf(
            "DELETE FROM `%s` WHERE `%s` = ?",
            $this->table_name,
            $column_name
        ), [$field_value]);
    }


    //

    public function deleteByMultipleFieldValues(string $column_name, array $field_values): int
    {

        if (!array_is_list($field_values)) {
            throw new \ValueError(sprintf(
                "%s%s(): Argument #2 (\$field_values) must be a list array",
                self::class,
                __FUNCTION__
            ));
        }

        $this->assertColumnExistence($column_name);

        $clause_format = array_fill(0, count($field_values), '`%1$s` = ?');
        $sql_query_str = sprintf(
            "DELETE FROM `%s` WHERE `%s` IN (%s)",
            $this->table_name,
            $column_name,
            implode(',', array_fill(0, count($field_values), '?'))
        );

        return $this->server->statement($sql_query_str, $field_values);
    }


    //

    public function deleteByMultipleColumns(array $data, NamedOperatorsEnum $operator = NamedOperatorsEnum::AND): int
    {

        $column_names = array_keys($data);
        $this->assertColumnsExistence($column_names);

        $clause_format = array_fill(0, count($data), '`%s` = ?');
        $sql_query_str = sprintf(
            "DELETE FROM `%s` WHERE %s",
            $this->table_name,
            implode(' ' . $operator->name . ' ', $clause_format)
        );

        return $this->server->statement(
            sprintf($sql_query_str, ...$column_names),
            array_values($data)
        );
    }


    //

    public function deleteByConditionObject(Condition|ConditionGroup $condition_object): int
    {

        if ($condition_object instanceof Condition) {

            return $this->deleteBySingleColumn($condition_object->keyword, $condition_object->value);

        } else {

            if ($condition_object->count() === 0) {
                throw new \ValueError("Condition object must not be empty");
            }

            $this->assertColumnsExistence($condition_object->getKeywords());

            if ($condition_object->hasStringifyReplacer()) {
                $stringify_replacer_copy = $condition_object->getStringifyReplacer();
            }

            $basic_sql_query_builder = new BasicSqlQueryBuilder($this->server);
            $fallback_condition_data = ['parameterize' => true];
            $condition_object->setStringifyReplacer(fn (Condition $condition) => $basic_sql_query_builder->stringifyCondition($condition, $fallback_condition_data));
            $sql_query_str = sprintf(
                "DELETE FROM `%s` WHERE %s",
                $this->table_name,
                $condition_object->__toString()
            );

            if (isset($stringify_replacer_copy)) {
                $condition_object->setStringifyReplacer($stringify_replacer_copy);
            }

            return $this->server->statement(
                $sql_query_str,
                $condition_object->getValues(unique: false)
            );
        }
    }


    /* Dataset Related */


    //

    public function setupModelPopulateCallbacks(BasePropertyModel $model): void
    {

        // Nothing to do here
    }


    // Validates identifier

    public function validateIdentifier(string $identifier, int $char_limit = 30): true
    {

        return $this->database->validateTableName($identifier);
    }


    //

    public function getStoreFieldValueFormatterIteratorClassName(): string
    {

        return TableStoreFieldValueFormatterIterator::class;
    }


    //

    public function getStoreHandleClassName(): string
    {

        return TableDatasetStoreHandle::class;
    }


    //

    public function getDefinitionDataArray(): array
    {

        return $this->getColumnDefinitionDataArray();
    }


    //

    public function buildMainUniqueCase(
        BasePropertyModel $model,
        bool $parameterize = true,
        array &$execution_params = [],
    ): ?ConditionGroup {

        return null;
    }


    // Checks if given column contains given value.

    public function containsContainerValue(
        string $container_name,
        ?string $value,
        ?ConditionGroup $condition_group = null
    ): bool {

        $this->containers->hasContainer($container_name);

        $abbreviation = $this->getAbbreviation();
        $container_name_with_prefix = Server::formatColumnIdentifierSyntax($container_name, $abbreviation);

        $basic_sql_query_builder = (new BasicSqlQueryBuilder($this->server))
            ->select('COUNT(' . BasicSqlQueryBuilder::ALL_SYMBOL . ')', 'count')
            ->from($this->table_name, $abbreviation)
            ->where($container_name_with_prefix . ' = ?');

        $values = [
            $value
        ];

        if ($condition_group) {

            $basic_sql_query_builder->whereCondition($condition_group);

            if (count($condition_group) !== 0) {
                $values = [...$values, ...$condition_group->getValues()];
            }
        }

        $result = $this->server->statement(
            $basic_sql_query_builder->__toString(),
            $values
        );

        return ($result->getOne()->count != 0);
    }


    /**
     * Checks if the given container contains given values
     *
     * @param string              $container_name  Container name
     * @param array               $values          Values to look for
     * @param ConditionGroup|null $condition_group Additional query conditions
     * @return array A list of found values.
     */
    public function containsContainerValues(
        string $container_name,
        array $values,
        ?ConditionGroup $condition_group = null
    ): array {

        $this->assertOwnContainer($container_name);

        $abbreviation = $this->getAbbreviation();
        $container_name_with_prefix = Server::formatColumnIdentifierSyntax($container_name, $abbreviation);
        $in_func_placeholders = implode(',', array_fill(0, count($values), '?'));

        $basic_sql_query_builder = (new BasicSqlQueryBuilder($this->server))
            ->select($container_name_with_prefix)
            ->from($this->table_name, $abbreviation)
            ->where($container_name_with_prefix . ' IN(' . $in_func_placeholders . ')')
            ->groupBy($container_name_with_prefix);

        if ($condition_group) {

            $basic_sql_query_builder->whereCondition($condition_group);

            if (count($condition_group) !== 0) {
                $values = [...$values, ...$condition_group->getValues()];
            }
        }

        $result = $this->server->statement(
            $basic_sql_query_builder->__toString(),
            $values
        );

        $found_values = [];

        foreach ($result as $data) {
            $found_values[] = $data[$container_name];
        }

        return $found_values;
    }


    // Tells if this dataset supports multiqueries.

    public function supportsMultiQuery(): bool
    {

        return $this->database->supportsMultiQuery();
    }


    // Gets primary container name

    public function getPrimaryContainerName(): ?string
    {

        return $this->getPrimaryColumnName();
    }


    //

    public function getSelectHandleClassName(): string
    {

        return TableDatasetSelectHandle::class;
    }


    //

    public function modelFillIn(BasePropertyModel $model): void
    {

        // Nothing to be done here
    }


    //

    public function lockContainersForUpdate(array $container_names): void
    {

        $this->lockColumnsForUpdate($container_names);
    }


    // Validates field value of a primary container

    public static function validatePrimaryContainerFieldValue(mixed $field_value): bool
    {

        // Column of any data-type in MySQL can be defined as primary.
        return true;
    }


    // Gets maximum value that is being stored in the given container

    public function getMaxValueByContainer(string $container_name): string|int
    {

        return $this->getMaxValueByColumn($container_name);
    }


    //

    public function createFromContainerGroupData(array $container_group_data): int
    {

        [
            'keys' => $all_data_keys,
            'containers' => $containers,
            'data_flattened' => $data_flattened,
        ] = $container_group_data;

        $query_statement = "INSERT INTO `" . $this->table_name . "`"
            . " " . $this->formatFieldListWithBrackets($containers)
            . " VALUES ";
        $values_part = '(' . implode(',', array_fill(0, count($containers), '?')) . '),';
        $values_str = str_repeat($values_part, count($all_data_keys));
        $query_statement .= (rtrim($values_str, ',') . ';');

        try {

            $create_count = $this->server->statement($query_statement, $data_flattened);

        } catch (\mysqli_sql_exception $exception) {

            throw new CreateEntryException(
                $exception->getMessage(),
                code: $exception->getCode(),
                previous: $exception
            );
        }

        return $create_count;
    }


    //

    public function byConditionObject(Condition|ConditionGroup $condition_object): iterable
    {

        $this->containers->containersExist($condition_object->getKeywords(), throw: true);
        $abbreviation = $this->getAbbreviation();

        $basic_sql_query_builder = (new BasicSqlQueryBuilder($this->server))
            ->select(BasicSqlQueryBuilder::ALL_SYMBOL)
            ->from($this->table_name, $abbreviation)
            ->whereCondition($condition_object, fallback_condition_data: [
                'parameterize' => true,
            ]);

        $result = $this->server->statement(
            $basic_sql_query_builder->__toString(),
            $condition_object->getValues()
        );

        return $result;
    }


    //

    public function byCondition(Condition $condition): iterable
    {

        return $this->byConditionObject($condition);
    }


    //

    public function getContainersByCondition(array $container_list, Condition $condition): iterable
    {

        return $this->getColumnsBy($container_list, $condition->keyword, $condition->value);
    }


    //

    public function deleteBy(Condition $condition): array|int
    {

        return $this->deleteBySingleColumn($condition->keyword, $condition->value);
    }


    //

    public function deleteEntry(string $container_name, string|int|float $field_value): string|int
    {

        $this->containers->assertUniqueContainer($container_name);

        return $this->deleteBySingleColumn($container_name, $field_value);
    }
}

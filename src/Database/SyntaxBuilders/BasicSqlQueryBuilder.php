<?php

declare(strict_types=1);

namespace LWP\Database\SyntaxBuilders;

use LWP\Common\Enums\NamedOperatorsEnum;
use LWP\Common\Exceptions\NotFoundException;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Exceptions\UnsupportedException;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;
use LWP\Common\Enums\StandardOrderEnum;
use LWP\Common\Enums\ExtendedOrderEnum;
use LWP\Common\Exceptions\EmptyStringException;
use LWP\Database\Table;
use LWP\Database\Server as SqlServer;
use LWP\Database\SyntaxBuilders\Enums\BasicSqlQueryTypesEnum;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\flatten;

class BasicSqlQueryBuilder implements \Stringable
{
    public const WITH_KEYWORD = 'WITH';
    public const SELECT_KEYWORD = 'SELECT';
    public const FROM_KEYWORD = 'FROM';
    public const REGULAR_JOIN_KEYWORD = 'JOIN';
    public const WHERE_KEYWORD = 'WHERE';
    public const ORDER_BY_KEYWORD = 'ORDER BY';
    public const GROUP_BY_KEYWORD = 'GROUP BY';
    public const LIMIT_KEYWORD = 'LIMIT';
    public const ALL_SYMBOL = '*';

    protected string $with;
    protected array $select = [];
    protected array $from = [];
    protected array $join_parts = [];
    protected array $where_parts = [];
    protected array $order_by_parts = [];
    protected array $group_by_parts = [];
    protected ?int $row_count;
    protected ?int $offset;
    protected string $count_function_parameter = self::ALL_SYMBOL;
    // Last query big parts data.
    protected array $last_query_parts = [];
    // Parameterized values
    protected array $params = [];


    public function __construct(
        public readonly SqlServer $server,
    ) {

    }


    //

    public function __toString(): string
    {

        return $this->getFullQueryString();
    }


    //

    public function setCountFunctionParameter(string $parameter): void
    {

        $this->count_functiom_parameter = $parameter;
    }


    //

    public function getCountFunctionParameter(): string
    {

        return $this->count_function_parameter;
    }


    //

    public function getParams(): array
    {

        return $this->params;
    }


    // Adds a single WITH (Common Table Expressions) expression as string.

    public function with(string $with_expression): self
    {

        $this->with = $with_expression;

        return $this;
    }


    //

    public function getWithString(): string
    {

        if (!isset($this->with)) {
            return '';
        }

        return (self::WITH_KEYWORD . ' ' . $this->with);
    }


    // Adds one or multiple select expressions.

    public function select(string|array $expression_data, ?string $alias_name = null): self
    {

        if (is_string($expression_data)) {

            if (!$expression_data) {
                throw new EmptyStringException("Parameter #1 \$expression_data cannot be empty.");
            }

            $expression = [
                'expression' => $expression_data,
            ];

            if ($alias_name) {
                $expression['alias'] = $alias_name;
            }

            $this->select[] = $expression;

            // Array
        } elseif ($expression_data) {

            foreach ($expression_data as $metadata) {

                if (is_array($metadata)) {

                    if ($metadata) {

                        // Route back to perform type checking and basic validation.
                        $this->select(
                            array_shift($metadata),
                            (($metadata)
                                ? array_shift($metadata)
                                : null)
                        );
                    }

                } else {

                    throw new UnsupportedException(sprintf(
                        "Unsupported element type \"%s\"",
                        gettype($metadata)
                    ));
                }
            }
        }

        return $this;
    }


    // Adds a single select expression as column name.

    public function selectColumn(string $column_name, string|bool $table_reference = false, ?string $alias_name = null): self
    {

        $this->select[] = self::parseFieldString($column_name, $table_reference, $alias_name);

        return $this;
    }


    //

    public function selectColumns(array $column_names, string|bool $table_reference = false): self
    {

        foreach ($column_names as $elem) {

            if (is_string($elem)) {

                $this->selectColumn($elem, $table_reference);

            } elseif (is_array($elem)) {

                if ($elem_size = count($elem)) {

                    if ($elem_size === 1) {

                        $this->selectColumn($elem[array_key_first($elem)], $table_reference);

                    } elseif ($elem_size === 2) {

                        $parts = array_combine(['column', 'alias'], $elem);

                        if ($table_reference) {
                            $parts['table'] = $table_reference;
                        }

                        $this->select[] = $parts;

                    } elseif ($elem_size === 3) {

                        $this->select[] = array_combine(['column', 'alias', 'table'], $elem);

                    } else {

                        throw new \RuntimeException("Array must not contain more than 3 elements.");
                    }
                }

            } else {

                throw new UnsupportedException(sprintf("Unsupported element type \"%s\".", gettype($metadata)));
            }
        }

        return $this;
    }


    //

    public function selectFromMetadata(array|\Traversable $metadata): self
    {

        if ($metadata) {

            foreach ($metadata as $data) {

                if (!isset($data['expression']) && !isset($data['column'])) {
                    throw new \Exception("At least expression or column name should be in the metadata.");
                }

                $result = [];

                if (isset($data['expression'])) {

                    $expression = $data['expression'];

                    if (
                        !is_int($expression)
                        && !is_float($expression)
                        && (!is_string($expression) || !$expression)
                    ) {
                        throw new \Exception(sprintf("Expression must be a non-empty string, integer, or float, got %s.", gettype($expression)));
                    }

                    #todo: sanitize.
                    $result['expression'] = $data['expression'];
                }

                if (isset($data['column'])) {
                    #todo: sanitize.
                    $result['column'] = $data['column'];
                }

                if (isset($data['table_reference'])) {
                    #todo: sanitize.
                    $result['table'] = $data['table_reference'];
                }

                if (isset($data['alias_name'])) {
                    #todo: sanitize.
                    $result['alias'] = $data['alias_name'];
                }

                $this->select[] = $result;
            }
        }

        return $this;
    }


    //

    public function getSelectParts(): array
    {

        return $this->select;
    }


    //

    public static function parseFieldString(string $column_name, bool|string $prefix = false, ?string $alias = null): array
    {

        $result = [
            'column' => $column_name,
        ];

        if ($prefix === true) {

            $pos = strrpos($column_name, '.');

            if ($pos !== false) {

                $result['table'] = substr($column_name, 0, $pos);
                $result['column'] = substr($column_name, ($pos + 1));

            } else {

                $result['column'] = $column_name;
            }

        } elseif ($prefix) {

            $result['table'] = $prefix;
        }

        if ($alias) {

            $result['alias'] = $alias;
        }

        return $result;
    }


    //

    public function getSelectString(
        BasicSqlQueryTypesEnum $query_type = BasicSqlQueryTypesEnum::FULL,
        bool $format = false
    ): string {

        $result = (self::SELECT_KEYWORD . ((!$format)
            ? ' '
            : "\n\t"));

        // Full query.
        if ($query_type === BasicSqlQueryTypesEnum::FULL) {

            if (!$this->select) {
                throw new NotFoundException(
                    "There are no select expressions."
                );
            }

            $parts = [];

            foreach ($this->select as $data) {

                $column_str = '';

                if (isset($data['expression'])) {

                    $column_str .= $data['expression'];

                } else {

                    if (!empty($data['table'])) {
                        $column_str .= (SqlServer::formatAsQuotedIdentifier($data['table']) . '.');
                    }

                    // Don't enclose the "all" symbol.
                    $column_str .= ($data['column'] !== self::ALL_SYMBOL)
                        ? SqlServer::formatAsQuotedIdentifier($data['column'])
                        : $data['column'];
                }

                if (!empty($data['alias'])) {
                    $column_str .= (' ' . SqlServer::formatAsQuotedIdentifier($data['alias']));
                }

                $parts[] = $column_str;
            }

            $result .= implode(
                ((!$format)
                    ? ','
                    : ",\n\t"),
                $parts
            );

            // Count query.
        } elseif ($query_type === BasicSqlQueryTypesEnum::COUNT) {

            $result .= ("COUNT(" . $this->count_function_parameter . ") `count`");
        }

        return $result;
    }


    //

    public function from(string|array $arg_tables, ?string $default_alias = null): self
    {

        $string_handler = function (string $table_name, ?string $default_alias = null): array {

            $result = [
                'table' => $table_name,
            ];

            if ($default_alias) {
                $result['alias'] = $default_alias;
            }

            return $result;
        };

        if (is_string($arg_tables)) {

            $this->from[] = $string_handler($arg_tables, $default_alias);

        } else {

            foreach ($arg_tables as $elem) {

                if (is_string($elem)) {

                    $this->from[] = $string_handler($elem);

                } elseif (is_array($elem)) {

                    if ($elem_size = count($elem)) {

                        if ($elem_size === 1) {
                            $this->from[] = $string_handler($elem[array_key_first($elem)]);
                        } elseif ($elem_size === 2) {
                            $this->from[] = $string_handler($elem[array_key_first($elem)], $elem[array_key_last($elem)]);
                        } else {
                            throw new \Exception("Array must not contain more than 2 elements.");
                        }
                    }

                } else {

                    throw new \Exception("Unsupported element type.");
                }
            }
        }

        return $this;
    }


    //

    public function getFromString(bool $format = false): string
    {

        if (!$this->from) {
            throw new NotFoundException("From statement is empty.");
        }

        $parts = [];

        foreach ($this->from as $data) {

            $part = SqlServer::formatAsQuotedIdentifier($data['table']);

            if (!empty($data['alias'])) {
                $part .= (' ' . SqlServer::formatAsQuotedIdentifier($data['alias']));
            }

            $parts[] = $part;
        }

        $result = (self::FROM_KEYWORD . ((!$format)
            ? ' '
            : "\n\t"));

        $result .= implode(
            ((!$format)
                ? ', '
                : ",\n\t"),
            $parts
        );

        return $result;
    }


    //

    public function join(string $join_str, array $params = []): self
    {

        $this->join_parts[] = [
            'part' => $join_str,
            'params' => $params
        ];

        return $this;
    }


    //

    public function joinFromGenerator(\Generator $join_part_generator): self
    {

        $this->join_parts[] = $join_part_generator;

        return $this;
    }


    //

    public function getJoin(bool $format = false): array
    {

        if (!$this->join_parts) {
            return ['', []];
        }

        $string = '';
        $i = 0;

        $append_to_string = function (
            string $join_string
        ) use (
            &$string,
            &$i,
            $format,
        ): void {

            $string .= (
                (!$format)
                ? (($i)
                    ? ' '
                    : '')
                : (($i)
                    ? "\n"
                    : '')
            ) . $join_string;

            $i++;
        };

        $join_parts = $this->join_parts;
        $all_params = [];

        foreach ($join_parts as $join_data) {

            if (is_array($join_data)) {

                $append_to_string($join_data['part']);
                $all_params = [...$all_params, ...$join_data['params']];

            } elseif ($join_data instanceof \Generator) {

                foreach ($join_data as $data) {

                    $join_string = (is_string($data)) ? $data : $data['part'] ?? null;

                    if ($join_string) {

                        $append_to_string($join_string);

                        if (is_array($data) && isset($data['params'])) {
                            $all_params = [...$all_params, ...$data['params']];
                        }
                    }
                }
            }
        }

        return [$string, $all_params];
    }


    //

    public function where(
        string $where_part,
        NamedOperatorsEnum $named_operator = NamedOperatorsEnum::AND,
        int $priority = 0,
        array $params = []
    ): self {

        $this->where_parts[] = [
            'part' => $where_part,
            'operator' => $named_operator,
            'priority' => $priority,
            'params' => $params
        ];

        return $this;
    }


    //

    public function whereCondition(
        Condition|ConditionGroup $condition_object,
        NamedOperatorsEnum $named_operator = NamedOperatorsEnum::AND,
        // Fallback stringification data that can be used for all conditions, when access to them is limited.
        array $fallback_condition_data = [],
        int $priority = 0,
        array $params = []
    ): self {

        $is_single_condition = ($condition_object instanceof Condition);

        // Prevent adding empty group conditions.
        if ((!$is_single_condition && $condition_object->count()) || $is_single_condition) {

            if ($is_single_condition || !$condition_object->hasStringifyReplacer()) {

                $stringify_replacer = (!$fallback_condition_data)
                    ? $this->stringifyCondition(...)
                    : fn (Condition $condition) => $this->stringifyCondition($condition, $fallback_condition_data);

                $condition_object->setStringifyReplacer($stringify_replacer);
            }

            $this->where_parts[] = [
                'part' => $condition_object,
                'operator' => $named_operator,
                'priority' => $priority,
                'params' => $params
            ];
        }

        return $this;
    }


    //

    public function getWhereParts(): array
    {

        return $this->where_parts;
    }


    //

    public function getWhere(bool $format = false): array
    {

        if (!$this->where_parts) {
            return ['', []];
        }

        $string = (self::WHERE_KEYWORD . ((!$format)
            ? ' '
            : "\n\t"));

        // Don't modify the global property.
        $where_parts = $this->where_parts;

        usort($where_parts, fn (array $a, array $b): int => $a['priority'] <=> $b['priority']);

        $all_params = [];

        foreach ($where_parts as $index => $where_data) {

            [
                'part' => $part,
                'operator' => $named_operator,
                'params' => $params
            ] = $where_data;

            $all_params = [...$all_params, ...$params];

            if (!$part) {
                throw new \ValueError("Part string cannot be empty");
            }

            if ($index) {

                if ($format) {
                    $string .= "\n\t";
                }

                $string .= (' ' . $named_operator->name . ' ');
            }

            $is_condition_group = ($part instanceof ConditionGroup);

            if ($is_condition_group) {
                $string .= '(';
            }

            $string .= (!is_string($part))
                ? $part->__toString()
                : $part;

            if ($is_condition_group) {
                $string .= ')';
            }
        }

        return [$string, $all_params];
    }


    //

    public function orderBy(
        string|array $order_by_part,
        null|StandardOrderEnum|ExtendedOrderEnum $order = null,
        int $priority = 0,
        array $params = []
    ): self {

        $this->order_by_parts[] = [
            'part' => $order_by_part,
            'order' => $order,
            'priority' => $priority,
            'params' => $params
        ];

        return $this;
    }


    //

    public function getOrderByParts(): array
    {

        return $this->order_by_parts;
    }


    //

    public function getOrderBy(bool $format = false): array
    {

        if (!$this->order_by_parts) {
            return ['', []];
        }

        $string = (self::ORDER_BY_KEYWORD . ((!$format)
            ? ' '
            : "\n\t"));

        // Don't modify the class property.
        $order_by_parts = $this->order_by_parts;

        usort($order_by_parts, fn (array $a, array $b): int => $a['priority'] <=> $b['priority']);

        $all_params = [];

        foreach ($order_by_parts as $index => $order_by_part) {

            if ($index) {

                $string .= ((!$format)
                    ? ','
                    : ",\n\t");
            }

            [
                'part' => $part,
                'order' => $order,
                'params' => $params
            ] = $order_by_part;

            $all_params = [...$all_params, ...$params];

            if (!is_array($part)) {
                $string .= $part;
                // Column metadata
            } else {
                $string .= self::stringifyColumnMetadata($part);
            }

            if ($order) {
                $string .= (' ' . $order->name);
            }
        }

        return [$string, $all_params];
    }


    //

    public function groupBy(
        string|array $group_by_part,
        int $priority = 0,
        array $params = []
    ): self {

        $this->group_by_parts[] = [
            'part' => $group_by_part,
            'priority' => $priority,
            'params' => $params
        ];

        return $this;
    }


    //

    public function getGroupByParts(): array
    {

        return $this->group_by_parts;
    }


    //

    public function getGroupBy(bool $format = false): array
    {

        if (!$this->group_by_parts) {
            return ['', []];
        }

        $string = (self::GROUP_BY_KEYWORD . ((!$format)
            ? ' '
            : "\n\t"));

        // Don't modify the class property.
        $group_by_parts = $this->group_by_parts;

        usort($group_by_parts, fn (array $a, array $b): int => $a['priority'] <=> $b['priority']);

        $all_params = [];

        foreach ($group_by_parts as $index => $group_by_part) {

            if ($index) {

                $string .= ((!$format)
                    ? ','
                    : ",\n\t");
            }

            [
                'part' => $part,
                'params' => $params
            ] = $group_by_part;

            if (!is_array($part)) {
                $string .= $part;
                // Column metadata
            } else {
                $string .= self::stringifyColumnMetadata($part);
            }

            $all_params = [...$all_params, ...$params];
        }

        return [$string, $all_params];
    }


    //
    // @param $offset - for security reasons it cannot be a string, though technically nth based strings are valid, eg. "n-1".

    public function limit(int $row_count, ?int $offset = null): self
    {

        $this->row_count = $row_count;
        $this->offset = $offset;

        return $this;
    }


    //

    public function getLimitString(): string
    {

        if (!isset($this->row_count)) {
            return '';
        }

        $result = (self::LIMIT_KEYWORD . ' ');

        // Allow zero integers.
        if ($this->offset !== null) {
            $result .= ($this->offset . ',');
        }

        $result .= $this->row_count;

        return $result;
    }


    //

    public function getFull(
        BasicSqlQueryTypesEnum $query_type = BasicSqlQueryTypesEnum::FULL,
        bool $reuse_last_parts = true,
        /* When reused, basic formatting will be inherited from the query that is being reused. */
        bool $format = false
    ): array {

        if ($reuse_last_parts && $this->last_query_parts) {

            // Parts for given type query exist.
            if (isset($this->last_query_parts[$query_type->name])) {

                ['parts' => $parts, 'params' => $params] = $this->last_query_parts[$query_type->name];
                return [self::buildFromBigParts($parts, $format), $params];

                // Build "count" type query from "full" type parts.
            } elseif (
                $query_type === BasicSqlQueryTypesEnum::COUNT
                && isset($this->last_query_parts[BasicSqlQueryTypesEnum::FULL->name])
            ) {

                ['parts' => $parts, 'params' => $params] = $this->last_query_parts[BasicSqlQueryTypesEnum::FULL->name];
                $parts['select'] = $this->getSelectString($query_type, $format);
                unset($parts['limit'], $parts['order_by'], $params['order_by'], $params['group_by']);

                // Build "full" type query from "count" type parts.
            } elseif (
                $query_type === BasicSqlQueryTypesEnum::FULL
                && isset($this->last_query_parts[BasicSqlQueryTypesEnum::COUNT->name])
            ) {

                ['parts' => $parts, 'params' => $params] = $this->last_query_parts[BasicSqlQueryTypesEnum::COUNT->name];
                $parts['select'] = $this->getSelectString($query_type, $format);

                [$group_by_string, $group_by_params] = $this->getGroupBy($format);

                if ($group_by_string) {
                    $parts['group_by'] = $group_by_string;
                    $params['group_by'] = $group_by_params;
                }

                [$order_by_string, $order_by_params] = $this->getOrderBy($format);

                if ($order_by_string) {
                    $parts['order_by'] = $order_by_string;
                    $params['order_by'] = $order_by_params;
                }

                if ($limit_string = $this->getLimitString()) {
                    $parts['limit'] = $limit_string;
                }
            }

            if (isset($parts)) {

                $params = flatten($params);

                $this->last_query_parts[$query_type->name] = [
                    'parts' => $parts,
                    'params' => $params
                ];

                return [
                    self::buildFromBigParts($parts, $format),
                    $params
                ];
            }
        }

        $parts = [];
        $all_params = [];
        $grouped_params = [];

        if ($with_string = $this->getWithString($format)) {
            $parts['with'] = $with_string;
        }

        // Thus far this is the only method that accepts query type.
        $parts['select'] = $this->getSelectString($query_type, $format);
        $parts['from'] = $this->getFromString($format);

        [$join_string, $params] = $this->getJoin($format);

        if ($join_string) {
            $parts['join'] = $join_string;
            $all_params = [...$all_params, ...$params];
            $grouped_params['join'] = $params;
        }

        [$where_string, $params] = $this->getWhere($format);

        if ($where_string) {
            $parts['where'] = $where_string;
            $all_params = [...$all_params, ...$params];
            $grouped_params['where'] = $params;
        }

        [$group_by_string, $params] = $this->getGroupBy($format);

        if ($group_by_string && $query_type !== BasicSqlQueryTypesEnum::COUNT) {
            $parts['group_by'] = $group_by_string;
            $all_params = [...$all_params, ...$params];
            $grouped_params['group_by'] = $params;
        }

        if ($query_type !== BasicSqlQueryTypesEnum::COUNT) {

            [$order_by_string, $params] = $this->getOrderBy($format);

            if ($order_by_string) {
                $parts['order_by'] = $order_by_string;
                $all_params = [...$all_params, ...$params];
                $grouped_params['order_by'] = $params;
            }
        }

        if (
            $query_type !== BasicSqlQueryTypesEnum::COUNT
            && ($limit_string = $this->getLimitString())
        ) {
            $parts['limit'] = $limit_string;
        }

        $this->last_query_parts[$query_type->name] = [
            'parts' => $parts,
            'params' => $grouped_params
        ];

        return [
            self::buildFromBigParts($parts, $format),
            $all_params
        ];
    }


    //

    public function getFullQueryString(
        BasicSqlQueryTypesEnum $query_type = BasicSqlQueryTypesEnum::FULL,
        bool $reuse_last_parts = true,
        /* When reused, basic formatting will be inherited from the query that is being reused. */
        bool $format = false
    ): string {

        [$string] = $this->getFull($query_type, $reuse_last_parts, $format);

        return $string;
    }


    //

    public function getNoLimitCountFull(bool $reuse_last_parts = true, bool $format = false): array
    {

        return $this->getFull(BasicSqlQueryTypesEnum::COUNT, $reuse_last_parts, $format);
    }


    //

    public function getNoLimitCountQueryString(bool $reuse_last_parts = true, bool $format = false): string
    {

        return $this->getFullQueryString(
            BasicSqlQueryTypesEnum::COUNT,
            $reuse_last_parts,
            $format
        );
    }


    //

    public static function formatFields(array $fields, bool $first_dot_as_abbr_mark = false): string
    {

        $fields = array_map(
            fn (string $item) => SqlServer::formatAsQuotedIdentifier($item, $first_dot_as_abbr_mark),
            $fields,
        );

        return implode(', ', $fields);
    }


    //

    public function stringifyCondition(Condition $condition, array $fallback_data = []): string
    {

        $condition_data = ($condition->data ?: []);
        $condition_data = array_merge($fallback_data, $condition_data);
        $result = '';

        if (isset($condition_data['table'])) {

            if ($condition_data['table'] instanceof Table) {
                $abbreviation = $condition_data['table']->getAbbreviation();
            } elseif (is_string($condition_data['table'])) {
                $abbreviation = $condition_data['table'];
            }
        }

        if (!isset($abbreviation) && isset($condition_data['abbreviation']) && $condition_data['abbreviation']) {
            $abbreviation = $condition_data['abbreviation'];
        }

        if (isset($abbreviation)) {
            $result .= (SqlServer::formatAsQuotedIdentifier($abbreviation) . '.');
        }

        $result .= SqlServer::formatAsQuotedIdentifier($condition->keyword);

        if ($condition->value !== null) {

            $parameterize = (isset($condition_data['parameterize']) && $condition_data['parameterize'] === true);

            switch ($condition->control_operator) {
                case ConditionComparisonOperatorsEnum::CONTAINS:
                    $result .= sprintf(
                        ' LIKE "%%%s%%"',
                        $parameterize ? '?' : $this->server->escape((string)$condition->value)
                    );
                    break;
                case ConditionComparisonOperatorsEnum::STARTS_WITH:
                    $result .= sprintf(
                        ' LIKE "%s%%"',
                        $parameterize ? '?' : $this->server->escape((string)$condition->value)
                    );
                    break;
                case ConditionComparisonOperatorsEnum::ENDS_WITH:
                    $result .= sprintf(
                        ' LIKE "%%%s"',
                        $parameterize ? '?' : $this->server->escape((string)$condition->value)
                    );
                    break;
                default:
                    $result .= sprintf(
                        ' %s %s',
                        $condition->control_operator->value,
                        $parameterize ? '?' : $this->server->formatVariable($condition->value)
                    );
                    break;
            }

        } else {

            if ($condition->control_operator === ConditionComparisonOperatorsEnum::EQUAL_TO) {
                $result .= ' IS';
            } elseif ($condition->control_operator === ConditionComparisonOperatorsEnum::NOT_EQUAL_TO) {
                $result .= ' IS NOT';
            } else {
                throw new UnsupportedException(sprintf(
                    "Cannot use named operator \"%s\" with null value",
                    $condition->control_operator->value
                ));
            }

            $result .= ' NULL';
        }

        return $result;
    }


    //

    public function addBasicRecursiveCte(int $iterator_count, string $column_name = 'rcte_i'): self
    {

        if ($column_name === '') {
            throw new EmptyStringException(sprintf(
                "String parameter %s must not be empty",
                $column_name
            ));
        }

        $this->with = "RECURSIVE `rcte` (`$column_name`) AS ("
            . "SELECT 0"
            . " UNION ALL"
            . " SELECT `$column_name` + 1 FROM `rcte` WHERE `$column_name` < $iterator_count"
            . ")";

        return $this;
    }


    //

    public function addBasicRecursiveCteComponents(int $count, string $column_name = 'rcte_i', string $alias_name = 'rcte_id'): self
    {

        $this->selectColumn('rcte.rcte_i', table_reference: true, alias_name: $alias_name)
            // Using "JOIN", because a straight "FROM" might not work with other joins in the query
            ->join("JOIN `rcte` ON 1")
            ->addBasicRecursiveCte($count, $column_name);

        return $this;
    }


    //

    protected static function buildFromBigParts(array $parts, bool $format = false): string
    {

        // When basic formatting is enabled, will separate statements with a new line.
        $separator = (!$format)
            ? ' '
            : "\n";

        return implode($separator, $parts);
    }


    //

    public static function stringifyColumnMetadata(array $metadata): string
    {

        return (SqlServer::formatAsQuotedIdentifier($metadata['table_reference'])
            . "." . SqlServer::formatAsQuotedIdentifier($metadata['column']));
    }
}

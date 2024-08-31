<?php

declare(strict_types=1);

namespace LWP\Common\Array;

use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;
use LWP\Common\Enums\AssortmentEnum;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;

class ArrayObjectIndexTree
{
    private int $index_id = 0;
    private array $index_tree = [
        'data_indexes' => [],
        'keys' => [],
        'values' => [],
    ];


    public function __construct(
        private array $non_indexable_keys = [],
        public readonly bool $two_level_support = false,
    ) {

        $this->setNonIndexableKeys($non_indexable_keys);
    }


    // Gets current index number.

    public function getIndexId(): int
    {

        return $this->index_id;
    }


    // Gets the entire index tree.

    public function getTree(): array
    {

        return $this->index_tree;
    }


    // Gets all registered non-indexable keys.

    public function getNonIndexableKeys(): array
    {

        return $this->non_indexable_keys;
    }


    // Sets a bunch of non-indexable keys.

    public function setNonIndexableKeys(array $non_indexable_keys): void
    {

        $this->non_indexable_keys = $non_indexable_keys;
    }


    // Adds a new key value to non-indexable keys.

    public function addToNonIndexableKeys(string $value): void
    {

        if (!$this->isIndexableKey($value)) {
            $this->non_indexable_keys[] = $value;
        }
    }


    // Tells if provided key can be indexed.

    public function isIndexableKey(string $key): bool
    {

        return !in_array($key, $this->non_indexable_keys);
    }


    // Tells if provided data index key exists.

    public function dataIndexExists(int|string $data_index): bool
    {

        return isset($this->index_tree['data_indexes'][$data_index]);
    }


    // Prepares special values for indexing.

    public static function prepareValue(mixed &$value): void
    {

        if ($value === false) {
            $value = '__false__';
        } elseif ($value === true) {
            $value = '__true__';
        } elseif ($value === null) {
            $value = '__null__';
        }
    }


    // Writes data into the index tree

    private function write(int|string $index, array|object $data)
    {

        $indexed_count = 0;

        foreach ($data as $key => $val) {

            $key = (string)$key;

            if ($this->isIndexableKey($key)) {

                self::prepareValue($val);

                if (is_string($val) || is_numeric($val)) {

                    $this->writePair($index, $key, $val);
                    $indexed_count++;

                } elseif ($this->two_level_support && is_array($val)) {

                    foreach ($val as $val_inside) {

                        if (is_string($val_inside) || is_numeric($val_inside)) {
                            $this->writePair($index, $key, $val_inside);
                        }
                    }

                    $indexed_count++;
                }
            }
        }

        return $indexed_count;
    }


    // Writes key and value pair into the index tree

    private function writePair(int|string $index, int|string $key, int|float|string $value): void
    {

        self::prepareValue($value);
        $this->addKey($index, $key, $value);
        $this->addValue($index, $key, $value);
    }


    // Adds data to the index tree.
    // Preferred index name can be chosen.

    public function add(array|object $data, int|string $index_name = null): int|string
    {

        $has_index_name = ($index_name !== null);

        if ($has_index_name && $this->dataIndexExists($index_name)) {
            throw new \Exception(sprintf(
                "Index name \"%s\" already exists: please choose a different index name",
                $index_name
            ));
        }

        $keys = &$this->index_tree['keys'];
        $values = &$this->index_tree['values'];

        if (!$has_index_name) {

            while ($this->dataIndexExists((string)$this->index_id)) {
                $this->index_id++;
            }
        }

        $index_id = (!$has_index_name)
            ? (string)$this->index_id
            : $index_name;

        $indexed_count = $this->write($index_id, $data);

        $this->index_tree['data_indexes'][$index_id] = $indexed_count;

        return $index_id;
    }


    // Adds additional data into a given index tree branch

    public function supplement(array|object $data, int|string $index_name = null): int
    {

        if (!$this->dataIndexExists($index_name)) {
            throw new \Exception(sprintf(
                "Index name \"%s\" was not found: please use an existing index name to supplement data",
                $index_name
            ));
        }

        $indexed_count = $this->write($index_name, $data);
        $this->index_tree['data_indexes'][$index_name] += $indexed_count;

        return $indexed_count;
    }


    // Creates a new key part.

    private function addKey(int|string $index_id, int|string $key, int|float|string $val): void
    {

        $keys = &$this->index_tree['keys'];

        if (isset($keys[$key])) {
            $keys[$key]['count']++;
        } else {
            $keys[$key]['count'] = 1;
        }

        // Preserve precision, otherwise float will be auto converted to int in the key part.
        if (is_float($val)) {
            $val = (string)$val;
        }

        $keys[$key]['values'][$val][$index_id] = $index_id;
        $keys[$key]['data_index'][$index_id][] = $val;
    }


    // Creates a new value part.

    private function addValue(int|string $index_id, int|string $key, int|float|string $val): void
    {

        $values = &$this->index_tree['values'];

        // Preserve precision, otherwise float will be auto converted to int in the key part.
        if (is_float($val)) {
            $val = (string)$val;
        }

        if (isset($values[$val])) {
            $values[$val]['count']++;
        } else {
            $values[$val]['count'] = 1;
        }

        $values[$val]['keys'][$key][$index_id] = $index_id;
        $values[$val]['data_index'][$index_id][$key] = $key;
    }


    // Removes data from a chosen data index container.

    public function removeData(int|string $index_id, array|object $data): void
    {

        foreach ($data as $key => $val) {

            $this->removePair($index_id, $key, $val);
        }
    }


    // Removes a pair from a chosen data index container.

    public function removePair(int|string $index_id, int|string $key, mixed $val): void
    {

        if ($this->isIndexableKey($key)) {

            self::prepareValue($val);

            if ($this->two_level_support || !is_array($val)) {

                $val = (array)$val;
                $removed = false;

                foreach ($val as $v) {

                    // Cannot go deeper than 2 levels.
                    if (!is_array($v) && !is_object($v)) {

                        if ($this->removeKey($index_id, $key, $v)) {

                            $this->removeValue($index_id, $key, $v);
                            $removed = true;
                        }
                    }
                }

                // Most likely hasn't been added at all due to unsupported type.
                if ($removed) {

                    $data_indexes = &$this->index_tree['data_indexes'];

                    if ($data_indexes[$index_id] === 1) {
                        unset($data_indexes[$index_id]);
                    } else {
                        $data_indexes[$index_id]--;
                    }
                }
            }
        }
    }


    // Remove value segment from the "keys" part.

    private function removeValueFromKey(int|string $index_id, int|string $key, int|float|string|bool $val): void
    {

        $keys = &$this->index_tree['keys'];
        $val = (string)$val;

        if (count($keys[$key]['values'][$val]) === 1) {
            unset($keys[$key]['values'][$val]);
        } else {
            unset($keys[$key]['values'][$val][$index_id]);
        }

        if (count($keys[$key]['data_index'][$index_id]) === 1) {
            unset($keys[$key]['data_index'][$index_id]);
        } else {
            $index = array_search($val, $keys[$key]['data_index'][$index_id]);
            unset($keys[$key]['data_index'][$index_id][$index]);
        }
    }


    // Removes index entry from the "keys" part.

    private function removeKey(int|string $index_id, int|string $key, int|float|string|bool $val): bool
    {

        $keys = &$this->index_tree['keys'];
        $removed = false;

        if (isset($keys[$key])) {

            if ($keys[$key]['count'] === 1) {

                unset($keys[$key]);
                $removed = true;

            } else {

                $this->removeValueFromKey($index_id, $key, $val);

                $keys[$key]['count']--;
                $removed = true;
            }
        }

        return $removed;
    }


    // Remove key segment from the "values" part.

    private function removeKeyFromValue(int|string $index_id, int|string $key, int|float|string|bool $val): void
    {

        $values = &$this->index_tree['values'];

        if (count($values[$val]['keys'][$key]) === 1) {
            unset($values[$val]['keys'][$key]);
        } else {
            unset($values[$val]['keys'][$key][$index_id]);
        }

        if (count($values[$val]['data_index'][$index_id]) === 1) {
            unset($values[$val]['data_index'][$index_id]);
        } else {
            unset($values[$val]['data_index'][$index_id][$key]);
        }
    }


    // Removes index entry from the "values" part.

    private function removeValue(int|string $index_id, int|string $key, int|float|string|bool $val): bool
    {

        $values = &$this->index_tree['values'];
        $val = (string)$val;
        $removed = false;

        if (isset($values[$val])) {

            if ($values[$val]['count'] == 1) {

                unset($values[$val]);
                $removed = true;

            } else {

                $this->removeKeyFromValue($index_id, $key, $val);

                $values[$val]['count']--;
                $removed = true;
            }
        }

        return $removed;
    }


    // Updates value.

    public function updateValue(int|string $index_id, int|string $key, mixed $val): bool
    {

        $data_indexes = &$this->index_tree['data_indexes'];

        // Invalid data index.
        if (!isset($data_indexes[$index_id])) {
            return false;
        }

        $keys = &$this->index_tree['keys'];

        // Provided key doesn't exist.
        if (!isset($keys[$key])) {
            return false;
        }

        $current_value = $keys[$key]['data_index'][$index_id];
        self::prepareValue($val);

        // No change to the value.
        if ($val === $current_value) {
            return false;
        }

        $current_value = (array)$current_value;

        foreach ($current_value as $value) {

            $this->removeValue($index_id, $key, $value);
            $this->removeValueFromKey($index_id, $key, $value);
        }

        $val = (array)$val;

        foreach ($val as $v) {

            $v = (string)$v;

            $this->addValue($index_id, $key, $v);
            $keys[$key]['data_index'][$index_id][] = $v;
            $keys[$key]['values'][$v][$index_id] = $index_id;
        }

        return true;
    }


    //

    public function assessCondition(Condition $condition): ?array
    {

        // Both, keyword and value, are available.
        if ($condition->control_operator instanceof ConditionComparisonOperatorsEnum) {

            return $this->assessKeyAndValueWithOperator($condition->keyword, $condition->value, $condition->control_operator);

            // Only keyword is available.
        } elseif (!($condition->keyword instanceof NoValueAttribute)) {

            return ($condition->control_operator === AssortmentEnum::INCLUDE)
                ? $this->containsKey($condition->keyword)
                : $this->doesNotContainKey($condition->keyword);

            // Only value is available.
        } else {

            return ($condition->control_operator === AssortmentEnum::INCLUDE)
                ? $this->containsValue($condition->value)
                : $this->doesNotContainValue($condition->value);
        }
    }


    //

    public function assessConditionGroup(ConditionGroup $condition_group, \Closure $callback = null): array
    {

        $indexes_generator = $condition_group->getAllConditionsHavingIndexesGenerator();

        while ($indexes_generator->valid()) {

            $condition = $indexes_generator->current();
            $assessment_indexes = $this->assessCondition($condition);

            if ($callback) {
                $assessment_indexes = $callback($condition, $assessment_indexes);
            }

            $indexes_generator->send($assessment_indexes);
        }

        return $indexes_generator->getReturn();
    }


    //

    public function assessKeyAndValueWithOperator(int|string $key, mixed $val, ConditionComparisonOperatorsEnum $comparison_operator): ?array
    {

        $keys = $this->index_tree['keys'];
        $values = $this->index_tree['values'];

        self::prepareValue($val);

        return Condition::assesComparisonOperatorMiddleware(
            $key,
            $val,
            $comparison_operator,
            function (string $key, mixed $val) use ($keys, $values, $comparison_operator): ?array {

                switch ($comparison_operator) {

                    case ConditionComparisonOperatorsEnum::EQUAL_TO:

                        // Search for matching pairs.
                        return (isset($values[$val], $values[$val]['keys'][$key]))
                            ? $values[$val]['keys'][$key]
                            : null;

                        break;

                    case ConditionComparisonOperatorsEnum::NOT_EQUAL_TO:

                        $all_data_indexes = array_keys($this->index_tree['data_indexes']);

                        #pre(array_diff_key($this->index_tree['data_indexes'], $keys[$key]['values'][$val]));

                        #pr($all_data_indexes);
                        #pre($keys[$key]['values'][$val]);

                        // Neither the key, nor value was found.
                        return (!isset($keys[$key]) || !isset($keys[$key]['values'][$val]))
                            ? $all_data_indexes
                            // Exclude data indexes that contain the pair.
                            : array_diff($all_data_indexes, $keys[$key]['values'][$val]);

                        break;

                    case ConditionComparisonOperatorsEnum::LESS_THAN:
                    case ConditionComparisonOperatorsEnum::GREATER_THAN:
                    case ConditionComparisonOperatorsEnum::LESS_THAN_OR_EQUAL_TO:
                    case ConditionComparisonOperatorsEnum::GREATER_THAN_OR_EQUAL_TO:

                        $result = [];

                        if (isset($keys[$key])) {

                            foreach ($keys[$key]['values'] as $index => $indexed_value) {

                                if (is_integer($index) && Condition::assessComparisonOperator($index, $val, $comparison_operator)) {
                                    $result += $indexed_value;
                                }
                            }
                        }

                        return $result;

                        break;

                    case ConditionComparisonOperatorsEnum::CONTAINS:
                    case ConditionComparisonOperatorsEnum::STARTS_WITH:
                    case ConditionComparisonOperatorsEnum::ENDS_WITH:

                        $result = [];

                        foreach ($keys[$key]['values'] as $index => $indexed_value) {

                            if (Condition::assessComparisonOperator($index, $val, $comparison_operator, case_sensitive: false, accent_sensitive: false)) {
                                $result += $values[$index]['keys'][$key];
                            }
                        }

                        return $result;

                        break;
                }

            },
            // With number specific operators (eg. less than, etc), treat only the second value (not they keyword) as numeric.
            treat_both_values_num: false
        );
    }


    // Checks if specific value is present.

    public function containsValue(int|string $val): ?array
    {

        return (isset($this->index_tree['values'][$val]))
            ? array_keys($this->index_tree['values'][$val]['data_index'])
            : null;
    }


    // Checks if specific key is present.

    public function containsKey(int|string $key): ?array
    {

        return (isset($this->index_tree['keys'][$key]))
            ? array_keys($this->index_tree['keys'][$key]['data_index'])
            : null;
    }


    // Checks if specific value is absent.

    public function doesNotContainValue(int|string $val): array
    {

        $all_data_indexes = array_keys($this->index_tree['data_indexes']);

        return ($data_indexes = $this->containsValue($val))
            ? array_diff($all_data_indexes, $data_indexes)
            : $all_data_indexes;
    }


    // Checks if specific key is absent.

    public function doesNotContainKey(int|string $key): array
    {

        $all_data_indexes = array_keys($this->index_tree['data_indexes']);
        $data_indexes = $this->containsKey($key);

        return ($data_indexes)
            ? array_diff($all_data_indexes, $data_indexes)
            : $all_data_indexes;
    }


    // Fetches indexes where given key contains any of the given values

    public function assessKeyValues(int|string $key, array $values, bool $group_as_unique = false): ?array
    {

        if (!$values) {
            return null;
        }

        $keys = $this->index_tree['keys'];

        if (!isset($keys[$key])) {
            return null;
        }

        $intersect = array_intersect_key($keys[$key]['values'], array_flip($values));

        if (!$intersect) {
            return null;
        }

        $indexes = [];

        foreach ($intersect as $value => $index_group) {
            if (!$group_as_unique) {
                $indexes = [...$indexes, ...$index_group];
            } else {
                if (count($index_group) > 1) {
                    throw new \Exception("Cannot group as unique, because index group contains more than one element");
                }
                $indexes[$value] = $index_group[array_key_first($index_group)];
            }
        }

        return $indexes;
    }
}

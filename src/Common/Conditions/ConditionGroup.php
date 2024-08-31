<?php

declare(strict_types=1);

namespace LWP\Common\Conditions;

use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;
use LWP\Common\Enums\NamedOperatorsEnum;
use LWP\Common\Iterators\EnhancedRecursiveIteratorIterator;
use LWP\Components\Model\BasePropertyModel;
use LWP\Components\Properties\Exceptions\PropertyValueNotAvailableException;
use LWP\Components\Properties\Exceptions\PropertyNotFoundException;
use LWP\Common\Enums\KeyValuePairEnum;

class ConditionGroup implements \Stringable, \IteratorAggregate, \Countable
{
    // Whether to print operators as symbols (eg. "&&" and "||") instead of words (eg. "AND" and "OR"). The latter is default.
    public const PRINT_OPERATORS_SYMBOLS = 1;
    // Logs positions of conditions that have actually run in reactive generator (eg. (true OR true) => 1; (false OR false AND true) => 1,2).
    public const DEBUG_MODE = 2;


    // Two-dimensional set-array, where each element contains: [0] => Condition|ConditionGroup, [1] => NamedOperatorsEnum.
    protected array $data = [];
    // Positions of executed conditions for the last run reactive generator. Works with "DEBUG_MODE" only.
    protected ?array $exec_condition_positions = null;
    // An array containing weak references to named groups.
    protected array $named_groups_map = [];


    public function __construct(
        public readonly ?string $name = null,
        // Callback to custom format condition string.
        protected ?\Closure $stringify_replacer = null,
        protected ?int $flags = null,
    ) {

        if ($name) {
            $this->named_groups_map[$name] = \WeakReference::create($this);
        }
    }


    // Gets the string representation of the condition group.

    public function __toString(): string
    {

        return $this->stringify(suppress_replacer: false);
    }


    // Stringifies entire condition group including nested condition groups.

    public function stringify(bool $suppress_replacer = false): string
    {

        $result = '';

        foreach ($this->data as $index => $fixed_array) {

            [$condition_object, $named_operator] = $fixed_array;
            unset($fixed_array);

            $named_operator_name = $named_operator->name;

            if ($print_operators_symbols = ($this->flags & self::PRINT_OPERATORS_SYMBOLS)) {

                $named_operator_name = match ($named_operator_name) {
                    NamedOperatorsEnum::AND->name => '&&',
                    NamedOperatorsEnum::OR->name => '||',
                };
            }

            if ($index) {
                $result .= (' ' . $named_operator_name . ' ');
            }

            if ($is_group = ($condition_object instanceof ConditionGroup)) {

                if ($print_operators_symbols) {

                    $flags = $condition_object->getFlags();

                    if ($flags === null) {
                        $condition_object->setFlags(self::PRINT_OPERATORS_SYMBOLS);
                    } elseif (!($flags & self::PRINT_OPERATORS_SYMBOLS)) {
                        $condition_object->setFlags($flags | self::PRINT_OPERATORS_SYMBOLS);
                    }
                }

                $result .= '(';
            }

            if (!$suppress_replacer && $this->stringify_replacer && (!$is_group || !$condition_object->hasStringifyReplacer())) {
                $condition_object->setStringifyReplacer($this->stringify_replacer);
            }

            $result .= $condition_object->stringify($suppress_replacer);

            if ($is_group) {
                $result .= ')';
            }
        }

        return $result;
    }


    // Getter for the named groups map.

    public function getNamedGroupsMap(): array
    {

        return $this->named_groups_map;
    }


    // Searches for a given named group and returns its object instance.

    public function getInnerGroupByName(string $name): ?self
    {

        if (!isset($this->named_groups_map[$name])) {
            return null;
        }

        // Gets object from a weak reference.
        return $this->named_groups_map[$name]->get();
    }


    // Gets the data array containing fixed array elements.

    public function getData(): array
    {

        return $this->data;
    }


    // Sets flags.

    public function setFlags(int $flags): void
    {

        $this->flags = $flags;
    }


    // Gets flags.

    public function getFlags(): ?int
    {

        return $this->flags;
    }


    // Sets stringify callback function.

    public function setStringifyReplacer(\Closure $stringify_replacer): void
    {

        $this->stringify_replacer = $stringify_replacer;
    }


    // Unsets stringify callback function.

    public function unsetStringifyReplacer(bool $deep = true): void
    {

        $this->stringify_replacer = null;

        if ($deep) {
            foreach ($this->data as $index => $fixed_array) {
                [$condition_object] = $fixed_array;
                $condition_object->unsetStringifyReplacer();
            }
        }
    }


    // Returns stringify callback function

    public function getStringifyReplacer(): ?\Closure
    {

        return $this->stringify_replacer;
    }


    // Tells if a stringify callback function is set.

    public function hasStringifyReplacer(): bool
    {

        return !!$this->stringify_replacer;
    }


    // Counts elements in the data structure.

    public function count(): int
    {

        return count($this->data);
    }


    // When debug mode is used, returns an array containing positions of executed conditions for the last run reactive generator.

    public function getExecConditionPositions(): ?array
    {

        return $this->exec_condition_positions;
    }


    // Adds a new condition or condition group with adjacent named operator.

    public function add(self|Condition $condition_object, NamedOperatorsEnum $named_operator = NamedOperatorsEnum::AND)
    {

        if ($condition_object instanceof self) {

            $named_groups_map = $condition_object->getNamedGroupsMap();

            if ($this->name && in_array($this->name, $named_groups_map)) {
                throw new \Exception("Group named {$this->name} already exists.");
            }

            if ($intersect = array_intersect_key($this->named_groups_map, $named_groups_map)) {
                throw new \Exception("Duplicate group names.");
            }

            $this->named_groups_map = array_merge($this->named_groups_map, $named_groups_map);
        }

        $fixed_array = new \SplFixedArray(2);
        $fixed_array[0] = $condition_object;
        $fixed_array[1] = $named_operator;

        $this->data[] = $fixed_array;
    }


    // Gets the custom data structure recursive iterator.

    public function getIterator(): \RecursiveIterator
    {

        return new ConditionGroupIterator($this->data);
    }


    // Gets the reactive generator.
    /* It is a bidirectional generator, which yield a condition and expects to be sent back information telling whether that condition is true or false. Based on the latter it calculates the next condition to be yielded. */

    public function getReactiveGenerator(): \Generator
    {

        $recursive_iterator = new EnhancedRecursiveIteratorIterator(
            $this->getIterator(),
            // Group first order. This is essential to the entire architecture of this method.
            mode: \RecursiveIteratorIterator::SELF_FIRST
        );

        $result = null;
        // Real preceeding named operator. Remember, for first in depth items the named operator is ignored.
        $preceeding_named_operator = null;
        // Number of the depth that is locked.
        $lock = false;

        if ($is_debug_mode = ($this->flags & self::DEBUG_MODE)) {

            $this->exec_condition_positions = [];
            $condition_position = 0;
        }

        foreach ($recursive_iterator as $key => $fixed_array) {

            if ($is_debug_mode && ($fixed_array[0] instanceof Condition)) {
                $condition_position++;
            }

            $depth = $recursive_iterator->getDepth();

            // Depth is locked.
            if ($lock !== false) {

                if ($depth > $lock) {
                    continue;
                } else {
                    $lock = false;
                }
            }

            [$condition_object, $named_operator] = $fixed_array;

            if (!$recursive_iterator->isFirstInGroup()) {
                $preceeding_named_operator = $named_operator;
            }

            // Core aspect: if acting result is true and the next condition object is preceeded by "OR" named operator, everything that goes next in current group can be ignored.
            if ($result === true && $preceeding_named_operator === NamedOperatorsEnum::OR) {

                // When root group, just exit, because everything else can be ignored.
                if ($depth === 0) {
                    break;
                } else {
                    $lock = ($depth - 1);
                }

                continue;
            }

            // Core aspect: if acting result is false and the next condition object is preceeded by "AND" named operator, this object (either condition or condition group) can be ignored.
            if ($result === false && $preceeding_named_operator === NamedOperatorsEnum::AND) {

                $lock = $depth;

                continue;
            }

            if ($condition_object instanceof Condition) {

                if ($is_debug_mode) {
                    $this->exec_condition_positions[] = $condition_position;
                }

                $result = (yield $condition_object);

                if (!is_bool($result)) {

                    throw new \RuntimeException(sprintf("Reactive generator expects to receive a result of boolean type in bidirectional communication, got type \"%s\".", gettype($result)));
                }

                # Keeping this for debugging purposes. Debug mode must be turned on for this to work. This will print parameters for the condition that has been executed and the result received.
                /* echo $depth
                    . ' ' . intval($recursive_iterator->isFirstInGroup())
                    . ' ' . $condition_position
                    . ' ' . $fixed_array[0]->__toString()
                    . ' => ' . var_export($result, true)
                    . PHP_EOL; */
            }
        }

        return $result;
    }


    // Loops the reactive-selective generator by using a condition handler and returns the final boolean result.

    public function reactiveMatch(\Closure $condition_handler): bool
    {

        $reactive_generator = $this->getReactiveGenerator();

        /* Using "while", because within a foreach loop the "send" method resumes the generator, which means the pointer within the generator is moved to the next element in the generator list. */
        while ($reactive_generator->valid()) {

            $reactive_generator->send(
                // Current returns the condition.
                // This also acts as last result.
                $result = $condition_handler($reactive_generator->current())
            );
        }

        return $result;
    }


    // Gets a generator which loops through all conditions and integrates indexes based on the preceeding named operator.

    public function getAllConditionsHavingIndexesGenerator(): \Generator
    {

        $recursive_iterator = new EnhancedRecursiveIteratorIterator(
            $this->getIterator(),
            mode: \RecursiveIteratorIterator::CHILD_FIRST
        );

        // Real preceeding named operator. Remember, for first in depth items the named operator is ignored.
        $preceeding_named_operator = null;
        // Stores indexes for uncompleted groups.
        $indexes_stored = [];

        foreach ($recursive_iterator as $key => $fixed_array) {

            [$condition_object, $named_operator] = $fixed_array;
            $depth = $recursive_iterator->getDepth();

            if (!$recursive_iterator->isFirstInGroup()) {
                $preceeding_named_operator = $named_operator;
            }

            $is_condition = ($condition_object instanceof Condition);

            $indexes = ($is_condition)
                ? (yield $condition_object)
                : ($indexes_stored[$depth + 1] ?? []);

            if (!$indexes) {
                $indexes = [];
            }

            if (!isset($indexes_stored[$depth])) {

                $indexes_stored[$depth] = $indexes;

            } else {

                // Core aspect: all indexes before a group (unless root level) preceeded by "OR" named operator are proclaimed "always" valid in the containing group, eg. (name = John OR (age > 25 AND occupation != student) AND sex = female) - all indexes satisfying condition "name = John" in the root group are "always" valid.
                if ((!$is_condition || !$depth) && $preceeding_named_operator === NamedOperatorsEnum::OR) {
                    $indexes_stored['__always'][$depth] = $indexes_stored[$depth];
                }

                $indexes_stored[$depth] = ($preceeding_named_operator === NamedOperatorsEnum::AND)
                    // Integrate the intersecting indexes.
                    ? array_intersect($indexes_stored[$depth], $indexes)
                    // Integrate combined indexes.
                    : ($indexes_stored[$depth] + $indexes);
            }

            // When condition group is reached, integrate "always" valid indexes.
            if (!$is_condition) {

                if (isset($indexes_stored['__always'][$depth + 1])) {

                    $indexes_stored[$depth] = ($indexes_stored[$depth] + $indexes_stored['__always'][$depth + 1]);
                    unset($indexes_stored['__always'][$depth + 1]);
                }

                unset($indexes_stored[$depth + 1]);
            }
        }

        // Root level "always" indexes are not integrated in the loop above.
        if (isset($indexes_stored['__always'][0])) {

            $indexes_stored[0] = ($indexes_stored[0] + $indexes_stored['__always'][0]);
            unset($indexes_stored['__always'][0]);
        }

        return ($indexes_stored[0] ?? []);
    }


    // Determines whether elements in the given one-dimentional array match the conditions.
    /* There is no purpose to strict match against a multi-dimensional array, because params in condition group are not unique, eg. (foo = 1 AND bar = 2) OR (foo = 11 AND bar = 12). */

    public function matchArray(array $array, bool $return_elems = false): bool|array
    {

        $matching_elems = [];
        $full_match = $this->reactiveMatch(function (Condition $condition) use ($array, $return_elems, &$matching_elems): bool {

            if (
                !array_key_exists($condition->keyword, $array)
                // Don't match arrays or objects.
                || is_array($array[$condition->keyword])
                || is_object($array[$condition->keyword])
            ) {
                return false;
            }

            $is_match = $condition->match($condition->keyword, $array[$condition->keyword]);

            if ($return_elems && $is_match) {
                $matching_elems[$condition->keyword] = $array[$condition->keyword];
            }

            return $is_match;

        });

        return (!$return_elems)
            ? $full_match
            : [$full_match, $matching_elems];
    }


    // Determines whether elements in the given data model match the conditions.

    public function matchModel(BasePropertyModel $model, bool $return_elems = false): bool
    {

        $matching_elems = [];
        $full_match = $this->reactiveMatch(function (Condition $condition) use ($model, $return_elems, &$matching_elems): bool {

            try {
                $property_value = $model->getPropertyValue($condition->keyword);
            } catch (PropertyValueNotAvailableException|PropertyNotFoundException) {
                return false;
            }

            $is_match = $condition->match($condition->keyword, $property_value);

            if ($return_elems && $is_match) {
                $matching_elems[$condition->keyword] = $array[$condition->keyword];
            }

            return $is_match;

        });

        return (!$return_elems)
            ? $full_match
            : [$full_match, $matching_elems];
    }


    // Returns a list of chosen components used in this condition group

    public function getComponents(KeyValuePairEnum $elem = KeyValuePairEnum::VALUE, bool $unique = true): array
    {

        $values = [];
        $is_value = ($elem === KeyValuePairEnum::VALUE);

        foreach ($this->data as $fixed_array) {

            $condition_object = $fixed_array[0];

            // Condition
            if ($condition_object instanceof Condition) {

                $elem_name = ($is_value)
                    ? 'value'
                    : 'keyword';
                $value = $condition_object->{$elem_name};

                if (!$unique || !in_array($value, $values)) {
                    $values[] = $condition_object->{$elem_name};
                }

                // Condition group
            } else {

                $method_name = ($is_value)
                    ? 'getValues'
                    : 'getKeywords';
                $values = [...$values, ...$condition_object->{$method_name}($unique)];
            }
        }

        return $values;
    }


    // Returns a list of condition values used in this condition group

    public function getValues(bool $unique = true): array
    {

        return $this->getComponents(KeyValuePairEnum::VALUE, $unique);
    }


    // Returns a list of keyword used in this condition group

    public function getKeywords(bool $unique = true): array
    {

        return $this->getComponents(KeyValuePairEnum::KEY, $unique);
    }


    // Creates a new instance from an array.

    public static function fromArray(
        array $array,
        NamedOperatorsEnum $default_named_operator = NamedOperatorsEnum::AND,
        ConditionComparisonOperatorsEnum $default_comparison_operator = ConditionComparisonOperatorsEnum::EQUAL_TO,
        ?string $root_name = null
    ): self {

        $make_tree_closure;

        $make_tree_closure = (static function (
            array $array,
            mixed $group_name,
        ) use (
            $default_named_operator,
            $default_comparison_operator,
            &$make_tree_closure,
        ): ConditionGroup {

            $condition_group = new ConditionGroup(
                name: (
                    is_string($group_name)
                    ? $group_name
                    : null
                )
            );

            foreach ($array as $key => $value) {

                if (is_array($value)) {

                    $condition_group->add(
                        $make_tree_closure($value, $key),
                        $default_named_operator
                    );

                } else {

                    $condition_group->add(
                        new Condition($key, $value, $default_comparison_operator),
                        $default_named_operator
                    );
                }
            }

            return $condition_group;

        });

        return $make_tree_closure($array, $root_name);
    }


    // Creates a new instance of self and adds the given condition into it

    public static function fromCondition(Condition $condition, array $params = []): self
    {

        $instance = new self(...$params);
        $instance->add($condition);

        return $instance;
    }
}

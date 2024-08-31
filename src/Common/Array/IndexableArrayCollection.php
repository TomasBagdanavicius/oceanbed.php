<?php

declare(strict_types=1);

namespace LWP\Common\Array;

use LWP\Common\Criteria;
use LWP\Common\String\Clause\SortByComponent;
use LWP\Common\Collections\Collection;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\sortByColumns;
use function LWP\Common\Array\Arrays\multisortPrepare;

class IndexableArrayCollection extends ArrayCollection
{
    private ArrayObjectIndexTree $index_tree;


    public function __construct(
        protected array $data = [],
        public readonly bool $two_level_tree_support = false,
        ?\Closure $element_filter = null,
        ?\Closure $obtain_name_filter = null,
        ?Collection $parent = null
    ) {

        $this->index_tree = new ArrayObjectIndexTree([], $two_level_tree_support);

        parent::__construct(
            element_filter: $element_filter,
            obtain_name_filter: $obtain_name_filter,
            parent: $parent
        );

        if ($data) {

            foreach ($data as $key => $value) {
                $this->set($key, $value);
            }
        }
    }


    // Gets the index tree object.

    public function getIndexTree(): ArrayObjectIndexTree
    {

        return $this->index_tree;
    }


    // Returns an array of arguments that can be used to create a new instance of collection

    public function getNewInstanceArgs(array $data, array $args = []): array
    {

        $main = [
            'data' => $data,
            'two_level_tree_support' => $this->two_level_tree_support,
            'element_filter' => $this->element_filter,
            'obtain_name_filter' => $this->obtain_name_filter,
            'parent' => $this->parent
        ];

        return [...$main, ...$args];
    }


    // Sets a new key-value pair. If the element is an array or object, it will be indexed.

    public function set(int|string $key, mixed $element, array $context = [], int $pos = null): null|int|string
    {

        if (is_array($element) || is_object($element)) {

            if ($this->index_tree->dataIndexExists($key)) {
                $this->index_tree->removeData($key, $this->data[$key]);
            }

            $this->index_tree->add($element, $key);
        }

        return parent::set($key, $element, $context, $pos);
    }


    // Adds a new element. If it's an array or object, it will be indexed.

    public function add(mixed $element, array $context = []): int
    {

        $index_id = $this->getNextIndexId();

        if (is_array($element) || is_object($element)) {
            $index_id = $this->index_tree->add($element, $index_id);
        }

        return parent::set($index_id, $element);
    }


    // Removes element by a given key name.

    public function remove(int|string $key): mixed
    {

        if (!$this->containsKey($key)) {
            return null;
        }

        $this->index_tree->removeData($key, $this->data[$key]);

        return parent::remove($key);
    }


    // Replace an element with a new one by a given key name.

    public function update(int|string $key, mixed $element): ?bool
    {

        if (!$this->containsKey($key)) {
            return null;
        }

        $this->index_tree->removeData($key, $this->data[$key]);
        $this->index_tree->add($element, $key);

        return parent::update($key, $element);
    }


    // Updates a single value in an element by given index Id and key name.

    public function updateValue(int|string $index_id, int|string $key, mixed $value): ?bool
    {

        if (!$this->containsKey($index_id)) {
            return null;
        }

        $this->index_tree->updateValue($index_id, $key, $value);
        $this->data[$index_id][$key] = $value;

        return true;
    }


    //

    public function matchCondition(Condition $condition): self
    {

        $data_set = [];
        $indexes = $this->matchConditionIndexes($condition);

        // Matching indexes found.
        if ($indexes) {
            $data_set = array_intersect_key($this->data, array_flip($indexes));
        }

        return $this->fromArgs($data_set);
    }


    //

    public function matchConditionIndexes(Condition $condition): ?array
    {

        return $this->index_tree->assessCondition($condition);
    }


    //

    public function matchConditionIndexesCount(Condition $condition): int
    {

        $indexes = $this->matchConditionIndexes($condition);

        if (!$indexes) {
            return 0;
        }

        return count($indexes);
    }


    //

    public function matchConditionGroup(ConditionGroup $condition_group): self
    {

        $data_set = [];
        $indexes = $this->matchConditionGroupIndexes($condition_group);

        // Matching indexes found.
        if ($indexes) {
            $data_set = array_intersect_key($this->data, array_flip($indexes));
        }

        return $this->fromArgs($data_set);
    }


    //

    public function matchConditionGroupIndexes(ConditionGroup $condition_group): array
    {

        if (count($condition_group) === 0) {
            return [];
        }

        return $this->index_tree->assessConditionGroup($condition_group);
    }


    //

    public function matchConditionGroupIndexesCount(ConditionGroup $condition_group): int
    {

        return count($this->matchConditionGroupIndexes($condition_group));
    }


    // Perform a full criteria object match and constructs a new collection with found elements.

    public function matchCriteria(Criteria $criteria): self
    {

        // Indexes of condition matching.
        $indexes = $this->matchCriteriaIndexes($criteria);

        $data_set = ($indexes)
            ? $this->intersectKeys($indexes)
            : $this->data;

        if ($data_set) {

            /* Sort By */

            if ($sort_by = $criteria->getSort()) {

                if ($sort_by instanceof SortByComponent) {

                    sortByColumns($data_set, multisortPrepare($data_set, $sort_by), $sort_by, preserve_keys: true);

                } elseif (is_callable($sort_by)) {

                    // Using "uasort" to maintain index association.
                    uasort($data_set, $sort_by);
                }
            }

            /* Offset & Limit */

            $limit = $criteria->getLimit();
            $offset = $criteria->getOffset();

            if ($limit && count($data_set) > $offset) {

                $slice_params = [
                    'array' => $data_set,
                    'offset' => $offset,
                    'length' => (($limit >= 0)
                        ? $limit
                        : null),
                    'preserve_keys' => true,
                ];

                $data_set = array_slice(...$slice_params);

            } else {

                $data_set = [];
            }
        }

        return $this->fromArgs($data_set);
    }


    //

    public function matchCriteriaIndexes(Criteria $criteria): array
    {

        if (!count($criteria->base_condition_group)) {
            return [];
        }

        return $this->matchConditionGroupIndexes($criteria->base_condition_group);
    }


    //

    public function matchCriteriaCount(Criteria $criteria): int
    {

        $limit = $criteria->getLimit();

        if ($limit === 0) {
            return 0;
        }

        $count_indexes = count($this->matchCriteriaIndexes($criteria));

        return ($limit > 0 && $limit < $count_indexes)
            ? $limit
            : $count_indexes;
    }


    //

    public function matchSingleEqualToCondition(string $keyword, mixed $value): self
    {

        return $this->matchCondition(new Condition($keyword, $value, ConditionComparisonOperatorsEnum::EQUAL_TO));
    }


    //

    public function matchSingleEqualToConditionCount(string $keyword, mixed $value): int
    {

        return $this->matchConditionIndexesCount(new Condition($keyword, $value, ConditionComparisonOperatorsEnum::EQUAL_TO));
    }


    //

    public function matchKeyValues(string|int $key, array $values, bool $group_as_unique = false): ?array
    {

        return $this->index_tree->assessKeyValues($key, $values, $group_as_unique);
    }


    //

    public function supplement(array|object $data, null|int|string $index_name): void
    {

        $this->data[$index_name] = [...$this->data[$index_name], ...$data];
        $this->index_tree->supplement($data, $index_name);
    }
}

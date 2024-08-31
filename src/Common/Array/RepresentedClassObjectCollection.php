<?php

/* Class objects are not typical collectable arrays, such as mysqli database
associative result or any other 2 level array. For such class objects, Indexable
and Collectable interfaces are used. They make sure that class object can
represent themselves with an indexable array. This collection is for such
represented class objects. As a collection it primarily collects those class
object, but it also creates a separate parallel branch for an indexable array
collection. This class cannot directly extend IndexableArrayCollection, because
its collectable type is class object rather than an array. This is important
when calling "new static" within the IndexableArrayCollection. It cannot convert
representative array back to class object. */

declare(strict_types=1);

namespace LWP\Common\Array;

use LWP\Common\Criteria;
use LWP\Common\Collectable;
use LWP\Common\Indexable;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Collections\Collection;
use LWP\Components\Datasets\SpecialContainerCollection;

class RepresentedClassObjectCollection extends ArrayCollection
{
    private Collectable $last_added_element;
    public readonly IndexableArrayCollection $indexable_array_collection;


    public function __construct(
        array $data = [],
        public readonly bool $two_level_tree_support = false,
        ?\Closure $element_filter = null,
        ?\Closure $obtain_name_filter = null,
        ?Collection $parent = null
    ) {

        $this->indexable_array_collection = new IndexableArrayCollection([], $two_level_tree_support);

        parent::__construct($data, $element_filter, $obtain_name_filter, $parent);

        if ($data) {

            // Make sure primary data gets indexed. Needed when creating a copy of a portion of object data, eg. "filterByKeys" or "filterByValues".
            foreach ($data as $index => $elem) {

                if (is_object($elem) && ($elem instanceof Indexable)) {
                    $this->setIndexableElement($index, $elem);
                    $elem->registerCollection($this, $index);
                }
            }
        }
    }


    // Gets the flags.

    public function getFlags(): int
    {

        return $this->flags;
    }


    // Gets the indexable array collection.

    public function getIndexableArrayCollection(): IndexableArrayCollection
    {

        return $this->indexable_array_collection;
    }


    // Sets a new indexable and collectable object element.

    public function set(int|string $key, mixed $element, array $context = [], int $pos = null): null|int|string
    {

        $key = parent::set($key, $element);

        $this->setIndexableElement($key, $element);
        $this->setLastAddedElement($element);

        return $key;
    }


    // Adds a new indexable and collectable object element.

    public function add(mixed $element, array $context = []): null|int|string
    {

        $key = parent::add($element);

        $this->setIndexableElement($key, $element);
        $this->setLastAddedElement($element);

        if ($element instanceof Collectable) {
            $element->registerCollection($this, $key);
        }

        return $key;
    }


    // Sets the indexable data that represents the collectable object element.

    private function setIndexableElement(int|string $key, Indexable $element): null|int|string
    {

        return $this->indexable_array_collection->set($key, $element->getIndexableData());
    }


    // Sets the last added element.

    private function setLastAddedElement(Collectable $element): void
    {

        $this->last_added_element = $element;
    }


    // Gets the last added element.

    public function getLastAddedElement(): ?Collectable
    {

        return ($this->last_added_element ?? null);
    }


    // Updates a single name-value entry.

    public function updateEntry(int|string $key, int|string $name, int|string $value): ?bool
    {

        return $this->indexable_array_collection->updateValue($key, $name, $value);
    }


    // Creates a new collection from matching elements by given criteria.

    public function match(Criteria $criteria): static
    {

        return $this->filterByKeys(array_keys($this->indexable_array_collection->matchCriteria($criteria)->toArray()));
    }


    // Creates a new collection from elements that match the given single condition.

    public function matchCondition(Condition $condition): static
    {

        return $this->filterByKeys($this->indexable_array_collection->matchCondition($condition)->getKeys());
    }


    // Creates a new collection from elements that match the given condition group.

    public function matchConditionGroup(ConditionGroup $condition_group): static
    {

        return $this->filterByKeys($this->indexable_array_collection->matchConditionGroup($condition_group)->getKeys());
    }


    // Creates a new collection from elements that match a single name-value condition.

    public function matchBySingleCondition(int|string $name, mixed $value): static
    {

        return $this->filterByKeys($this->indexable_array_collection->matchSingleEqualToCondition($name, $value)->getKeys());
    }


    // Counts elements that match a single name-value condition.

    public function matchBySingleConditionCount(int|string $name, mixed $value): int
    {

        return $this->indexable_array_collection->matchSingleEqualToConditionCount($name, $value);
    }
}

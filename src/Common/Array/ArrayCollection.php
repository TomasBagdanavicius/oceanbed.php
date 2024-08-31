<?php

declare(strict_types=1);

namespace LWP\Common\Array;

use LWP\Common\Exceptions\ElementNotFoundException;
use LWP\Common\Collections\Collection;
use LWP\Common\Collections\Exceptions\EmptyCollectionException;
use LWP\Common\Collections\Exceptions\EndOfCollectionException;
use LWP\Common\Exceptions\ElementNotAllowedException;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\insertAssociative;

class ArrayCollection implements Collection, \IteratorAggregate, \JsonSerializable, \Countable
{
    /* The next index is calculated similarly to standard array behavior, where
    the next opaque key number is "max(key) + 1" */
    private int $next_index_id = 0;


    public function __construct(
        protected array $data = [],
        protected ?\Closure $element_filter = null,
        protected ?\Closure $obtain_name_filter = null,
        public readonly ?Collection $parent = null
    ) {

        if (!empty($data)) {

            // Find the largest index key number.
            $max = max(array_keys($data));

            if (is_numeric($max)) {

                // Set auto increment position after the largest index key number.
                $this->next_index_id = ($max + 1);
            }
        }
    }


    // Runs when this class object is cloned.

    public function __clone(): void
    {

        foreach ($this->data as $index => $data) {

            // Achieve deeper cloning and also possibly trigger "__clone" methods in those objects.
            if (is_object($data)) {
                $this->data[$index] = clone $data;
            }
        }
    }


    // Gets next index Id number.

    public function getNextIndexId(): int
    {

        return $this->next_index_id;
    }


    // Gets the element filter as closure.

    public function getElementFilter(): ?\Closure
    {

        return $this->element_filter;
    }


    // Gets the obtain name filter as closure.

    public function getObtainNameFilter(): ?\Closure
    {

        return $this->obtain_name_filter;
    }


    // Gets the parent collection.

    public function getParentCollection(): ?Collection
    {

        return $this->parent;
    }


    // Creates a new collection instante with the provided data array.

    public static function from(array $data, array $args = []): static
    {

        return new static($data, ...$args);
    }


    // Function to be called on data rebase, eg. `filterbyValues`

    public function onRebase(array $data): array
    {

        return $data;
    }


    // Returns an array of arguments that can be used to create a new instance of collection

    public function getNewInstanceArgs(array $data, array $args = []): array
    {

        $main = [
            'data' => $data,
            'element_filter' => $this->element_filter,
            'obtain_name_filter' => $this->obtain_name_filter,
            'parent' => $this->parent
        ];

        return [...$main, ...$args];
    }


    // Creates a new collection by initializing the new instance with managed arguments.

    public function fromArgs(array $data, array $args = []): self
    {

        $params = $this->getNewInstanceArgs($data, $args);

        return new static(...$params);
    }


    // Checks the provided element by using the given filter and determines whether it can be added to this collection.

    public function checkElement(mixed $element, null|int|string $key = null): void
    {

        if ($this->element_filter) {

            $callback_result = ($this->element_filter)($element, $key);

            if (!is_bool($callback_result)) {
                throw new \InvalidArgumentException("Element filter callback must return a boolean");
            }

            if (!$callback_result) {
                throw new ElementNotAllowedException(sprintf(
                    "Element is not allowed (key \"%s\")",
                    $key
                ));
            }
        }
    }


    // Sets a new key-element pair. Optionally, its position can be chosen.

    public function set(int|string $key, mixed $element, array $context = [], ?int $pos = null): null|int|string
    {

        $this->checkElement($element, $key);

        if ($pos === null) {

            $this->data[$key] = $element;

        } else {

            $this->data = insertAssociative($this->data, [
                $key => $element,
            ], $pos);
        }

        if (is_numeric($key) && $key >= $this->next_index_id) {

            $this->next_index_id = (intval($key) + 1);
        }

        return $key;
    }


    // Mass set key-value pairs from a provided payload.

    public function setMass(array $data): array
    {

        $result = [];

        foreach ($data as $key => $value) {

            $result[] = $this->set($key, $value);
        }

        return $result;
    }


    // Adds/Appends a new value.

    public function add(mixed $element, array $context = []): null|int|string
    {

        $next_index_id = $this->next_index_id;

        $this->checkElement($element, $next_index_id);

        if (
            $this->obtain_name_filter
            && ($name = ($this->obtain_name_filter)($element))
        ) {

            return $this->set($name, $element, $context);

        } else {

            // Casual incremented add, hence no position.
            $this->data[$next_index_id] = $element;
            $this->next_index_id++;

            return $next_index_id;
        }
    }


    // Updates element value by a given index key.

    public function update(int|string $key, mixed $element): ?bool
    {

        if (!$this->containsKey($key)) {
            return null;
        }

        $this->data[$key] = $element;

        return true;
    }


    // Replaces an existing element or sets a new one.

    public function setAndReplace(int|string $key, mixed $element)
    {

        return ($this->containsKey($key))
            ? $this->update($key, $element)
            : $this->set($key, $element);
    }


    // Completely empties the dataset.

    public function clear(): void
    {

        $this->data = [];
    }


    // Tells if the dataset is empty.

    public function isEmpty(): bool
    {

        return empty($this->data);
    }


    // Gets the first element in the dataset. If empty, throws an exception.

    public function first(): mixed
    {

        if ($this->isEmpty()) {
            throw new EmptyCollectionException(sprintf(
                "Collection %s is empty, therefore the first element cannot be returned; use \"isEmpty\" before calling this method",
                $this::class
            ));
        }

        return reset($this->data);
    }


    // Gets the last element in the dataset. If empty, throws an exception.

    public function last(): mixed
    {

        if ($this->isEmpty()) {
            throw new EmptyCollectionException(sprintf(
                "Collection %s is empty, therefore the last element cannot be returned; use \"isEmpty\" before calling this method",
                $this::class
            ));
        }

        return end($this->data);
    }


    // Gets current element in the dataset. If empty or if beyond the end of elements, throws an exception.

    public function current(): mixed
    {

        if ($this->isEmpty()) {
            throw new EmptyCollectionException(
                "Collection is empty, therefore the current element cannot be returned; use \"isEmpty\" before calling this method"
            );
        }

        $current = current($this->data);

        if ($current === false && $this->key() === null) {
            throw new EndOfCollectionException(
                "You have reached the end of the collection. There are no more elements."
            );
        }

        return $current;
    }


    // Gets next element in the dataset. If empty or if beyond the end of elements, throws an exception.

    public function next(): mixed
    {

        if ($this->isEmpty()) {
            throw new EmptyCollectionException(
                "Collection is empty, therefore the next element cannot be returned; use \"isEmpty\" before calling this method"
            );
        }

        $next = next($this->data);

        if ($next === false && $this->key() === null) {
            throw new EndOfCollectionException(
                "You have reached the end of the collection. There are no more elements."
            );
        }

        return $next;
    }


    // Gets the key name of the current element.

    public function key(): null|int|string
    {

        return key($this->data);
    }


    // Gets the number of elements in the dataset.

    public function count(): int
    {

        return count($this->data);
    }


    // Tells if specified key name exists in the dataset.

    public function containsKey(int|string $key): bool
    {

        // Using "isset" for performance.
        return (isset($this->data[$key]) || array_key_exists($key, $this->data));
    }


    // Tells if specified element value exists in the dataset.

    public function contains(mixed $element): bool
    {

        return in_array($element, $this->data, true); // Strict to compare types.
    }


    // Construct an array iterator from the dataset.

    public function getIterator(): \Traversable
    {

        return new \ArrayIterator($this->data);
    }


    // Gets element value by specified key.

    public function get(int|string $key): mixed
    {

        // All "empty" values are valid, hence the exception to indicate an unexisting element. It's recommended to use "containsKey" before getting any value.
        if (!$this->containsKey($key)) {
            throw new ElementNotFoundException(sprintf(
                "Entry with keyname \"%s\" was not found; use \"containsKey\" before calling this method",
                $key
            ));
        }

        return $this->data[$key];
    }


    // Searches the dataset for a given element value and returns the first corresponding key if successful.

    public function indexOf(mixed $element): null|int|string
    {

        return array_search($element, $this->data, true);
    }


    // Removes an element by a given key name.

    public function remove(int|string $key): mixed
    {

        if (!$this->containsKey($key)) {
            return null;
        }

        $removed = $this->data[$key];
        unset($this->data[$key]);

        return $removed;
    }


    // Gets a slice of the dataset.

    public function slice(int $offset, ?int $length = null): array
    {

        return array_slice($this->data, $offset, $length, preserve_keys: true);
    }


    // An alias of "containsKey" required by the "ArrayAccess" interface.

    public function offsetExists(mixed $offset): bool
    {

        return $this->containsKey($offset);
    }


    // An alias of "get" required by the "ArrayAccess" interface.

    public function offsetGet(mixed $offset): mixed
    {

        return $this->get($offset);
    }


    // Sets a new element value by a given offset key name.

    public function offsetSet(mixed $offset, mixed $value): void
    {

        if ($offset === null) {
            $this->add($value);
        } else {
            $this->set($offset, $value);
        }
    }


    // An alias of "remove" required by the "ArrayAccess" interface.

    public function offsetUnset(mixed $offset): void
    {

        $this->remove($offset);
    }


    // Gets the first element in the collection.

    public function getFirst(): mixed
    {

        $key = array_key_first($this->data);

        // Zero is a valid key.
        if ($key === null) {
            return null;
        }

        return $this->data[$key];
    }


    // Gets the last element in the collection.

    public function getLast(): mixed
    {

        $key = array_key_last($this->data);

        // Zero is a valid key.
        if ($key === null) {
            return null;
        }

        return $this->data[$key];
    }


    // Builds a new array with elements whose keys match the given list.

    public function intersectKeys(array $keys): array
    {

        $data = [];

        // Using loop instead of `array_intersect_key` to preserve the order of keys.
        foreach ($keys as $key) {
            if (array_key_exists($key, $this->data)) {
                $data[$key] = $this->data[$key];
            }
        }

        return $data;
    }


    // Exports the dataset to array.

    public function toArray(): array
    {

        return $this->data;
    }


    // Filters out elements by matching against provided keys.

    public function filterByKeys(array $keys): static
    {

        $data = $this->intersectKeys($keys);
        $data = $this->onRebase($data);

        return $this->fromArgs($data);
    }


    // Filters out elements by matching against provided values.

    public function filterByValues(array $values): static
    {

        $data = array_intersect($this->data, $values);
        $data = $this->onRebase($data);

        return $this->fromArgs($data);
    }


    // Exports data in JSON format.

    public function jsonSerialize(): mixed
    {

        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }


    // Gets element keys.

    public function getKeys(): array
    {

        return array_keys($this->data);
    }


    // Imports another collection into this collection.

    public function importFromCollection(Collection $collection): void
    {

        foreach ($collection as $name => $collection_member) {

            if (is_int($name)) {

                $this->add($collection_member);

            } else {

                // Trim off trailing dash followed by a digit.
                $original_name = preg_replace('/-\d+$/', '', $name);
                $new_name = $name;
                $i = 2;

                while ($this->containsKey($new_name)) {

                    $new_name = ($original_name . '-' . $i);
                    $i++;
                }

                $this->set($new_name, $collection_member);

            }
        }
    }
}

<?php

declare(strict_types=1);

namespace LWP\Common\Collections;

interface Collection extends \Countable, \IteratorAggregate, \ArrayAccess, \JsonSerializable
{
    // @return - null for failure (no affect).
    public function set(int|string $key, mixed $element, array $context = [], int $pos = null): null|int|string;

    // @return - null for failure (no affect).
    public function add(mixed $element, array $context = []): null|int|string;

    // @return - null when not exists, bool - success/failure.
    public function update(int|string $key, mixed $element): ?bool;

    public function clear(): void;

    public function isEmpty(): bool;

    public function first(): mixed;

    public function last(): mixed;

    public function key(): null|int|string;

    public function next(): mixed;

    public function current(): mixed;

    public function containsKey(int|string $key): bool;

    public function contains(mixed $element): bool;

    public function get(int|string $key): mixed;

    public function indexOf(mixed $element): null|int|string;

    // @return - the element that was removed.
    public function remove(int|string $key): mixed;

    public function slice(int $offset, ?int $length = null): array;

    public function toArray(): array;

}

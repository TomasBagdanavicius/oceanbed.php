<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\Interfaces;

use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Properties\BaseProperty;
use LWP\Common\Conditions\ConditionGroup;

interface DatasetInterface
{
    // Gets the unique dataset name

    public function getDatasetName(): string;


    // Gets database instance

    public function getDatabase(): DatabaseInterface;


    // Gets the unique dataset name abbreviation

    public function getAbbreviation(array $taken): string;


    // Sets custom unique dataset name abbreviation

    public function setAbbreviation(string $abbreviation): void;


    // Gets container count number

    public function getContainerCount(): int;


    // Gets container list

    public function getContainerList(): array;


    // Creates property object for a given container

    public function getContainerProperty(string $container_name): BaseProperty;


    // Gets full container definition data array

    public function getDefinitionDataArray(): array;


    // Gets complete container definition collection set

    public function getDefinitionCollectionSet(): DefinitionCollectionSet;


    // Tells if the given container is primary

    public function isContainerPrimary(string $container_name): bool;


    // Checks if the given container contains given single value

    public function containsContainerValue(string $container_name, ?string $value, ?ConditionGroup $condition_group = null): bool;


    // Checks if the given container contains given multiple values
    /* Returns found values. */

    public function containsContainerValues(string $container_name, array $values, ?ConditionGroup $condition_group = null): array;


    // Updates value for a given integer data type container by the provided primary key number.

    public function updateIntegerContainerValue(string $container_name, int $field_value, int|string $primary_key): int;


    // Tells if this dataset supports multiqueries.

    public function supportsMultiQuery(): bool;


    // Gets primary container name
    // There can be only one primary container or none at all

    public function getPrimaryContainerName(): ?string;


    // Gets descriptor object instance.

    public function getDescriptor(): DatabaseDescriptorInterface;


    // Gets given container's data type.

    public function getDataTypeForOwnContainer(string $container_name): string;


    // Validates container name

    public function validateContainerName(string $container_name, int $char_limit = 30): true;


    // Creates a new entry from given data

    public function createEntry(array $data, bool $product = false): array;


    // Gets representational name

    public function getRepresentationalName(): string;
}

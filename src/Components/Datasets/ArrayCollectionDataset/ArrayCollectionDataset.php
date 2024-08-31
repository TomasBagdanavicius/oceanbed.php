<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\ArrayCollectionDataset;

use LWP\Common\Array\ColumnArrayCollection;
use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Properties\BaseProperty;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Components\Datasets\AbstractDataset;
use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Components\Datasets\Interfaces\DatabaseInterface;
use LWP\Components\Datasets\Interfaces\DatasetDescriptorInterface;
use LWP\Components\Datasets\Interfaces\DatasetStoreFieldValueFormatterInterface;
use LWP\Components\Datasets\AbstractDatasetTrait;
use LWP\Components\Datasets\Attributes\SelectAllAttribute;
use LWP\Components\Model\BasePropertyModel;
use LWP\Common\Enums\NamedOperatorsEnum;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;
use LWP\Common\Iterators\ArrayColumnSelectIterator;
use LWP\Components\Datasets\Exceptions\EntryNotFoundException;
use LWP\Common\Enums\AccessLevelsEnum;
use LWP\Common\Exceptions\NotAllowedException;

class ArrayCollectionDataset extends AbstractDataset implements DatasetInterface
{
    public readonly ColumnArrayCollection $column_array_collection;


    public function __construct(
        public readonly array $column_data_array,
        public readonly array $definition_data_array,
        public readonly string $dataset_name,
        ?ArrayCollectionDatabase $database,
        public readonly ?string $primary_container_name = null,
        public readonly ?ConditionGroup $main_unique_case = null
    ) {

        parent::__construct($dataset_name, $database);
        $this->column_array_collection = new ColumnArrayCollection($column_data_array, $this->getContainerList());
    }


    // Returns an instance of the fetch manager

    public function getFetchManager(): ArrayCollectionDatasetFetchManager
    {

        $class_name = (__NAMESPACE__ . '\ArrayCollectionDatasetFetchManager');

        return new ($class_name)($this);
    }


    // Builds the main unique entry case

    public function buildMainUniqueCase(
        BasePropertyModel $model,
        bool $parameterize = true,
        array &$execution_params = [],
    ): ?ConditionGroup {

        return $this->main_unique_case;
    }


    // Sets up callbacks that will populate extra properties based on given property and its value

    public function setupModelPopulateCallbacks(BasePropertyModel $model): void
    {

        // Nothing to do here
    }


    //

    public function getDefinitionDataArray(): array
    {

        return $this->definition_data_array;
    }


    //

    public function byConditionObject(Condition|ConditionGroup $condition_object): iterable
    {

        return ($condition_object instanceof Condition)
            ? $this->column_array_collection->matchCondition($condition_object)
            : $this->column_array_collection->matchConditionGroup($condition_object);
    }


    // Checks if the given column contains given value.

    public function containsContainerValue(string $container_name, ?string $value, ?ConditionGroup $condition_group = null): bool
    {

        if (!$condition_group || count($condition_group) === 0) {
            return ($this->column_array_collection->matchSingleEqualToConditionCount($container_name, $value) !== 0);
        } else {
            $condition_group->add(new Condition($container_name, $value));
            return ($this->column_array_collection->matchConditionGroupIndexesCount($condition_group) !== 0);
        }
    }


    // Checks if the given column contains given values.
    /* Returns found values. */

    public function containsContainerValues(string $container_name, array $values, ?ConditionGroup $condition_group = null): array
    {

        $values_condition_group = new ConditionGroup();

        foreach ($values as $value) {
            $values_condition_group->add(new Condition($container_name, $value), NamedOperatorsEnum::OR);
        }

        if (!$condition_group || count($condition_group) === 0) {
            $root_condition_group = $values_condition_group;
        } else {
            $root_condition_group = new ConditionGroup();
            $root_condition_group->add($values_condition_group);
            $root_condition_group->add($condition_group);
        }

        $filtered_collection = $this->column_array_collection->matchConditionGroup($root_condition_group);

        $values = [];

        foreach ($filtered_collection as $data) {
            $values[] = $data[$container_name];
        }

        return $values;
    }


    // Updates value for a given integer data type column by the provided primary key number.

    public function updateIntegerContainerValue(string $container_name, int $field_value, int|string $primary_key): int
    {

        $this->assertOwnContainer($container_name);
        $data_type = $this->getDataTypeForOwnContainer($container_name);

        if ($data_type !== 'integer') {
            throw new \TypeError(sprintf(
                "Container \"%s\" is not of integer data type",
                $container_name
            ));
        }

        $property = $this->containers->getProperty($container_name);

        if ($property->getAccessLevel() !== AccessLevelsEnum::PUBLIC) {
            throw new NotAllowedException(sprintf(
                "Container \"%s\" cannot be updated",
                $container_name
            ));
        }

        $indexes = $this->updateBy($this->getPrimaryContainerName(), $primary_key, [
            $container_name => $field_value
        ]);

        return $indexes[array_key_first($indexes)];
    }


    // Gets primary column name

    public function getPrimaryContainerName(): ?string
    {

        return ($this->primary_container_name !== null)
            ? $this->primary_container_name
            : parent::getPrimaryContainerName();
    }


    //

    public function getSelectHandleClassName(): string
    {

        return ArrayCollectionDatasetSelectHandle::class;
    }


    //

    public function getStoreFieldValueFormatterIteratorClassName(): string
    {

        return ArrayCollectionDatasetStoreFieldValueFormatterIterator::class;
    }


    //

    public function modelFillIn(BasePropertyModel $model): void
    {


    }


    //

    public function countByConditionWithPrimaryExcluded(Condition $condition, array $exclude_primary = []): int
    {

        if (!$exclude_primary) {

            return $this->column_array_collection->matchConditionIndexesCount($condition);

        } else {

            $condition_group = ConditionGroup::fromCondition($condition);
            $primary_container_name = $this->getPrimaryContainerName();

            foreach ($exclude_primary as $primary_container_value) {
                $condition = new Condition($primary_container_name, $primary_container_value, ConditionComparisonOperatorsEnum::NOT_EQUAL_TO);
                $condition_group->add($condition, NamedOperatorsEnum::AND);
            }

            return $this->column_array_collection->matchConditionGroupIndexesCount($condition_group);
        }
    }


    //

    public function createEntryBasic(array $data): int|string
    {

        $data_containers = array_keys($data);
        $this->containers->containersExist($data_containers, throw: true);
        $this->validateRequired($data_containers);

        return $this->column_array_collection->add($data);
    }


    //

    public function lockContainersForUpdate(array $container_names): void
    {

        #tbd
    }


    //

    public function getStoreHandleClassName(): string
    {

        return ArrayCollectionDatasetStoreHandle::class;
    }


    //

    public function getMaxValueByContainer(string $container_name): int|string
    {


    }


    // Selects chosen containers for entries by a single condition

    public function getContainersByCondition(array $container_list, Condition $condition): iterable
    {

        $this->containers->containersExist($container_list, throw: true);
        $iterator = $this->byConditionObject($condition);
        $iterator = new ArrayColumnSelectIterator($iterator, $container_list);

        return $iterator;
    }


    // Updates by a single condition that can result in multiple entries

    public function updateBy(string $container_name, string|int|float $field_value, array $data): array
    {

        $condition = new Condition($container_name, $field_value);
        $indexes = $this->column_array_collection->matchConditionIndexes($condition);

        if ($indexes) {

            foreach ($indexes as $index) {
                foreach ($data as $container_name => $container_field_value) {
                    $this->column_array_collection->updateValue($index, $container_name, $container_field_value);
                }
            }
        }

        return $indexes;
    }


    //

    public function updateEntryBasic(string $container_name, string|int|float $field_value, array $data): int|string
    {

        $this->containers->assertUniqueContainer($container_name);

        $condition = new Condition($container_name, $field_value);
        $indexes = $this->column_array_collection->matchConditionIndexes($condition);
        // Expecting just one index
        $index = $indexes[array_key_first($indexes)];

        foreach ($data as $container_name => $container_field_value) {
            $this->column_array_collection->updateValue($index, $container_name, $container_field_value);
        }

        return $index;
    }


    //

    public function deleteBy(Condition $condition): array|int
    {

        $this->containerExists($condition->keyword, throw: true);

        $indexes = $this->column_array_collection->matchConditionIndexes($condition);
        $primary_container_name = $this->getPrimaryContainerName();
        $deleted = [];

        foreach ($indexes as $index) {
            $data = $this->column_array_collection->remove($index);
            $deleted[] = $data[$primary_container_name];
        }

        return $deleted;
    }


    //

    public function deleteEntry(string $container_name, string|int|float $field_value): string|int
    {

        $this->containers->assertUniqueContainer($container_name);

        $condition = new Condition($container_name, $field_value);
        $indexes = $this->column_array_collection->matchConditionIndexes($condition);

        if (!$indexes) {
            throw new EntryNotFoundException(sprintf(
                "Entry was not found, looked for value \"%g\" in container \"%s\"",
                $field_value,
                $container_name
            ));
        }

        // Expecting just one index
        $index = $indexes[array_key_first($indexes)];
        $data = $this->column_array_collection->remove($index);
        $primary_container_name = $this->getPrimaryContainerName();

        return $data[$primary_container_name];
    }
}

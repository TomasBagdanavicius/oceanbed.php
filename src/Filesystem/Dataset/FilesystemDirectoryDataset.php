<?php

declare(strict_types=1);

namespace LWP\Filesystem\Dataset;

use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Filesystem\FileType\Directory;
use LWP\Components\Datasets\AbstractDataset;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Enums\NamedOperatorsEnum;
use LWP\Components\Datasets\Interfaces\DatasetDescriptorInterface;
use LWP\Components\Datasets\Interfaces\DatasetStoreFieldValueFormatterInterface;
use LWP\Components\Model\BasePropertyModel;
use LWP\Components\Properties\RelationalProperty;
use LWP\Components\Model\RelationalPropertyModel;
use LWP\Filesystem\Path\FilePath;
use LWP\Filesystem\Path\Path;
use LWP\Filesystem\Path\PathEnvironmentRouter;
use LWP\Filesystem\FileType\File;
use LWP\Components\Constraints\NotInDatasetConstraint;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;
use LWP\Common\Exceptions\NotFoundException;
use LWP\Filesystem\Exceptions\FileNotFoundException;
use LWP\Filesystem\Filesystem;
use LWP\Common\Iterators\IndexableSelectIterator;
use LWP\Components\Datasets\Exceptions\EntryNotFoundException;
use LWP\Filesystem\Exceptions\FileDeleteError;
use LWP\Common\Enums\AccessLevelsEnum;
use LWP\Common\Exceptions\NotAllowedException;

class FilesystemDirectoryDataset extends AbstractDataset implements DatasetInterface
{
    public function __construct(
        public readonly Directory $directory,
        ?FilesystemDatabase $database = null
    ) {

        parent::__construct($directory->pathname, $database ?: new FilesystemDatabase());
    }


    // Gets data definitions

    public function getDefinitionDataArray(): array
    {

        return [
            'pathname' => [
                #review: when building models for find match, the file path might not represent an existing file
                'type' => 'string',
                'unique' => true,
                'required' => true,
                'searchable' => true,
                'set_access' => 'private',
                'dependencies' => [
                    'basename',
                ],
                'title' => "Path Name",
                'description' => "Full path name",
            ],
            'name' => [
                'type' => 'string',
                'alias' => 'pathname',
                'unique' => true,
                'title' => "Name",
                'description' => "An alias of pathname"
            ],
            'filename' => [
                'type' => 'string',
                'searchable' => true,
                'required' => true,
                'allow_empty' => false,
                'set_access' => 'private',
                'title' => "File Name",
                'description' => "File name (extension name excluded, see: basename)",
            ],
            'extension' => [
                'type' => 'string',
                'searchable' => true,
                'allow_empty' => true,
                'required' => true,
                'set_access' => 'private',
                'title' => "Extension",
                'description' => "Extension name",
            ],
            'basename' => [
                'type' => 'string',
                'searchable' => true,
                'join' => [
                    'properties' => [
                        'filename',
                        'extension',
                    ],
                    'options' => [
                        'separator' => Path::FILENAME_EXTENSION_PREFIX,
                        'shrink' => true,
                    ],
                ],
                'required' => true,
                'unique' => true,
                'title' => "Base Name",
                'description' => "Base name"
            ],
            'type' => [
                'type' => 'string',
                'in_set' => [
                    'file',
                    'directory',
                ],
                'required' => true,
                'title' => "Type",
                'description' => "File type"
            ],
            'size' => [
                'type' => 'integer',
                'min' => 0,
                'searchable' => true,
                'set_access' => 'private',
                'title' => "Size",
                'description' => "File size"
            ],
            'date_last_modified' => [
                'type' => 'datetime',
                'searchable' => true,
                'set_access' => 'private',
                'title' => "Date Modified",
                'description' => "Date-time when file was last modified"
            ]
        ];
    }


    // Returns primary container name

    public function getPrimaryContainerName(): ?string
    {

        return 'pathname';
    }


    //

    public function getFetchManager(): FilesystemDirectoryDatasetFetchManager
    {

        return new FilesystemDirectoryDatasetFetchManager($this);
    }


    // Returns fully qualified class name of the select handle object

    public function getSelectHandleClassName(): string
    {

        return FilesystemDirectoryDatasetSelectHandle::class;
    }


    // Returns fully qualified class name of the store handle object

    public function getStoreHandleClassName(): string
    {

        return FilesystemDirectoryDatasetStoreHandle::class;
    }


    // Runs a query by given condition or condition group

    public function byConditionObject(Condition|ConditionGroup $condition_object): iterable
    {

        $reader = $this->directory->getReader();
        $reader->conditions($condition_object);

        return $reader->getIterator();
    }


    //

    public function byConditionGroup(ConditionGroup $condition_group): iterable
    {

        return $this->byConditionObject($condition_group);
    }


    //

    public function byCondition(Condition $condition): iterable
    {

        return $this->byConditionObject($condition);
    }


    //

    public function countByConditionGroup(ConditionGroup $condition_group): int
    {

        return iterator_count($this->byConditionGroup($condition_group));
    }


    // Checks if the given container contains given value.

    public function containsContainerValue(string $container_name, ?string $value, ?ConditionGroup $condition_group = null): bool
    {

        $main_condition = new Condition($container_name, $value);

        if ($condition_group) {
            $root_condition_group = ConditionGroup::fromCondition($main_condition);
            $root_condition_group->add($condition_group);
        } else {
            $root_condition_group = $main_condition;
        }

        $iterator = $this->byConditionObject($root_condition_group);

        foreach ($iterator as $file) {
            return true;
        }

        return false;
    }


    // Checks if the given container contains given values.
    /* Returns found values. */

    public function containsContainerValues(string $container_name, array $values, ?ConditionGroup $condition_group = null): array
    {

        $main_condition_group = new ConditionGroup();

        foreach ($values as $value) {
            $main_condition_group->add(new Condition($container_name, $value), NamedOperatorsEnum::OR);
        }

        if ($condition_group) {
            $root_condition_group = new ConditionGroup();
            $root_condition_group->add($main_condition_group);
            $root_condition_group->add($condition_group);
        } else {
            $root_condition_group = $main_condition_group;
        }

        $iterator = $this->byConditionGroup($root_condition_group);
        $found_values = [];

        foreach ($iterator as $file) {
            #review: containers are not really indexable
            $found_values[] = $file->getIndexablePropertyValue($container_name);
        }

        return $found_values;
    }


    // Updates value for a given integer data type container by the provided primary key number

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

        $this->updateBy($container_name, $field_value, [
            $this->getPrimaryContainerName() => $primary_key
        ]);

        return $field_value;
    }


    // Gets descriptor fully qualified class name.

    public function getDescriptorClassName(): ?string
    {

        return FilesystemDirectoryDatasetDescriptor::class;
    }


    //

    public function getStoreFieldValueFormatterIteratorClassName(): string
    {

        return FilesystemDirectoryDatasetStoreFieldValueFormatterIterator::class;
    }


    //

    public function buildMainUniqueCase(
        BasePropertyModel $model,
        bool $parameterize = true,
        array &$execution_params = [],
    ): ?ConditionGroup {

        // This tells that the default unique case is essentially the main unique case.
        return null;
    }


    // Sets up callbacks that will populate extra properties based on given property and its value

    public function setupModelPopulateCallbacks(BasePropertyModel $model): void
    {

        $model->onAfterSetValue(
            function (
                mixed $property_value,
                BasePropertyModel $model,
                string $property_name
            ): mixed {

                // Populate extra fields from the "basename" value
                if ($property_name === 'basename') {

                    // Start setting values as private (system edit)
                    $model->occupySetAccessControlStack();

                    $directory_separator = $this->directory->file_path->getDefaultSeparator();

                    /* Since there is pathname<=basename dependency, and dependencies are solved once all "after set value" hooks are called, and this piece of code is inside one of those hooks, solve dependency manually. */
                    $model->getPropertyByName('pathname')->attemptToSolveDependency($property_name);
                    $model->pathname = ($this->directory->pathname . $directory_separator . $property_value);

                    // End setting values in system edit mode
                    $model->deoccupySetAccessControlStack();
                }

                return $property_value;
            }
        );
    }


    //

    public function modelFillIn(BasePropertyModel $model): void
    {

        // Filesystem directory dataset currently has no properties that it would need to auto-fill
    }


    //

    public function createEntryBasic(array $data): string
    {

        $data_containers = array_keys($data);
        $this->containers->containersExist($data_containers, throw: true);
        $this->validateRequired($data_containers);

        $type_enumerated = Filesystem::convertTypeToEnumerated($data['type']);
        Filesystem::createFile($data['pathname'], $type_enumerated);

        return $data[$this->getPrimaryContainerName()];
    }


    // Updates by a single condition that can result in multiple entries

    public function updateBy(string $container_name, string|int|float $field_value, array $data): array
    {

        $condition = new Condition($container_name, $field_value);
        $iterator = $this->byCondition($condition);
        $updated = [];

        foreach ($iterator as $file) {
            $updated_file = $file->update($data);
            $updated[] = $updated_file->getIndexablePropertyValue($this->getPrimaryContainerName());
        }

        return $updated;
    }


    //

    public function updateEntryBasic(string $container_name, string|int|float $field_value, array $data): int|string
    {

        $this->containers->assertUniqueContainer($container_name);

        // Under the assumption that there can ever only be 2 unique containers
        $basename = ($container_name === 'pathname')
            ? basename($field_value)
            : $field_value;
        $reader = $this->directory->getReader();
        $file = $reader->find($basename, throw: true);
        $updated_file = $file->update($data);

        return $updated_file->getIndexablePropertyValue($this->getPrimaryContainerName());
    }


    //

    public function countByConditionWithPrimaryExcluded(Condition $condition, array $exclude_primary = []): int
    {

        $condition_group = ConditionGroup::fromCondition($condition);
        $primary_container_name = $this->getPrimaryContainerName();

        foreach ($exclude_primary as $primary_container_value) {
            $condition = new Condition($primary_container_name, $primary_container_value, ConditionComparisonOperatorsEnum::NOT_EQUAL_TO);
            $condition_group->add($condition);
        }

        return $this->countByConditionGroup($condition_group);
    }


    //

    public function lockContainersForUpdate(array $container_names): void
    {

        // Nothing to be done here
    }


    //

    public function getMaxValueByContainer(string $container_name): int|string
    {

        $data_type = $this->getDataTypeForContainer($container_name);

        if ($data_type !== 'number' && $data_type !== 'integer') {
            throw new \Exception(sprintf(
                "Filesystem dataset can determine maximum value for containers of number and integer type only, got %s",
                $data_type
            ));
        }
    }


    // Selects chosen containers for entries by a single condition

    public function getContainersByCondition(array $container_list, Condition $condition): iterable
    {

        $this->containers->containersExist($container_list, throw: true);

        $reader = $this->directory->getReader();
        $reader->conditions($condition);

        return new IndexableSelectIterator($reader, $container_list);
    }


    //

    public function deleteBy(Condition $condition): array|int
    {

        $this->containerExists($condition->keyword, throw: true);
        $iterator = $this->byCondition($condition);
        $deleted = $failed = [];

        foreach ($iterator as $file) {

            $primary_container_value = $file->getIndexablePropertyValue($this->getPrimaryContainerName());

            try {
                $file->delete();
                $deleted[] = $primary_container_value;
            } catch (FileDeleteError) {
                $failed[] = $primary_container_value;
            }
        }

        return $deleted;
    }


    //

    public function deleteEntry(string $container_name, string|int|float $field_value): string|int
    {

        $this->containers->assertUniqueContainer($container_name);

        // Under the assumption that there can ever only be 2 unique containers
        $basename = ($container_name === 'pathname')
            ? basename($field_value)
            : $field_value;
        $reader = $this->directory->getReader();
        $file = $reader->find($basename);

        if ($file === null) {
            throw new EntryNotFoundException(sprintf(
                "Entry was not found, looked for value \"%g\" in container \"%s\"",
                $field_value,
                $container_name
            ));
        }

        $primary_container_value = $file->getIndexablePropertyValue($this->getPrimaryContainerName());
        $file->delete();

        return $primary_container_value;
    }
}

<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Common\Indexable;
use LWP\Common\Collectable;
use LWP\Components\Datasets\Interfaces\DatabaseInterface;
use LWP\Components\Datasets\Relationships\Relationship;
use LWP\Components\Datasets\Attributes\SelectAllAttribute;
use LWP\Components\Datasets\Exceptions\ContainerNotFoundException;
use LWP\Components\Properties\BaseProperty;
use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Datasets\Interfaces\DatabaseDescriptorInterface;
use LWP\Components\Definitions\Interfaces\WithDefinitionArrayInterface;
use LWP\Components\Model\BasePropertyModel;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Components\Properties\Exceptions\PropertyValueNotAvailableException;
use LWP\Common\Enums\NamedOperatorsEnum;
use LWP\Components\Properties\RelationalProperty;
use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\Constraints\NotInDatasetConstraint;
use LWP\Components\Attributes\NoValueAttribute;
use LWP\Common\Enums\AssortmentEnum;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;
use LWP\Common\Enums\AccessLevelsEnum;
use LWP\Common\Enums\ReadWriteModeEnum;
use LWP\Components\Violations\NotInSetViolation;
use LWP\Common\Enums\ValidityEnum;
use LWP\Common\Exceptions\NotFoundException;
use LWP\Components\DataTypes\ValueOriginEnum;
use LWP\Components\Properties\BasePropertyCollection;
use LWP\Components\DataTypes\Natural\Null\NullDataTypeValueContainer;
use LWP\Components\Datasets\Interfaces\DatabaseStoreFieldValueFormatterInterface;
use LWP\Components\Datasets\Relationships\RelatedTypeEnum;
use LWP\Components\Datasets\Container;
use LWP\Components\Datasets\ExtrinsicContainer;
use LWP\Components\Datasets\Exceptions\DatasetUpdateException;
use LWP\Components\Properties\AbstractPropertyCollection;
use LWP\Components\Properties\EnhancedPropertyCollection;
use LWP\Components\Rules\TagnameFormattingRule;
use LWP\Components\Rules\FormattingRuleCollection;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\generateStringNotIn;

abstract class AbstractDataset implements WithDefinitionArrayInterface, Indexable, Collectable
{
    use \LWP\Common\IndexableTrait;
    use \LWP\Common\CollectableTrait;


    public const VALIDATE_NAME = 1;

    /* Threshold for how many rows can receive product data. */
    public const MAX_UPDATE_ENTRIES = 100;


    protected string $abbreviation;
    public readonly array $own_container_list;
    protected array $related_read_container_data = [];
    protected array $related_store_container_data = [];
    protected array $related_read_container_list = [];
    protected array $related_store_container_list = [];
    public readonly SpecialContainerCollection $containers;
    public readonly ExtrinsicContainerCollection $foreign_container_collection;
    public readonly BasePropertyModel $model;


    public function __construct(
        public readonly string $name,
        public readonly DatabaseInterface $database,
        public readonly ?int $options = self::VALIDATE_NAME
    ) {

        if ($options & self::VALIDATE_NAME) {
            $this->database->validateDatasetName($name);
        }

        $definition_data_array = $this->getDefinitionDataArray();
        // Own container list is static
        $this->own_container_list = array_keys($definition_data_array);
        $this->related_read_container_list = array_keys($this->getRelatedReadContainerData());
        $this->related_store_container_list = array_keys($this->getRelatedStoreContainerData());
        $this->foreign_container_collection = new ExtrinsicContainerCollection();
        $this->containers = $this->buildFullSpecialContainerCollection();
    }


    //

    abstract public function getDefinitionDataArray(): array;


    //

    abstract public function modelFillIn(BasePropertyModel $model): void;


    //

    abstract public function byConditionObject(Condition|ConditionGroup $condition_object): iterable;


    // Selects chosen containers for entries by a single condition

    abstract public function getContainersByCondition(array $container_list, Condition $condition): iterable;


    //
    // @return an array of primary container values for deleted entries, or a number of entries deleted

    abstract public function deleteBy(Condition $condition): array|int;


    // Builds the main unique entry case

    abstract public function buildMainUniqueCase(
        BasePropertyModel $model,
        bool $parameterize = true,
        array &$execution_params = [],
    ): ?ConditionGroup;


    // Sets up callbacks that will populate extra properties based on given property and its value

    abstract public function setupModelPopulateCallbacks(BasePropertyModel $model): void;


    //

    abstract public function getFetchManager(): AbstractDatasetFetchManager;


    //

    abstract public function getSelectHandleClassName(): string;


    //

    abstract public function getStoreHandleClassName(): string;


    //

    abstract public function countByConditionWithPrimaryExcluded(Condition $condition, array $exclude_primary = []): int;


    //

    abstract public function getMaxValueByContainer(string $container_name): int|string;


    //

    abstract public function getStoreFieldValueFormatterIteratorClassName(): string;


    // Returns the unique dataset name

    public function getDatasetName(): string
    {

        return $this->name;
    }


    // Returns the representational dataset name

    public function getRepresentationalName(): string
    {

        return $this->getDatasetName();
    }


    // Returns the database

    public function getDatabase(): DatabaseInterface
    {

        return $this->database;
    }


    //

    public function getIndexablePropertyList(): array
    {

        return [
            'name'
        ];
    }


    // Returns value of a given indexable property

    public function getIndexablePropertyValue(string $property_name): mixed
    {

        $this->assertIndexablePropertyExistence($property_name);

        return match ($property_name) {
            'name' => $this->getDatasetName()
        };
    }


    // Tells if this dataset supports multi-queries

    public function supportsMultiQuery(): bool
    {

        // The default value
        return false;
    }


    // Returns a list of own containers

    public function getOwnContainerList(): array
    {

        return $this->own_container_list;
    }


    // An alias of the `getOwnContainerList` method

    public function getContainerList(): array
    {

        return $this->getOwnContainerList();
    }


    // Tells if own container exists

    public function hasOwnContainer(string $container_name): bool
    {

        return in_array($container_name, $this->own_container_list);
    }


    // Checks if all given containers exist as own containers

    public function hasOwnContainers(string $container_names): bool
    {

        return !array_diff($container_names, $this->own_container_list);
    }


    // Throws exception for when own container is not found

    protected function throwOwnContainerNotFoundException(string $container_name): void
    {

        throw new ContainerNotFoundException(sprintf(
            "Own container \"%s\" was not found in dataset \"%s\"",
            $container_name,
            $this->name
        ));
    }


    // Asserts own container existence

    public function assertOwnContainer(string $container_name): void
    {

        if (!in_array($container_name, $this->own_container_list)) {
            $this->throwOwnContainerNotFoundException($container_name);
        }
    }


    // Returns own container

    public function getOwnContainer(string $container_name): Container
    {

        $this->assertOwnContainer($container_name);
        return $this->containers->get($container_name);
    }


    // Gets schema for an own container

    public function getOwnContainerSchema(string $container_name): array
    {

        $definition_data_array = $this->getDefinitionDataArray();

        if (!isset($definition_data_array[$container_name])) {
            $this->throwOwnContainerNotFoundException($container_name);
        }

        return $definition_data_array[$container_name];
    }


    // Builds main container collection and registers extrinsic containers

    public function buildFullSpecialContainerCollection(): SpecialContainerCollection
    {

        $collection = new SpecialContainerCollection();
        $definition_data_array = $this->getDefinitionDataArray();

        foreach ($definition_data_array as $container_name => $schema) {
            $container = $this->database->findOrAddContainer($container_name, $this);
            $collection->add($container);
        }

        $related_read_container_data = $this->getRelatedReadContainerData();

        foreach ($related_read_container_data as $container_name => $build_options) {
            $this->addExtrinsicContainer($container_name, $build_options);
        }

        $related_store_container_data = $this->getRelatedStoreContainerData();

        foreach ($related_store_container_data as $container_name => $build_options) {
            $this->addExtrinsicContainer($container_name, $build_options, ReadWriteModeEnum::WRITE);
        }

        return $collection;
    }


    // Builds a container collection with given containers

    public function buildSelectedContainerGroup(array $container_list, bool $foreign_container_submit_index = false): SpecialContainerCollection
    {

        $collection = new SpecialContainerCollection();

        foreach ($container_list as $container_name) {

            if ($this->hasOwnContainer($container_name)) {
                $own_container = $this->database->findOrAddContainer($container_name, $this);
                $collection->add($own_container);
            } elseif ($this->foreignContainerExists($container_name)) {
                $foreign_container = $this->foreign_container_collection->get($container_name);
                $collection->add($foreign_container);
                if ($foreign_container_submit_index) {
                    $foreign_container->submitSchemaToIndex();
                }
            } else {
                throw new ContainerNotFoundException(sprintf(
                    "Container \"%s\" was not recognized",
                    $container_name
                ));
            }
        }

        return $collection;
    }


    // Adds extrinsic container by build options

    public function addExtrinsicContainer(string $container_name, array $build_options, ReadWriteModeEnum $type = ReadWriteModeEnum::READ): ExtrinsicContainer
    {

        $extrinsic_container = $this->foreign_container_collection->findByBuildOptions($build_options, $this, $type);

        if ($extrinsic_container) {
            return $extrinsic_container;
        }

        $params = [
            $container_name,
            $this,
            $build_options['relationship'],
            $build_options['property_name'],
            $type
        ];

        if (isset($build_options['perspective'])) {
            $params['perspective_position'] = $build_options['perspective'];
            unset($build_options['perspective']);
        }

        if (isset($build_options['which'])) {
            $params['which'] = $build_options['which'];
            unset($build_options['which']);
        }

        if (isset($build_options['join_options'])) {
            $params['join_options'] = $build_options['join_options'];
        }

        unset($build_options['relationship'], $build_options['property_name'], $build_options['join_options']);

        if ($build_options) {
            $params['extra_schema_data'] = $build_options;
        }

        $extrinsic_container = new ExtrinsicContainer(...$params);
        $this->foreign_container_collection->add($extrinsic_container);

        return $extrinsic_container;
    }


    // Registers a new related read container

    public function setRelatedReadContainer(string $container_name, array $build_options): void
    {

        $this->related_read_container_data[$container_name] = $build_options;
        $this->related_read_container_list[] = $container_name;

        $this->addExtrinsicContainer($container_name, $build_options);
    }


    // Registers a new related store container

    public function setRelatedStoreContainer(string $container_name, array $build_options): void
    {

        $this->related_store_container_data[$container_name] = $build_options;
        $this->related_store_container_list[] = $container_name;

        $this->addExtrinsicContainer($container_name, $build_options, ReadWriteModeEnum::WRITE);
    }


    // Returns the primary container name

    public function getPrimaryContainerName(): ?string
    {

        $container_list = $this->getOwnContainerList();

        // Acts as default method to get the primary container name
        return ($container_list)
            ? $container_list[array_key_first($container_list)]
            : null;
    }


    // Returns definition collection set for own containers

    public function getDefinitionCollectionSet(): DefinitionCollectionSet
    {

        return $this->containers->getDefinitionCollectionSet();
    }


    // Returns reusable definition collection set for own containers

    public function getReusableDefinitionCollectionSet(): DefinitionCollectionSet
    {

        return $this->containers->getReusableDefinitionCollectionSet();
    }


    // Returns the model

    public function getModel(bool $reuse = true): BasePropertyModel
    {

        if (!$reuse || !isset($this->model)) {
            $this->model = $this->containers->getModel();
        }

        return $this->model;
    }


    // Returns data type for the given own container

    public function getDataTypeForOwnContainer(string $container_name): string
    {

        return $this->containers->getDataTypeForContainer($container_name);
    }


    // Tells if related own container exists

    public function relatedOwnContainerExists(string $container_name): bool
    {

        $definition_array = $this->getDefinitionDataArray();

        return isset($definition_array[$container_name]['relationship']);
    }


    // Throws an exception for when related own container is not found

    protected function throwRelatedOwnContainerNotFoundException(string $container_name): void
    {

        throw new ContainerNotFoundException(sprintf(
            "Related intrinsic container %s does not exist in dataset \"%s\"",
            $container_name,
            $this->name
        ));
    }


    // Asserts related own container existence

    public function assertRelatedOwnContainerExistence(string $container_name): void
    {

        if (!$this->relatedOwnContainerExists($container_name)) {
            $this->throwRelatedOwnContainerNotFoundException($container_name);
        }
    }


    // Returns relationship name for a related own container

    public function getRelationshipNameForRelatedOwnContainer(string $container_name): string
    {

        $definition_array = $this->getDefinitionDataArray();

        if (!isset($definition_array[$container_name]['relationship'])) {
            $this->throwRelatedOwnContainerNotFoundException($container_name);
        }

        return $definition_array[$container_name]['relationship'];
    }


    // Returns related read container data

    public function getRelatedReadContainerData(): array
    {

        return $this->related_read_container_data;
    }


    // Returns a list of related read containers

    public function getRelatedReadContainerList(): array
    {

        return $this->related_read_container_list;
    }


    // Tells whether related read container exists

    public function relatedReadContainerExists(string $container_name): bool
    {

        return in_array($container_name, $this->related_read_container_list);
    }


    // Affirms whether the given container exists as related read property

    public function assertRelatedReadPropertyExistence(string $container_name): void
    {

        if (!$this->relatedReadContainerExists($container_name)) {
            throw new ContainerNotFoundException(sprintf(
                "Related read container \"%s\" was not found",
                $container_name
            ));
        }
    }


    // Returns build options for related read container

    public function getRelatedReadContainerBuildOptions(string $container_name): array
    {

        $this->assertRelatedReadPropertyExistence($container_name);

        return $this->related_read_container_data[$container_name];
    }


    // Returns relationship name for related read container

    public function getRelationshipNameForRelatedReadContainer(string $container_name): string
    {

        $build_options = $this->getRelatedReadContainerBuildOptions($container_name);

        return $build_options['relationship'];
    }


    // Returns related store container data

    public function getRelatedStoreContainerData(): array
    {

        return $this->related_store_container_data;
    }


    // Returns a list of related store containers

    public function getRelatedStoreContainerList(): array
    {

        return $this->related_store_container_list;
    }


    // Tells whether related store container exists

    public function relatedStoreContainerExists(string $container_name): bool
    {

        return in_array($container_name, $this->related_store_container_list);
    }


    // Affirms whether the given container exists as related store container

    public function assertRelatedStoreContainerExistence(string $container_name): void
    {

        if (!$this->relatedStoreContainerExists($container_name)) {
            throw new ContainerNotFoundException(sprintf(
                "Related store container \"%s\" was not found in dataset \"%s\"",
                $container_name,
                $this->name
            ));
        }
    }


    // Returns build options for related store container

    public function getRelatedStoreContainerBuildOptions(string $container_name): array
    {

        $this->assertRelatedStoreContainerExistence($container_name);

        return $this->related_store_container_data[$container_name];
    }


    // Returns relationship name for related store container

    public function getRelationshipNameForRelatedStoreContainer(string $container_name): string
    {

        $build_options = $this->getRelatedStoreContainerBuildOptions($container_name);

        return $build_options['relationship'];
    }


    // Returns a list of foreign containers

    public function getForeignContainerList(): array
    {

        return [...$this->related_read_container_list, ...$this->related_store_container_list];
    }


    // Tells whether foreign container exists

    public function foreignContainerExists(string $container_name): bool
    {

        return in_array($container_name, $this->getForeignContainerList());
    }


    // Affirms whether the given container exists as foreign container

    public function assertForeignContainerExistence(string $container_name): void
    {

        if (!$this->foreignContainerExists($container_name)) {
            throw new ContainerNotFoundException(
                "Foreign container \"%s\" was not found in dataset \"%s\"",
                $container_name,
                $this->name
            );
        }
    }


    // Returns relationship name for a foreign container

    public function getRelationshipNameForForeignContainer(string $container_name): string
    {

        if ($this->relatedReadContainerExists($container_name)) {
            return $this->getRelationshipNameForRelatedReadContainer($container_name);
        } elseif ($this->relatedStoreContainerExists($container_name)) {
            return $this->getRelationshipNameForRelatedStoreContainer($container_name);
        } else {
            throw new ContainerNotFoundException(sprintf(
                "Foreign container \"%s\" was not found in dataset \"%s\"",
                $property_name,
                $this->name
            ));
        }
    }


    // Returns relationship name for a related container

    public function getRelationshipNameForRelatedContainer(string $container_name): string
    {

        if ($this->relatedReadContainerExists($container_name)) {
            return $this->getRelationshipNameForRelatedReadContainer($container_name);
        } elseif ($this->relatedStoreContainerExists($container_name)) {
            return $this->getRelationshipNameForRelatedStoreContainer($container_name);
        } elseif ($this->relatedOwnContainerExists($container_name)) {
            return $this->getRelationshipNameForRelatedOwnContainer($container_name);
        } else {
            throw new ContainerNotFoundException(sprintf(
                "Related property %s was not found",
                $property_name
            ));
        }
    }


    //

    public function getGivenRelatedTypeBuildOptions(string $container_name, RelatedTypeEnum $related_type): array
    {

        return match ($related_type) {
            RelatedTypeEnum::OWN => [
                'relationship' => $this->getRelationshipNameForRelatedOwnContainer($container_name),
            ],
            RelatedTypeEnum::FOREIGN_READ => $this->getRelatedReadContainerBuildOptions($container_name),
            RelatedTypeEnum::FOREIGN_WRITE => $this->getRelatedStoreContainerBuildOptions($container_name),
        };
    }


    //

    public function getSelectHandle(
        array|SelectAllAttribute $identifiers = new SelectAllAttribute(),
        array $modifiers = [],
        ?string $model_class_name = null,
        array $model_class_extras = []
    ): AbstractDatasetSelectHandle {

        if ($identifiers instanceof SelectAllAttribute) {
            $identifiers = $this->own_container_list;
        }

        $class_name = $this->getSelectHandleClassName();

        return new $class_name($this, $identifiers, $modifiers, $model_class_name, $model_class_extras);
    }


    //

    public function getPrimaryContainerSelectHandle(): AbstractDatasetSelectHandle
    {

        return $this->getSelectHandle([
            $this->getPrimaryContainerName(),
        ]);
    }


    //

    public function getStoreHandle(array $identifiers = [], array $extra_params = []): AbstractDatasetStoreHandle
    {

        $class_name = $this->getStoreHandleClassName();

        return new $class_name($this, $identifiers, ...$extra_params);
    }


    // Gets the unique dataset name abbreviation.

    public function getAbbreviation(array $taken = []): string
    {

        $abbreviation = ($this->abbreviation ?? uniqid());

        if ($taken) {
            $abbreviation = generateStringNotIn($taken, $abbreviation);
        }

        return $abbreviation;
    }


    // Sets custom dataset name abbreviation.

    public function setAbbreviation(string $abbreviation): void
    {

        $this->abbreviation = $abbreviation;
    }


    // Gets container count number.

    public function getContainerCount(): int
    {

        return count($this->getContainerList());
    }


    // Creates property object for a given container.

    public function getContainerProperty(string $container_name): BaseProperty
    {

        $this->containers->assertContainerExistence($container_name);

        return BaseProperty::fromDefinitionArray(
            $container_name,
            $this->getDefinitionsForContainer($container_name)
        );
    }


    // Tells if the given container is primary.

    public function isContainerPrimary(string $container_name): bool
    {

        return ($container_name === $this->getPrimaryContainerName());
    }


    // Gets descriptor object instance

    public function getDescriptor(): DatabaseDescriptorInterface
    {

        return $this->database->getDescriptor();
    }


    //

    public function getStoreFieldValueFormatter(): DatabaseStoreFieldValueFormatterInterface
    {

        return $this->database->getStoreFieldValueFormatter();
    }


    // Builds a condition group (as a match shape) that can be used to probe matching data structures.
    // $parameterize - Whether to parameterize the conditions and build a separate array of execution params.
    // $rcte_id - Adds this recursive common table expression ID number to the condition group.

    public function buildDefaultUniqueCase(
        BasePropertyModel $model,
        bool $parameterize = false,
        ?int $rcte_id = null,
        bool $exclude_prime = false,
        bool $required_when_not_available = true
    ): null|\SplFixedArray|ConditionGroup {

        $exclude = (!$exclude_prime)
            ? []
            : [$this->getPrimaryContainerName()];
        $match_sensitive_containers = $this->containers->getMatchSensitiveContainers($exclude);

        if (!$match_sensitive_containers) {
            return null;
        }

        $condition_group = new ConditionGroup();

        if ($parameterize) {
            $execution_values = [];
        }

        $condition_options = [
            'parameterize' => $parameterize,
            'abbreviation' => $this->getAbbreviation()
        ];

        foreach ($match_sensitive_containers as $container_name) {

            try {

                $property_value = $model->getPropertyValue($container_name);

            } catch (PropertyValueNotAvailableException $exception) {

                $property = $model->getPropertyByName($container_name);

                if ($required_when_not_available && $property->isRequired() === true) {

                    throw new \Exception(
                        sprintf(
                            "Required property \"%s\" does not contain a value",
                            $container_name,
                        ),
                        previous: $exception
                    );
                }

                continue;
            }

            /* NULL values are omitted from being evaluated as unique values. */
            if ($property_value !== null) {

                $condition_group->add(
                    new Condition(
                        $container_name,
                        $property_value,
                        data: $condition_options
                    ),
                    NamedOperatorsEnum::OR
                );

                $execution_values[] = $property_value;
            }
        }

        if (count($condition_group) === 0) {
            return null;
        }

        if ($rcte_id !== null) {

            $outer_condition_group = new ConditionGroup();
            $outer_condition_group->add($condition_group);

            $condition_group = $outer_condition_group;
            $condition_group->add(
                new Condition(
                    keyword: 'rcte_i',
                    value: $rcte_id,
                    data: [
                        'abbreviation' => 'rcte',
                    ]
                ),
                NamedOperatorsEnum::AND
            );
        }

        if ($parameterize) {

            $fixed_array = new \SplFixedArray(2);
            $fixed_array[0] = $condition_group;
            $fixed_array[1] = $execution_values;

            return $fixed_array;

        } else {

            return $condition_group;
        }
    }


    // Builds a standard unique case where default unique case is joined with main unique case

    public function buildStandardUniqueCase(
        BasePropertyModel $model,
        bool $parameterize = false,
        ?int $rcte_id = null,
        bool $exclude_prime = false,
        ?BasePropertyModel $model_for_default_case = null,
        bool $required_when_not_available = true,
        // Exclude main unique case when any given containers intersect with main unique case participants
        ?array $compare_main_unique_case_participants = null
    ): null|\SplFixedArray|ConditionGroup {

        $natural_unique_case_result = self::buildDefaultUniqueCase(
            ($model_for_default_case ?? $model),
            $parameterize,
            // There can only be one RCTE ID and it will be added in this method below.
            rcte_id: null,
            exclude_prime: $exclude_prime,
            required_when_not_available: $required_when_not_available
        );

        $execution_params = [];

        if ($natural_unique_case_result) {

            $primary_unique_case = new ConditionGroup();

            if ($parameterize) {

                [
                    $primary_unique_case,
                    $execution_params
                ] = $natural_unique_case_result;

            } else {

                $primary_unique_case = $natural_unique_case_result;
            }
        }

        $root_condition_group = new ConditionGroup();
        $main_unique_case = $this->buildMainUniqueCase(
            $model,
            $parameterize,
            $execution_params
        );

        $include_main_unique_case = true;

        if ($compare_main_unique_case_participants && $main_unique_case) {

            $main_unique_case_participants = $main_unique_case->getKeywords();
            $include_main_unique_case = boolval(array_intersect(
                $main_unique_case_participants,
                $compare_main_unique_case_participants
            ));
        }

        if (isset($primary_unique_case)) {

            if ($include_main_unique_case && $main_unique_case) {
                // This now houses all unique cases.
                $primary_unique_case->add($main_unique_case, NamedOperatorsEnum::OR);
            }

            // If RCTE is not used or prime unique case has only one condition, don't nest all unique cases.
            if ($rcte_id === null || $primary_unique_case->count() === 1) {
                $root_condition_group = $primary_unique_case;
            } else {
                $root_condition_group->add($primary_unique_case);
            }

        } elseif ($include_main_unique_case && $main_unique_case) {

            // If RCTE is not used, don't nest the main unique case.
            if ($rcte_id === null) {
                $root_condition_group = $main_unique_case;
            } else {
                $root_condition_group->add($main_unique_case);
            }
        }

        $root_condition_group_count = $root_condition_group->count();

        if ($rcte_id !== null && $root_condition_group_count !== 0) {

            $root_condition_group->add(
                new Condition(
                    keyword: 'rcte_i',
                    value: $rcte_id,
                    data: [
                        'abbreviation' => 'rcte',
                    ]
                ),
                NamedOperatorsEnum::AND
            );
        }

        if ($root_condition_group_count === 0) {
            return null;
        }

        if ($parameterize) {

            $fixed_array = new \SplFixedArray(2);
            $fixed_array[0] = $root_condition_group;
            $fixed_array[1] = $execution_params;

            return $fixed_array;

        } else {

            return $root_condition_group;
        }
    }


    //

    public function getRelationalModelFromFullIntrinsicDefinitions(
        RelationalPropertyModel $model,
        bool $auto_population = true,
        bool $auto_fill_in = true,
        // Default is false, because batch unique constraint method is the preferred one.
        bool $dataset_unique_constraint = false,
        bool $field_value_extension = true,
        SpecialContainerCollection $containers = null
    ): void {

        // Auto fill-in feature.
        if ($auto_fill_in) {
            // Fill in properties automatically (eg. where default value is not sufficient, etc.).
            $this->modelFillIn($model);
        }

        // Auto population feature.
        if ($auto_population) {
            // Callbacks that will populate based on given value.
            $this->setupModelPopulateCallbacks($model);
        }

        // Field value extension feature
        if ($field_value_extension) {
            $this->enableFieldValueExtension($model, $containers);
        }

        /* Dataset unique constraint feature. `batchValidateUniqueContainers()` is preferred over this, but this option will be kept here. */
        if ($dataset_unique_constraint) {

            $unique_properties = $this->containers->getMatchSensitiveContainers();

            foreach ($unique_properties as $unique_property_name) {

                $property = $model->getPropertyByName($unique_property_name);

                $not_in_dataset_constraint = new NotInDatasetConstraint(
                    $this,
                    $unique_property_name
                );

                $property->setConstraint($not_in_dataset_constraint);
            }
        }
    }


    //

    public function enableFieldValueExtension(BasePropertyModel $model, SpecialContainerCollection $containers = null): void
    {

        $model->onAfterGetValue(
            function (
                mixed $property_value,
                BasePropertyModel $model,
                string $property_name
            ) use ($containers): mixed {

                // Skip nullables
                if ($property_value !== null) {

                    $containers = ($containers ?? $this->containers);

                    if (
                        // Can be a foreign container, which will be not included in `SpecialContainerCollection`
                        $containers->containsKey($property_name)
                        && !$containers->isVirtualContainer($property_name)
                        && $containers->isRelationalContainer($property_name)
                    ) {
                        return new FieldValueExtender($property_value, $property_name, $containers);
                    }
                }

                return $property_value;
            }
        );
    }


    // Batch validates unique flagged containers for duplicate values with data taken from a given model

    public function batchValidateUniqueContainers(BasePropertyModel $model): void
    {

        $unique_properties = $this->containers->getMatchSensitiveContainers();
        $definition_collection_set = $this->getDefinitionCollectionSet();

        /* Get relationship properties that don't have unique state defined explicitly. Normally, all properties that represent foreign key containers should have their unique state defined explicitly. This is useful for optimization purposes - the system doesn't have to check whether it's a one type (unique) container or not. */

        $condition_group = new ConditionGroup();
        $condition_group->add(new Condition('unique', new NoValueAttribute(), AssortmentEnum::EXCLUDE));
        $condition_group->add(new Condition('relationship', new NoValueAttribute(), AssortmentEnum::INCLUDE));
        $filtered_collection_set = $definition_collection_set->matchConditionGroup($condition_group);

        // In a healthy dataset with properly configured schema, this should come back empty. See notes above.
        if (count($filtered_collection_set) !== 0) {

            $relationship_property_names = $filtered_collection_set->getKeys();

            foreach ($relationship_property_names as $relationship_property_name) {

                $container = $this->containers->get($relationship_property_name);
                $relationship = $container->getRelationship();
                $the_other_perspective = $container->getTheOtherPerspective();

                if (
                    // Reference (foreign key) container.
                    !$relationship->isNode()
                    && $the_other_perspective->type_code === 1
                ) {

                    #todo: add warning to the error log asking the user to define "unique" state on this property explicitly.

                    $unique_properties[] = $relationship_property_name;
                }
            }
        }

        if ($unique_properties) {

            $root_condition_group = new ConditionGroup();
            $rcte_map = [];
            $count_unique_properties = count($unique_properties);
            $i = 0;

            foreach ($unique_properties as $unique_property) {

                try {
                    $value = $model->getPropertyValue($unique_property);
                } catch (PropertyValueNotAvailableException) {
                    continue;
                }

                $condition_group = new ConditionGroup();
                $condition_group->add(
                    new Condition($unique_property, $value, ConditionComparisonOperatorsEnum::EQUAL_TO)
                );

                /* If there is only one unique-flagged property, the RCTE is not
                required, because a single result means that the property
                matches, whereas no result implies that property does not match.
                */
                if ($count_unique_properties > 1) {

                    $condition_group->add(
                        new Condition(
                            'rcte_i',
                            $i,
                            ConditionComparisonOperatorsEnum::EQUAL_TO,
                            data: [
                                'abbreviation' => 'rcte',
                            ]
                        ),
                        NamedOperatorsEnum::AND
                    );

                    $rcte_map[$i] = [
                        'property_name' => $unique_property,
                        'property_value' => $value,
                    ];
                }

                $root_condition_group->add($condition_group, NamedOperatorsEnum::OR);

                $i++;
            }

            $count_root_condition_group = count($root_condition_group);

            if ($count_root_condition_group !== 0) {

                $select_handle = $this->getSelectHandle([
                    $this->getPrimaryContainerName(),
                ]);
                $fetch_manager = $this->getFetchManager();
                $data_server_context = $fetch_manager->getByConditionGroup(
                    $select_handle,
                    $root_condition_group,
                    use_rcte: true,
                    rcte_iterator_count: $count_root_condition_group
                );
                $result = $data_server_context->getDatasetResult();

                foreach ($result as $data) {

                    if ($rcte_map) {
                        $duplicate_property_info = $rcte_map[$data['rcte_id']];
                        $property_name = $duplicate_property_info['property_name'];
                        $property_value = (string)$duplicate_property_info['property_value'];
                        // Expecting single unique property
                    } else {
                        $property_name = $unique_property;
                        $property_value = (string)$value;
                    }

                    $property = $this->setupNotInSetViolation($model, $property_name, $property_value);

                    /* When entry matches multiple RCTE IDs, we receive info
                    about the first match only (condition group stops looking
                    until the first match). For instance, in filesystem dataset
                    when pathname matches, basename also matches, but RCTE ID is
                    returned for pathname only. The solutions is to look at
                    dependency list and mark all dependencies "taken" as well.
                    */
                    $dependency_list = $property->getDependencyList();

                    foreach ($dependency_list as $dependency_property_name) {
                        try {
                            $dependency_property_value = (string)$model->getPropertyValue($unique_property);
                        } catch (PropertyValueNotAvailableException) {
                            continue;
                        }
                        $this->setupNotInSetViolation($model, $dependency_property_name, $dependency_property_value);
                    }
                }
            }
        }
    }


    //

    public function setupNotInSetViolation(RelationalPropertyModel $model, string $property_name, string $property_value): RelationalProperty
    {

        $property = $model->getPropertyByName($property_name);

        $not_in_set_violation = new NotInSetViolation(
            (array)$property_value,
            $property_value,
            $property_value
        );

        $not_in_set_violation->setErrorMessageString(sprintf(
            "Value \"%s\" is taken",
            $property_value
        ));

        $property->setViolation($not_in_set_violation);

        return $property;
    }


    // Validates container name

    public function validateContainerName(string $container_name, int $char_limit = 30): true
    {

        return $this->validateIdentifier($dataset_name, $char_limit);
    }


    //

    public function assignTrustedDataCallback(BasePropertyModel $model, ?string $callback_identifier = null): int|string
    {

        $dataset_descriptor = $this->getDescriptor();
        $property_name_cache = [];
        $descriptor_cache = [];
        // Formatting rules, that when found on property, will automatically be considered as trusted store rules
        $store_formatting_rules = [
            TagnameFormattingRule::class
        ];

        $callback_id = $model->onBeforeSetValue(
            function (
                mixed $property_value,
                BasePropertyModel $model,
                string $property_name
            ) use (
                $dataset_descriptor,
                &$property_name_cache,
                &$descriptor_cache,
                $store_formatting_rules,
            ): mixed {

                $property = $model->getPropertyByName($property_name);
                $data_type_descriptor_class_name = $property->data_type_descriptor_class_name;

                if (!isset($property_name_cache[$property_name])) {

                    $descriptor_formatting_rule_collection = new FormattingRuleCollection();
                    $data_type_formatting_rule = $dataset_descriptor->getSetterFormattingRuleForDataType($property->data_type_name);

                    if ($data_type_formatting_rule) {
                        $descriptor_formatting_rule_collection->add($data_type_formatting_rule);
                    }

                    $property_formatting_rules = $property->getFormattingRuleCollection();

                    foreach ($store_formatting_rules as $store_formatting_rule) {

                        if ($property_formatting_rules?->containsKey($store_formatting_rule)) {
                            $descriptor_formatting_rule_collection->add($property_formatting_rules->get($store_formatting_rule));
                        }
                    }

                    $value_descriptor_class_name = $data_type_descriptor_class_name::getValueDescriptorClassName();
                    $value_descriptor = new $value_descriptor_class_name(
                        // All values stored in the dataset, by assumption, are valid
                        ValidityEnum::VALID,
                        // Assign dataset descriptor's formatting rule
                        $descriptor_formatting_rule_collection,
                        ValueOriginEnum::INTERNAL
                    );
                    $property_name_cache[$property_name] = $value_descriptor;

                } else {

                    $value_descriptor = $property_name_cache[$property_name];
                }

                if (
                    // Is not a NULL value
                    ($property_value !== null && !($property_value instanceof NullDataTypeValueContainer))
                    || ($property->data_type_class_name instanceof NullDataTypeValueContainer)
                ) {

                    $params = [
                        $property_value,
                        $value_descriptor,
                    ];
                    $formatting_rule_for_convert = $property->getDataTypeConvertFormattingRule();

                    if ($formatting_rule_for_convert) {
                        $params['formatting_rule'] = $formatting_rule_for_convert;
                    }

                    $property_value = $data_type_descriptor_class_name::getConverterClassName()::convert(...$params);
                }

                return $property_value;

            },
            identifier: $callback_identifier,
            // Should be the last one
            priority: 999
        );

        return $callback_id;
    }


    //

    public function unassignTrustedDataCallback(BasePropertyModel $data_model, int|string $callback_id): ?bool
    {

        return $data_model->unsetOnBeforeSetValueCallback($callback_id);
    }


    //

    public function assignModelCallbacks(BasePropertyModel &$model): void
    {

        $model->onFlushQueue(function (AbstractPropertyCollection $property_collection, BasePropertyModel $model): void {

            $primary_container_name = $this->getPrimaryContainerName();

            // Primary column is essential to be able to update properties from this model.
            if (!isset($model->{$primary_container_name})) {
                throw new \Exception(sprintf(
                    "Model must contain the primary container (%s) property for it to be updateable",
                    $primary_container_name
                ));
            }

            $update_data = [];
            $update_data_private = [];

            foreach ($property_collection as $property_name => $property) {

                $is_private = false;
                $is_own = in_array($property_name, $this->own_container_list);

                if ($is_own) {
                    $schema = $this->getOwnContainerSchema($property_name);
                    if (isset($schema['set_access']) && $schema['set_access'] === 'private') {
                        $is_private = true;
                    }
                }

                if (!$is_private) {
                    $update_data[$property_name] = $property->getValue();
                } else {
                    $update_data_private[$property_name] = $property->getValue();
                }
            }

            $update_manager = $this->getStoreHandle()->getUpdateManager();
            $update_result = $update_manager->singleFromArray(
                $primary_container_name,
                $model->{$primary_container_name},
                $update_data,
                $update_data_private
            );

            if ($update_result['status'] === 0) {
                throw new DatasetUpdateException("Could not update from flush queue");
            }
        });
    }


    // Generates the next value

    public function getNextUniqueContainerValueGenerator(
        string $container_name,
        string $original_value,
        int $max_length,
        string $separator
    ): \Closure {

        $iteration = 0;

        return function () use (&$iteration, $original_value, $max_length, $separator): string {

            if ($iteration === 0) {

                $new_value = $original_value;

            } else {

                $num_suffix = ($separator . ($iteration + 1));
                $new_value = ($original_value . $num_suffix);

                // When appending the numeric suffix, make sure it does not exceed the allowed maximum length
                if ($max_length && strlen($new_value) > $max_length) {
                    $new_value = (substr($original_value, 0, $max_length - strlen($num_suffix)) . $num_suffix);
                }
            }

            $iteration++;

            return $new_value;
        };
    }


    // Gets next value for a unique column
    /* Warning: this method should normally be used inside transactions. */
    // $max_length - Maximum allowed length of the new value

    public function getNextUniqueContainerValue(
        string $container_name,
        string $value,
        int $max_length,
        array $reserved = null,
        array $exclude = [],
        string $separator = '-',
    ): string {

        $this->containers->assertUniqueContainer($container_name);
        $generator = $this->getNextUniqueContainerValueGenerator($container_name, $value, $max_length, $separator);

        do {

            $new_value = $generator();
            $result = false;

            if (!$reserved || !in_array($new_value, $reserved)) {

                $condition = new Condition($container_name, $new_value);
                // If found, result is false
                $result = ($this->countByConditionWithPrimaryExcluded($condition, $exclude) === 0);
            }

        } while (!$result);

        return $new_value;
    }


    //

    public function solveUniqueContainers(array|BasePropertyModel $data, array $unique_container_definition_array): array|\ArrayAccess
    {

        foreach ($unique_container_definition_array as $container_name => $unique_container_metadata) {

            if (
                isset($data[$container_name], $unique_container_metadata['type'])
                && $unique_container_metadata['type'] === 'string'
            ) {

                $data[$container_name] = $this->getNextUniqueContainerValue(
                    container_name: $container_name,
                    value: $data[$container_name],
                    max_length: ($unique_container_metadata['max'] ?? 0)
                );
            }
        }

        return $data;
    }


    //

    abstract public function createEntryBasic(array $data): int|string;


    //

    abstract public function updateEntryBasic(string $container_name, string|int|float $field_value, array $data): int|string;


    //

    protected function getUniqueFieldsFromDataStore(array $data): array
    {

        $root_condition_group = new ConditionGroup();

        $condition_group = new ConditionGroup();
        // `true` is a synonym for "strict"
        $condition_group->add(new Condition('unique', true));
        $condition_group->add(new Condition('unique', 'strict'), NamedOperatorsEnum::OR);
        $root_condition_group->add($condition_group);

        $root_condition_group->add(new Condition('type', 'string'));

        $condition_group = new ConditionGroup();
        $condition_group->add(new Condition('readonly', new NoValueAttribute(), AssortmentEnum::EXCLUDE));
        $condition_group->add(new Condition('readonly', false), NamedOperatorsEnum::OR);
        $root_condition_group->add($condition_group);

        $definition_array = $this->getDefinitionCollectionSet()->matchConditionGroup($root_condition_group)->toArray();

        return $definition_array;
    }


    //

    abstract public function lockContainersForUpdate(array $container_names): void;


    //

    public function lockAndSolveUniqueContainers(array|BasePropertyModel $model, array $unique_column_definition_array): array|\ArrayAccess
    {

        $containers_to_lock = array_keys($unique_column_definition_array);

        // Lock given unique containers
        $this->lockContainersForUpdate($containers_to_lock);

        return $this->solveUniqueContainers($model, $unique_column_definition_array);
    }


    // Creates a new entry
    // $product - specifies if an attempt to format data should be taken to avoid insertion pitfalls, eg. date-time format, unique fields, etc.

    public function createEntry(array $data, bool $product = true): array
    {

        $data_containers = array_keys($data);
        $this->containers->containersExist($data_containers, throw: true);

        if ($product) {

            $unique_container_metadata = $this->getUniqueFieldsFromDataStore($data);

            /* Format data for store */

            $data_store_formatter_iterator = $this->containers->getStoreFieldValueFormatterIterator($data);

            foreach ($data_store_formatter_iterator as $key => $value) {
                $data[$key] = $value;
            }
        }

        if (!empty($unique_container_metadata)) {

            // To be captured inside the closure
            $created_data;
            $create_id;

            $this->getDatabase()->makeTransaction(function () use (
                $data,
                $unique_container_metadata,
                &$create_id,
                &$created_data,
            ): void {

                $created_data = $data = $this->lockAndSolveUniqueContainers($data, $unique_container_metadata);
                $create_id = $this->createEntryBasic($data);

            });

        } else {

            $created_data = $data;
            $create_id = $this->createEntryBasic($data);
        }

        return [
            $create_id => $created_data,
        ];
    }


    //

    public function validateRequired(array $available_containers): true
    {

        if ($available_containers) {

            $required_containers = $this->containers->getRequiredContainers();

            if ($required_containers) {

                $diff = array_diff($required_containers, $available_containers);

                if ($diff) {
                    throw new \Exception(sprintf(
                        "Some required containers were not provided: %s",
                        ('"' . implode('", ', $diff) . '"')
                    ));
                }
            }
        }

        return true;
    }


    //

    public function updateEntry()
    {


    }


    //

    public function update(string $container_name, string|int|float $field_value, array $data, bool $product = false): array
    {

        if ($product) {

            $unique_field_metadata = $this->getUniqueFieldsFromDataStore($data);

            /* Format data for store */

            $data_store_formatter_iterator = $this->containers->getStoreFieldValueFormatterIterator($data);

            foreach ($data_store_formatter_iterator as $key => $value) {
                $data[$key] = $value;
            }
        }

        $update_data = [];

        // Is set and not empty array.
        if (!empty($unique_field_metadata)) {

            $this->database->makeTransaction(function () use (
                $container_name,
                $field_value,
                $data,
                $unique_field_metadata,
                &$update_data,
            ): void {

                $this->lockContainersForUpdate(array_keys($unique_field_metadata));

                $primary_container_name = $this->getPrimaryContainerName();
                $condition = new Condition($container_name, $field_value);

                // Result of entries that need to be updated.
                $affected_entries_result = $this->getContainersByCondition(
                    [$primary_container_name, ...array_keys($data)],
                    $condition
                );

                $affected_entries_count = $affected_entries_result->count();

                // A set of entries to be updated can potentially be very large
                if ($affected_entries_count > self::MAX_UPDATE_ENTRIES) {
                    throw new \Exception(sprintf(
                        "Update set size (%s) exceeds the threshold (%d)",
                        $affected_entries_count,
                        self::MAX_UPDATE_ENTRIES
                    ));
                }

                $primary_column_value_list
                    = $entries
                    = [];

                /* Load entry data into memory. This arbitrary loop is required to gather a list of all primary container values. */
                foreach ($affected_entries_result as $entry_data) {

                    $entries[] = $entry_data;
                    $primary_column_value_list[] = $entry_data[$primary_container_name];
                }

                $cache = [];

                foreach ($entries as $entry_data) {

                    $unique_entry_data = array_filter(
                        $entry_data,
                        fn (int|string $key) => isset($unique_field_metadata[$key]),
                        ARRAY_FILTER_USE_KEY
                    );

                    $entry_update_data = $data;

                    foreach ($unique_entry_data as $unique_entry_container_name => $unique_entry_field_value) {

                        // Value from an unique column hasn't changed.
                        if ($data[$unique_entry_container_name] == $unique_entry_field_value) {

                            // Reserve the value.
                            $cache[$unique_entry_container_name][] = $unique_entry_field_value;
                            // Since value hasn't changed - don't update it.
                            unset($entry_update_data[$unique_entry_container_name]);

                        } else {

                            $entry_update_data[$unique_entry_container_name] = $this->getNextUniqueContainerValue(
                                container_name: $unique_entry_container_name,
                                value: $data[$unique_entry_container_name],
                                max_length: ($unique_field_metadata[$unique_entry_container_name]['max'] ?? 0),
                                reserved: ($cache[$unique_entry_container_name] ?? []),
                                exclude: $primary_column_value_list
                            );

                            // Reserve the value.
                            $cache[$unique_entry_container_name][] = $entry_update_data[$unique_entry_container_name];
                        }
                    }

                    if ($entry_update_data) {

                        $update_data[$entry_data[$primary_container_name]] = $entry_update_data;

                        $this->updateBy($primary_container_name, $entry_data[$primary_container_name], $entry_update_data);
                    }
                }

            });

        } else {

            $update_data = $data;

            $this->updateBy($container_name, $field_value, $data);
        }

        return $update_data;
    }


    /*** Prototype ***/

    //

    public function validateBuildOptions(array $build_options): void
    {

        if (!isset($build_options['relationship'])) {
            throw new \Exception("Build options must contain \"relationship\" element");
        }

        if (!isset($build_options['property_name'])) {
            throw new \Exception("Build options must contain \"property_name\" element");
        }
    }
}

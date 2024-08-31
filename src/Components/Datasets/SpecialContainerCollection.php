<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Common\Collectable;
use LWP\Common\Array\RepresentedClassObjectCollection;
use LWP\Common\Collections\ClassObjectCollection;
use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Datasets\Interfaces\ContainerInterface;
use LWP\Components\Datasets\Exceptions\ContainerNotFoundException;
use LWP\Components\Model\BasePropertyModel;
use LWP\Components\Model\RelationalPropertyModel;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Components\Properties\EnhancedProperty;
use LWP\Common\Enums\NamedOperatorsEnum;
use LWP\Components\Attributes\NoValueAttribute;
use LWP\Common\Enums\AssortmentEnum;
use LWP\Components\Definitions\Interfaces\WithDefinitionArrayInterface;
use LWP\Components\Datasets\Interfaces\DatasetStoreFieldValueFormatterInterface;
use LWP\Components\Datasets\Iterators\AbstractStoreFieldValueFormatterIterator;
use LWP\Components\Datasets\Relationships\RelationshipNodeStorageInterface;
use LWP\Components\Definitions\DefinitionCollection;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\sortByNumericElement;

class SpecialContainerCollection extends RepresentedClassObjectCollection implements ClassObjectCollection
{
    protected DefinitionCollectionSet $reusable_definition_collection_set;
    public readonly DatasetInterface $prime_dataset;


    public function __construct(
        array $data = [],
        protected array $modifiers = [],
        public readonly ?string $model_class_name = null,
        public readonly array $model_class_extras = []
    ) {

        if ($data) {
            $first = $data[array_key_first($data)];
            $this->prime_dataset = $first->dataset;
        }

        parent::__construct(
            $data,
            element_filter: function (
                mixed $element,
                null|int|string $key
            ): true {

                if (!($element instanceof ContainerInterface)) {
                    throw new \Exception(sprintf(
                        "Collection %s accepts elements that implement %s",
                        self::class,
                        ContainerInterface::class
                    ));
                }

                if (!isset($this->prime_dataset)) {
                    $this->prime_dataset = $element->dataset;
                } elseif ($this->prime_dataset !== $element->dataset) {
                    throw new \Exception("Datasets must be consistent");
                }

                return true;
            },
            obtain_name_filter: function (mixed $element): ?string {

                return $element->container_name;
            }
        );
    }


    //

    public function createNewMember(array $params = []): Collectable
    {

        #tbd
    }


    //

    public function onRebase(array $data): array
    {

        return $data;
    }


    //

    public function getNewInstanceArgs(array $data, array $args = []): array
    {

        return [
            'data' => $data,
            'modifiers' => $this->modifiers,
            'model_class_name' => $this->model_class_name,
            'model_class_extras' => $this->model_class_extras
        ];
    }


    //

    public function getContainerList(): array
    {

        return $this->getKeys();
    }


    //

    public function setModifier(array $modifier_data): void
    {

        $this->modifiers[] = $modifier_data;
    }


    //

    public function getDefinitionDataArray(): array
    {

        $indexable_array_collection = $this->getIndexableArrayCollection();
        $definition_data_array = $indexable_array_collection->toArray();

        if ($this->modifiers) {
            $modifiers = $this->modifiers;
            sortByNumericElement($modifiers, 'priority');
        }

        foreach ($definition_data_array as &$definitions) {

            unset(
                $definitions['container_name'],
                $definitions['container_type'],
                $definitions['dataset_name'],
                $definitions['perspective'],
                $definitions['the_other_perspective'],
                $definitions['dataset_association_type'],
                $definitions['extrinsic_container_name'],
                $definitions['join_options']
            );

            if (isset($modifiers)) {

                foreach ($modifiers as $data) {

                    if (is_array($data) && isset($data['modifier']) && is_callable($data['modifier'])) {
                        $modifier = $data['modifier'];
                        $definitions = $modifier($definitions);
                    }
                }
            }
        }

        return $definition_data_array;
    }


    // Returns schema for a given container

    public function getDefinitionsForContainer(string $container_name): array
    {

        $this->assertContainerExistence($container_name);

        return $this->get($container_name)->getSchema();
    }


    //

    public function getDefinitionCollectionForContainer(string $container_name): DefinitionCollection
    {

        return DefinitionCollection::fromArray(
            $this->getDefinitionsForContainer($container_name)
        );
    }


    //

    public function getDefinitionCollectionSet(): DefinitionCollectionSet
    {

        return DefinitionCollectionSet::fromArray($this->getDefinitionDataArray());
    }


    // Checks if given container exists

    public function hasContainer(string $container_name, bool $throw = false): bool
    {

        $has = $this->containsKey($container_name);

        if ($throw && !$has) {
            throw new ContainerNotFoundException(sprintf(
                "Container \"%s\" was not found",
                $container_name
            ));
        }

        return $has;
    }


    // Checks if all given containers exist

    public function containersExist(array $container_names, bool $throw = false): bool
    {

        $diff = array_diff($container_names, $this->getContainerList());

        if ($throw && $diff) {
            throw new ContainerNotFoundException(sprintf(
                "Some containers were not found: %s",
                ('"' . implode('", ', $diff) . '"')
            ));
        }

        return !$diff;
    }


    // Throws an exception if the given container does not exist

    public function assertContainerExistence(string $container_name): void
    {

        $this->hasContainer($container_name, throw: true);
    }


    // Gets reusable definition collection set

    public function getReusableDefinitionCollectionSet(): DefinitionCollectionSet
    {

        return ($this->reusable_definition_collection_set ??= $this->getDefinitionCollectionSet());
    }


    // Gets the model

    public function getModel(bool $reuse = true): BasePropertyModel
    {

        $definition_collection_set = ($reuse)
            ? $this->getReusableDefinitionCollectionSet()
            : $this->getDefinitionCollectionSet();
        $class_name = ($this->model_class_name ?: RelationalPropertyModel::class);

        return $class_name::fromDefinitionCollectionSet($definition_collection_set, $this->model_class_extras ?: []);
    }


    //

    public function getFilteredContainers(Condition|ConditionGroup $condition_object, array $exclude = [], bool $reuse = true): ?array
    {

        $definition_collection_set = ($reuse)
            ? $this->getReusableDefinitionCollectionSet()
            : $this->getDefinitionCollectionSet();

        $filtered_collection = ($condition_object instanceof Condition)
            ? $definition_collection_set->matchCondition($condition_object)
            : $definition_collection_set->matchConditionGroup($condition_object);

        foreach ($exclude as $property_name) {
            if ($filtered_collection->offsetExists($property_name)) {
                $filtered_collection->remove($property_name);
            }
        }

        return ($filtered_collection->getKeys() ?: null);
    }


    // Collects a list of containers that are match sensitive

    public function getMatchSensitiveContainers(array $exclude = [], bool $reuse = true): ?array
    {

        $condition_group = new ConditionGroup();
        // Find strictly unique columns
        // `true` is a synonym for "strict"
        $condition_group->add(new Condition('unique', true));
        $condition_group->add(new Condition('unique', 'strict'), NamedOperatorsEnum::OR);

        return $this->getFilteredContainers($condition_group, $exclude, $reuse);
    }


    // Gets a list of containers that are required

    public function getRequiredContainers(array $exclude = [], bool $reuse = true): ?array
    {

        // "true" is a synonym for "strict"
        $condition = new Condition('required', true);

        return $this->getFilteredContainers($condition, $exclude, $reuse);
    }


    // Gets a list of containers that are relational

    public function getRelationshipContainers(array $exclude = [], bool $reuse = true): ?array
    {

        $condition = new Condition('relationship', new NoValueAttribute(), AssortmentEnum::INCLUDE);

        return $this->getFilteredContainers($condition, $exclude, $reuse);
    }


    // Gets a list of containers that are loosely unique

    public function getLooselyUniqueContainers(array $exclude = [], bool $reuse = true): ?array
    {

        $condition = new Condition('unique', 'loose');

        return $this->getFilteredContainers($condition, $exclude, $reuse);
    }


    // Gets a list of containers that are flagged as searchable

    public function getSearchableContainers(array $exclude = [], bool $reuse = true): ?array
    {

        $condition = new Condition('searchable', true);

        return $this->getFilteredContainers($condition, $exclude, $reuse);
    }


    // Gets given container's data type

    public function getDataTypeForContainer(string $container_name): string
    {

        $definitions = $this->getDefinitionsForContainer($container_name);

        return $definitions['type'];
    }


    // Tells if given container represents a virtual property

    public function isVirtualContainer(string $container_name): bool
    {

        $definitions = $this->getDefinitionsForContainer($container_name);

        return (isset($definitions['virtual']) && $definitions['virtual'] === true);
    }


    //

    public function getNonVirtualContainers(): array
    {

        $condition = new Condition('virtual', new NoValueAttribute(), AssortmentEnum::EXCLUDE);

        return $this->getFilteredContainers($condition);
    }


    // Tells if given container should store unique values only

    public function isUniqueContainer(string $container_name): bool
    {

        $definitions = $this->getDefinitionsForContainer($container_name);

        return (!empty($definitions['unique']));
    }


    // Throws an exception if given container is not flagged as unique

    public function assertUniqueContainer(string $container_name, string $message_format = "Container \"%s\" is not unique"): void
    {

        if (!$this->isUniqueContainer($container_name)) {
            throw new \Exception(sprintf(
                $message_format,
                $container_name
            ));
        }
    }


    //

    public function getProperty(string $container_name): EnhancedProperty
    {

        return EnhancedProperty::fromDefinitionArray($container_name, $this->getDefinitionsForContainer($container_name));
    }


    // Checks if given container has "relationship" definition

    public function isRelationalContainer(string $container_name): false|string|array
    {

        $definition_array = $this->getDefinitionsForContainer($container_name);

        return (!empty($definition_array['relationship']))
            ? $definition_array['relationship']
            : false;
    }


    //

    public function assertRelationalContainer(string $container_name): string|array
    {

        $relationship_data = $this->isRelationalContainer($container_name);

        if (!$relationship_data) {
            throw new \Exception(sprintf(
                "Container \"%s\" is not a relational container",
                $container_name
            ));
        }

        return $relationship_data;
    }


    //

    public function resolveRelationalProperty(string $container_name, mixed $field_value): AbstractDatasetDataServerContext
    {

        $this->assertContainerExistence($container_name);
        $container = $this->get($container_name);
        $primary_container_name = $this->prime_dataset->getPrimaryContainerName();
        $the_other_dataset = $container->getTheOtherDataset();

        $select_handle = $the_other_dataset->getSelectHandle();
        $fetch_manager = $the_other_dataset->getFetchManager();

        return $fetch_manager->getSingleByUniqueContainer($select_handle, $primary_container_name, $field_value);
    }


    //

    public function getStoreFieldValueFormatterIterator(array|object $data): AbstractStoreFieldValueFormatterIterator
    {

        $class_name = $this->prime_dataset->getStoreFieldValueFormatterIteratorClassName();

        return new $class_name(
            new \ArrayIterator($data),
            $this->prime_dataset->database->getStoreFieldValueFormatter(),
            $this
        );
    }
}

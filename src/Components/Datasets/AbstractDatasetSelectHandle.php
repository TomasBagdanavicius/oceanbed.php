<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Properties\RelationalProperty;
use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\Definitions\Interfaces\WithDefinitionArrayInterface;
use LWP\Components\Datasets\Relationships\Relationship;
use LWP\Components\Datasets\Relationships\RelationshipPerspective;
use LWP\Components\Model\BasePropertyModel;
use LWP\Components\Datasets\FieldValueExtender;
use LWP\Components\Datasets\Interfaces\ContainerInterface;

abstract class AbstractDatasetSelectHandle implements WithDefinitionArrayInterface
{
    public readonly SpecialContainerCollection $containers;
    /* Reusable model */
    protected BasePropertyModel $model;


    public function __construct(
        public readonly DatasetInterface $dataset,
        protected array $identifiers,
        protected array $modifiers = [],
        protected ?string $model_class_name = null,
        protected array $model_class_extras = []
    ) {

        $identifiers = $this->addRelatedIdentifiers($identifiers);
        $relationships_to_fetch = [];
        $relationships_data = [];
        $this->containers = new SpecialContainerCollection(
            modifiers: $modifiers,
            model_class_name: $model_class_name,
            model_class_extras: $model_class_extras
        );

        foreach ($identifiers as $key => $identifier) {

            $relationship_data = null;
            $is_identifier_array = is_array($identifier);

            // Relationship meta data
            if ($is_identifier_array) {

                $relationship_data = $identifier;

                // Intrinsic container
            } elseif ($dataset->hasOwnContainer($identifier)) {

                $container = $dataset->database->findOrAddContainer($identifier, $dataset);
                $this->containers->add($container);

                // Extrinsic container
            } elseif ($dataset->relatedReadContainerExists($identifier)) {

                $relationship_data = $dataset->getRelatedReadContainerBuildOptions($identifier);

                // Unrecognized container name
            } else {

                try {

                    $relationship_data = self::parseRelationshipIdentifier($identifier);

                } catch (\Exception $exception) {

                    throw new \Exception(
                        sprintf("Unrecognized identifier \"%s\"", $identifier),
                        previous: $exception
                    );
                }
            }

            if ($relationship_data) {

                $property_name = ($is_identifier_array)
                    ? $key
                    : $identifier;
                $property_name = str_replace('-', '', $property_name);

                // Avoid duplicates
                if (!in_array($relationship_data['relationship'], $relationships_to_fetch)) {
                    $relationships_to_fetch[] = $relationship_data['relationship'];
                }

                $relationships_data[$property_name] = $relationship_data;
            }
        }

        if ($relationships_to_fetch) {

            $relationships = $dataset->database->getRelationships($relationships_to_fetch);

            foreach ($relationships_data as $property_name => $build_options) {
                $container = $dataset->addExtrinsicContainer($property_name, $build_options);
                $this->containers->add($container);
            }
        }
    }


    //

    abstract public function getDataServerContextClassName(): string;


    //

    public function getContainer(string $container_name): ContainerInterface
    {

        $this->containers->assertContainerExistence($container_name);
        return $this->containers->get($container_name);
    }


    //

    public function getDefinitionCollectionSet(): DefinitionCollectionSet
    {

        return $this->containers->getDefinitionCollectionSet();
    }


    //

    public function getReusableDefinitionCollectionSet(): DefinitionCollectionSet
    {

        return $this->containers->getReusableDefinitionCollectionSet();
    }


    //

    public function getModel(bool $reuse = true): BasePropertyModel
    {

        if ($reuse && isset($this->model)) {

            return $this->model;

        } else {

            $this->indexSchemaForExtrinsicContainers();

            $model = $this->containers->getModel();
            $this->dataset->assignTrustedDataCallback($model, 'trusted');
            $this->model = $model;

            return $model;
        }
    }


    //

    public function indexSchemaForExtrinsicContainers(): void
    {

        $extrinsic_containers = $this->getExtrinsicContainerList();

        if ($extrinsic_containers) {

            foreach ($extrinsic_containers as $extrinsic_container) {
                $this->containers->get($extrinsic_container)->submitSchemaToIndex();
            }
        }
    }


    //

    public function isAll(): bool
    {

        return !array_diff($this->dataset->getContainerList(), $this->identifiers);
    }


    // Adds properties that are instrumental to given identifiers (eg. in "join" or "match" relation)

    public function addRelatedIdentifiers(array $identifiers): array
    {

        $relation_name_map = RelationalProperty::mapRelationNamesToDefinitionNames();
        $definition_collection_set = $this->dataset->getReusableDefinitionCollectionSet();

        foreach ($identifiers as $identifier) {

            if (is_string($identifier) && $definition_collection_set->containsKey($identifier)) {

                $definition_collection = $definition_collection_set[$identifier];

                foreach ($relation_name_map as $relation_name => $definition_name) {

                    if ($definition_collection->containsKey($definition_name)) {

                        $definition_value = $definition_collection[$definition_name]->getValue();
                        $properties = ($definition_value['properties'] ?? (array)$definition_value);

                        $identifiers = [...$identifiers, ...$properties];
                    }
                }
            }
        }

        return $identifiers;
    }


    //

    public function getAllIdentifiers(): array
    {

        return $this->dataset->getContainerList();
    }


    //

    public function getSelectList(): array
    {

        return $this->containers->getContainerList();
    }


    //

    public function getExtrinsicContainerList(): array
    {

        return $this->containers->matchBySingleCondition('container_type', 'extrinsic')->getKeys();
    }


    //

    public function hasExtrinsicContainers(): bool
    {

        return !empty($this->getExtrinsicContainerList());
    }


    //

    public function hasExtrinsicContainer(string $property_name): bool
    {

        return in_array($property_name, $this->getExtrinsicContainerList());
    }


    //

    public function getRelationshipForExtrinsicContainer(string $property_name): ?Relationship
    {

        return $this->containers->get($property_name)->getRelationship();
    }


    //

    public function getPerspectiveForExtrinsicContainer(string $property_name): ?RelationshipPerspective
    {

        return $this->containers->get($property_name)->getPerspective();
    }


    //

    public function getTheOtherPerspectiveForExtrinsicContainer(string $property_name): ?RelationshipPerspective
    {

        return $this->containers->get($property_name)->getTheOtherPerspective();
    }


    //

    public function getTheOtherDatasetForExtrinsicContainer(string $property_name): ?DatasetInterface
    {

        return $this->getTheOtherPerspectiveForExtrinsicContainer($property_name)?->dataset;
    }


    //

    public function getPropertyNameForExtrinsicContainer(string $property_name): string
    {

        return $this->containers->get($property_name)->extrinsic_container_name;
    }


    //

    public function getDefinitionDataArray(): array
    {

        return $this->containers->getDefinitionDataArray();
    }


    //

    public function getSearchablePropertyNames(): ?array
    {

        return $this->containers->getSearchableContainers();
    }


    //

    public static function parseRelationshipIdentifier(string $identifier): array
    {

        if ($identifier === '') {
            throw new \ValueError("Identifier cannot be empty");
        }

        $parts = explode('_', $identifier, 2);
        $result = [
            'relationship' => array_shift($parts),
        ];

        if (!$parts) {
            throw new \Exception(sprintf(
                "Identifier cannot contain relationship name only (%s)",
                $identifier
            ));
        }

        $index = 0;
        $parts = explode('_', $parts[0], 2);

        while (ctype_digit($parts[0]) && $index < 2) {

            $element_name = match ($index) {
                0 => 'which',
                1 => 'perspective',
            };

            $result[$element_name] = array_shift($parts);

            if (!$parts) {
                break;
            }

            $parts = explode('_', $parts[0], 2);
            $index++;
        }

        if (!$parts) {
            throw new \Exception(sprintf(
                "Identifier \"%s\" is missing the property name part",
                $identifier
            ));
        }

        $result['property_name'] = implode('_', $parts);

        return $result;
    }
}

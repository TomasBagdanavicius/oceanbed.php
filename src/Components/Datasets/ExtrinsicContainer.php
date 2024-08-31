<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Common\Collectable;
use LWP\Common\Indexable;
use LWP\Common\Enums\ReadWriteModeEnum;
use LWP\Components\Datasets\Interfaces\ContainerInterface;
use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Components\Datasets\Relationships\RelatedTypeEnum;
use LWP\Components\Datasets\Relationships\Relationship;
use LWP\Components\Datasets\Relationships\RelationshipPerspective;
use LWP\Components\Definitions\DefinitionCollection;

class ExtrinsicContainer extends AbstractContainer implements Indexable, Collectable, ContainerInterface
{
    use \LWP\Common\IndexableTrait;
    use \LWP\Common\CollectableTrait;


    public Container $container;


    public function __construct(
        public readonly string $container_name,
        /* Dataset that wants to join this extrinsic container */
        public readonly DatasetInterface $dataset,
        public readonly string $relationship_name,
        public readonly string $extrinsic_container_name,
        public readonly ReadWriteModeEnum $type = ReadWriteModeEnum::READ,
        public readonly ?int $perspective_position = null,
        public readonly ?int $which = null,
        public readonly array $extra_schema_data = [],
        public readonly ?array $join_options = []
    ) {

    }


    //

    public function getIntrinsicContainer(): Container
    {

        $this->container ??= $this->getTheOtherPerspective()->dataset->getOwnContainer($this->extrinsic_container_name);

        return $this->container;
    }


    //

    public function getSchema(): array
    {

        if ($this->type === ReadWriteModeEnum::WRITE) {

            $relationship = $this->getRelationship();

            if ($relationship->node_dataset) {
                $target_container = $relationship->node_dataset->getOwnContainer($this->extrinsic_container_name);
            }
        }

        if (!isset($target_container)) {
            $target_container = $this->getIntrinsicContainer();
        }

        $schema = $target_container->getSchema();
        $schema['relationship'] = $this->relationship_name;
        // Allow for null value, when link to the extrinsic container does not yield a value
        $schema['nullable'] = true;

        if (!isset($schema['unique'])) {
            $schema['unique'] = false;
        }

        if ($this->extra_schema_data) {
            $schema = [...$schema, ...$this->extra_schema_data];
        }

        unset($schema['dependencies']);

        return $schema;
    }


    //

    public function getDefinitionCollection(): DefinitionCollection
    {

        return DefinitionCollection::fromArray($this->getSchema());
    }


    //

    public function getBuildOptions(bool $full = false, bool $extra_schema_data = false): array
    {

        $result = [
            'relationship' => $this->relationship_name,
            'property_name' => $this->extrinsic_container_name
        ];

        if ($full || $this->perspective_position !== null) {
            $result['perspective'] = (!$full)
                ? $this->perspective_position
                : $this->getPerspective()->position;
        }

        if ($full || $this->which !== null) {
            $result['which'] = (!$full)
                ? $this->which
                : $this->getTheOtherPerspective()->position;
        }

        if ($full && $extra_schema_data) {
            $result = [...$result, ...$this->extra_schema_data];
        }

        return $result;
    }


    //

    public function getIndexablePropertyList(): array
    {

        return [
            'container_name',
            'container_type',
            'extrinsic_container_name',
            'relationship',
            'perspective',
            'the_other_perspective',
            'dataset_association_type',
            ...array_keys($this->getSchema()),
        ];
    }


    //

    public function getIndexablePropertyValue(string $property_name): mixed
    {

        if ($property_name === 'container_name') {
            return $this->container_name;
        } elseif ($property_name === 'container_type') {
            return 'extrinsic';
        } elseif ($property_name === 'extrinsic_container_name') {
            return $this->extrinsic_container_name;
        } elseif ($property_name === 'relationship') {
            return $this->relationship_name;
        } elseif ($property_name === 'perspective') {
            return (isset($this->perspective)) ? $this->perspective->position : null;
        } elseif ($property_name === 'the_other_perspective') {
            return (isset($this->the_other_perspective)) ? $this->the_other_perspective->position : null;
        } elseif ($property_name === 'dataset_association_type') {
            return strtolower($this->type->name);
        } else {
            $schema = $this->getSchema();
            if (array_key_exists($property_name, $schema)) {
                return $schema[$property_name];
            }
            throw new ElementNotFoundException(sprintf("Element %s was not found", $property_name));
        }
    }


    //

    public function getIndexableData(): array
    {

        return [
            'container_name' => $this->container_name,
            'container_type' => 'extrinsic',
            'extrinsic_container_name' => $this->extrinsic_container_name,
            'relationship' => $this->relationship_name,
            'perspective' => (isset($this->perspective)) ? $this->perspective->position : null,
            'the_other_perspective' => (isset($this->the_other_perspective)) ? $this->the_other_perspective->position : null,
            'dataset_association_type' => strtolower($this->type->name)
        ];
    }


    //

    public function submitSchemaToIndex(): void
    {

        $schema = null;

        foreach ($this->collections as $data) {
            [$collection] = $data;
            if ($collection instanceof ExtrinsicContainerCollection || $collection instanceof SpecialContainerCollection) {
                $schema ??= $this->getSchema();
                $collection->getIndexableArrayCollection()->supplement($schema, $this->container_name);
            }
        }
    }
}

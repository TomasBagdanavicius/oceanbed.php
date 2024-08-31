<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Common\Collectable;
use LWP\Common\Exceptions\ElementNotFoundException;
use LWP\Common\Indexable;
use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Components\Definitions\DefinitionCollection;
use LWP\Components\Datasets\Interfaces\ContainerInterface;

class Container extends AbstractContainer implements Indexable, Collectable, ContainerInterface
{
    use \LWP\Common\IndexableTrait;
    use \LWP\Common\CollectableTrait;


    public readonly ?string $relationship_name;
    public readonly array $build_options;


    public function __construct(
        public readonly string $container_name,
        public readonly DatasetInterface $dataset
    ) {

        $dataset->assertOwnContainer($container_name);
        $schema = $dataset->getOwnContainerSchema($container_name);
        $this->build_options = (isset($schema['relationship']) && is_array($schema['relationship']))
            ? $schema['relationship']
            : [];
        $this->relationship_name = ($schema['relationship']['name'] ?? $schema['relationship'] ?? null);
    }


    //

    public function getSchema(): array
    {

        return $this->dataset->getOwnContainerSchema($this->container_name);
    }


    //

    public function getBuildOptions(bool $full = false): array
    {

        if ($this->build_options) {

            $result = $this->build_options;
            $result['relationship'] = $result['name'];

        } else {

            $result = [
                'relationship' => $this->relationship_name
            ];
        }

        return $result;
    }


    //

    public function getDefinitionCollection(): DefinitionCollection
    {

        return DefinitionCollection::fromArray($this->getSchema());
    }


    //

    public function getIndexablePropertyList(): array
    {

        return [
            ...array_keys($this->getSchema()),
            'container_name',
            'container_type',
            'dataset_name',
        ];
    }


    //

    public function getIndexablePropertyValue(string $property_name): mixed
    {

        if ($property_name === 'container_name') {
            return $this->container_name;
        } elseif ($property_name === 'dataset_name') {
            return $this->dataset->getDatasetName();
        } elseif ($property_name === 'container_type') {
            return 'intrinsic';
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

        return [...$this->getSchema(), ...[
            'container_name' => $this->container_name,
            'container_type' => 'intrinsic',
            'dataset_name' => $this->dataset->getDatasetName(),
        ]];
    }
}

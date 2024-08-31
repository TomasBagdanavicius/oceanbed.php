<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Common\Indexable;
use LWP\Common\Collectable;
use LWP\Common\Collections\ClassObjectCollection;
use LWP\Common\Array\RepresentedClassObjectCollection;
use LWP\Common\Exceptions\EmptyElementException;
use LWP\Common\Collections\Exceptions\InvalidMemberException;

class DefinitionCollection extends RepresentedClassObjectCollection implements
    ClassObjectCollection,
    Indexable,
    Collectable
{
    use \LWP\Common\IndexableTrait;
    use \LWP\Common\CollectableTrait;


    public function __construct(
        array $data = [],
        ?DefinitionCollectionSet $parent = null
    ) {

        // Allow for valid constraint object classes to be added only.
        parent::__construct(
            data: $data,
            element_filter: function (mixed $element): true {

                if (!($element instanceof Definition)) {

                    throw new InvalidMemberException(sprintf(
                        "Collection %s accepts elements of class %s only",
                        self::class,
                        Definition::class
                    ));
                }

                return true;

                // Use element class name as the name identifier in the collection.
            },
            obtain_name_filter: function (mixed $element): ?string {

                if ($element instanceof Definition) {
                    return $element->getName();
                }

                return null;

            },
            parent: $parent
        );
    }


    // Creates a new definition object instance and attaches it to this collection.

    public function createNewMember(array $params = []): DefinitionCollection
    {

        if (!empty($params['type'])) {

            throw new EmptyElementException(sprintf(
                "Missing \"type\" element in the first argument in function %s.",
                __FUNCTION__
            ));
        }

        $class_name = ('LWP\Components\Definitions' . ucfirst(strtolower($params['type'])) . 'Definition');

        if (!isset($params['value']) || $params['value'] == '') {

            throw new EmptyElementException(sprintf(
                "Missing \"value\" element in the first argument in function %s.",
                __FUNCTION__
            ));
        }

        $definition = new $class_name($params['value']);
        $definition->registerCollection($this, $this->add($definition));

        return $definition;
    }


    //

    public function getNewInstanceArgs(array $data, array $args = []): array
    {

        return [
            'data' => $data,
            'parent' => $this->parent
        ];
    }


    //

    public function hasPrimaryDefinition(): bool
    {

        return boolval($this->matchBySingleConditionCount('is_primary', value: true));
    }


    //

    public function getPrimaryDefinition(): ?Definition
    {

        if (!$this->hasPrimaryDefinition()) {
            return null;
        }

        return $this->matchBySingleCondition('is_primary', value: true)->first();
    }


    //

    public static function fromArray(array $array, ?DefinitionCollectionSet $parent_definition_collection_set = null, bool $ignore_unexisting = false): self
    {

        $definition_collection = new self(
            parent: $parent_definition_collection_set,
        );

        foreach ($array as $name => $value) {

            if ($ignore_unexisting && !DefinitionFactory::definitionExists($name)) {
                continue;
            }

            $definition_collection->add(DefinitionFactory::createNew($name, $value));
        }

        return $definition_collection;
    }


    // Converts to definition collection data array.

    public function toArray(): array
    {

        $result = [];

        foreach ($this->data as $name => $definition) {
            $result[$name] = $definition->getValue();
        }

        return $result;
    }


    //

    public function getIndexablePropertyList(): array
    {

        return array_keys($this->data);
    }


    // Collects the indexable data that will represent this class object.

    public function getIndexableData(): array
    {

        return $this->toArray();
    }


    // Gets the type value.

    public function getTypeValue(): string
    {

        return $this->get('type')->getValue();
    }
}

<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Common\Array\RepresentedClassObjectCollection;
use LWP\Common\Collections\Exceptions\InvalidMemberException;
use LWP\Components\Definitions\Exceptions\InvalidVariableNameException;

class DefinitionCollectionSet extends RepresentedClassObjectCollection
{
    public function __construct(
        array $data = []
    ) {

        // Allow for valid constraint object classes to be added only.
        parent::__construct($data, two_level_tree_support: true, element_filter: function (mixed $element): true {

            if (!($element instanceof DefinitionCollection)) {
                throw new InvalidMemberException(sprintf(
                    "Collection %s accepts elements of class %s only",
                    self::class,
                    DefinitionCollection::class
                ));
            }

            return true;

        });
    }


    //

    public function getNewInstanceArgs(array $data, array $args = []): array
    {

        return [
            'data' => $data
        ];
    }


    // Since definition collection set requires named members, the "add" method is hereby disabled.

    public function add(mixed $element, array $context = []): int
    {

        throw new \LogicException(sprintf(
            "Method \"add\" is disabled in class %s, because it requires a named member. Use method \"set\" instead.",
            self::class
        ));
    }


    //

    public static function fromArray(array $array, bool $validate = false, bool $ignore_unexisting = false): static
    {

        $definition_collection_set = new static();

        foreach ($array as $var_name => $definition_array) {

            if ($validate && !self::validateVariableName($var_name)) {
                throw new InvalidVariableNameException(sprintf(
                    "Definition variable name \"%s\" is invalid: it must consist of alphanums and underscore \"_\" characters only",
                    $var_name
                ));
            }

            if (is_array($definition_array)) {
                $definition_collection_set->set($var_name, DefinitionCollection::fromArray($definition_array, $definition_collection_set, $ignore_unexisting));
            } else {
                throw new \TypeError(sprintf(
                    "Element (%s) value must be of array type, got \"%s\"",
                    $var_name,
                    gettype($definition_array)
                ));
            }
        }

        return $definition_collection_set;
    }


    //

    public function toArray(): array
    {

        $result = [];

        foreach ($this->data as $property_name => $definition_collection) {
            $result[$property_name] = $definition_collection->getIndexableData();
        }

        return $result;
    }


    //

    public static function validateVariableName(string $var_name): bool
    {

        return ctype_alpha(str_replace('_', '', $var_name));
    }
}

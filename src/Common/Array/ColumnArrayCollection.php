<?php

declare(strict_types=1);

namespace LWP\Common\Array;

use LWP\Common\Array\Exceptions\ColumnKeysMismatchException;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\valuesMatch;

class ColumnArrayCollection extends IndexableArrayCollection
{
    public function __construct(
        array $data,
        protected ?array $element_list = null
    ) {

        parent::__construct(
            $data,
            element_filter: function (
                array $element,
                null|int|string $key
            ): true {

                $keys = array_keys($element);

                if (!$this->element_list) {

                    $this->element_list = $keys;

                } elseif (
                    ($excess_diff = array_diff($keys, $this->element_list))
                    || ($shortage_diff = array_diff($this->element_list, $keys))
                ) {

                    throw new ColumnKeysMismatchException(sprintf(
                        "Element field keys at index \"%s\" must match the primary field keys; %s %s.",
                        $key,
                        ((isset($shortage_diff)) ? "missing" : "excess"),
                        ('"' . implode('", "', ($excess_diff ?: $shortage_diff)) . '"')
                    ));
                }

                return true;
            }
        );
    }


    //

    public function getElementList(): array
    {

        return $this->element_list;
    }


    //

    public function getNewInstanceArgs(array $data, array $args = []): array
    {

        $result = [
            'data' => $data
        ];

        return [...$result, ...$args];
    }


    // Filters the entire data array by including just the given elements

    public function selectElements(array $element_list): self
    {

        if (!$element_list) {
            throw new \ValueError("Element list cannot be empty");
        }

        // Requested element list matches the original one.
        if (valuesMatch($this->element_list, $element_list)) {
            return $this;
        }

        $unrecognized_element_list = array_diff($element_list, $this->element_list);

        if ($unrecognized_element_list) {
            throw new \Exception(sprintf(
                "Some elements were not recognized: %s",
                ('"' . implode('", "', $unrecognized_element_list) . '"')
            ));
        }

        $first_element = array_shift($element_list);
        $tree = $this->getIndexTree()->getTree();
        $prime_data_index = $tree['keys'][$first_element]['data_index'];
        $data = [];

        foreach ($prime_data_index as $index => $values) {

            $data[$index] = [
                $first_element => $values[0]
            ];

            foreach ($element_list as $column) {
                $data[$index][$column] = $tree['keys'][$column]['data_index'][$index][0];
            }
        }

        return new static(...$this->getNewInstanceArgs($data, [
            // Bring in back the first element
            'element_list' => [$first_element, ...$element_list],
        ]));
    }
}

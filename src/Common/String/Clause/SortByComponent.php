<?php

declare(strict_types=1);

namespace LWP\Common\String\Clause;

use LWP\Common\Common;
use LWP\Common\Indexable;
use LWP\Common\Enums\StandardOrderEnum;

class SortByComponent implements \Stringable
{
    public function __construct(
        private array $data = [],
    ) {

    }


    // Converts sort by component data array to a string.

    public function __toString(): string
    {

        if (empty($this->data)) {
            return '';
        }

        $parts = [];

        foreach ($this->data as $field => $data) {

            $part = $field;

            if (!empty($data['order'])) {

                if ($data['order'] instanceof StandardOrderEnum) {
                    $part .= (' ' . $data['order']->name);
                } else {
                    throw new \RuntimeException(sprintf("Order element must be an instance of \"%s\".", StandardOrderEnum::class));
                }
            }

            $parts[] = $part;
        }

        return implode(', ', $parts);
    }


    // Creates a new instance from string.

    public static function fromString(string $sort_by_str)
    {

        return new self(self::parseSortByString($sort_by_str));
    }


    // Gets the data.

    public function getData(): array
    {

        return $this->data;
    }


    // Gets the fields.

    public function getFields(): array
    {

        return array_keys($this->data);
    }


    // Parse a sort by string

    public static function parseSortByString(string $sort_by_str): array
    {

        $result = [];

        if ($sort_by_str !== '') {

            $parts = explode(',', $sort_by_str);

            foreach ($parts as $part) {

                $part = trim($part);

                if ($part === '') {
                    throw new \Exception("Part cannot be empty in sort by string \"{$sort_by_str}\".");
                }

                $elements = explode(' ', $part, 2);

                $result[$elements[0]] = [];

                if (isset($elements[1])) {

                    $order_str = trim($elements[1]);

                    $standard_order_enum_cases = StandardOrderEnum::cases();
                    $order_name_found = false;
                    $order_names = [];

                    foreach ($standard_order_enum_cases as $index => $standard_order_enum) {

                        $order_names[] = $standard_order_enum->name;

                        if ($order_str === $standard_order_enum->name) {
                            $order_name_found = true;
                            break;
                        }
                    }

                    if (!$order_name_found) {

                        throw new \Exception(sprintf(
                            "Order element must be either %s in sort by string \"%s\".",
                            ('"' . implode('" or "', $order_names) . '"'),
                            $sort_by_str
                        ));
                    }

                    $result[$elements[0]]['order'] = $standard_order_enum;
                }
            }
        }

        return $result;
    }


    //

    public static function getSortHandlerForIndexableObject(Indexable $indexable_object, string|self $sort_by_component): \Closure
    {

        if (is_string($sort_by_component)) {
            $sort_by_component = self::fromString($sort_by_component);
        }

        $keywords = $sort_by_component->getFields();
        $indexable_object->assertIndexablePropertyExistence($keywords);
        $sort_data = $sort_by_component->getData();

        return function (Indexable $a, Indexable $b) use ($sort_data): int {

            $array1 = $array2 = [];

            foreach ($sort_data as $field => $data) {

                $is_asc = ($data['order'] === StandardOrderEnum::ASC);
                $a_value = $a->getIndexablePropertyValue($field);
                $b_value = $b->getIndexablePropertyValue($field);

                if ($is_asc) {
                    $array1[] = $a_value;
                    $array2[] = $b_value;
                } else {
                    $array1[] = $b_value;
                    $array2[] = $a_value;
                }
            }

            return $array1 <=> $array2;
        };
    }


    // Converts standard order string (eg. "ASC", "DESC") to enum

    public static function standardOrderStringToEnum(string $order_str, bool $case_sensitive = true): ?StandardOrderEnum
    {

        return Common::findEnumCase(StandardOrderEnum::class, $order_str, $case_sensitive);
    }


    // Validates if given standard order string (eg. "ASC", "DESC") exists

    public static function assertStandardOrderString(string $order_str, bool $case_sensitive = true): void
    {

        if (!self::standardOrderStringToEnum($order_str, $case_sensitive)) {
            throw new \Exception(sprintf(
                "Invalid order string \"%s\"",
                $order_str
            ));
        }
    }


    // Combine a list of sort keywords and order strings into a sort by string

    public static function combineIntoString(array $sort, array $order, bool $order_case_sensitive = true): string
    {

        if (count($order) > count($sort)) {
            throw new \Exception("The number of order params cannot be larger than the number of sort params");
        }

        if (!$order) {
            throw new \Exception("There must be at least one order param");
        }

        $sort_by_str = '';
        $i = 0;
        $first_order_param = null;

        foreach ($sort as $sort_param) {

            if ($i) {
                $sort_by_str .= ', ';
            }

            $sort_by_str .= $sort_param;

            if (isset($order[$i])) {
                self::assertStandardOrderString($order[$i], $order_case_sensitive);
                $order_param = $first_order_param = strtoupper($order[$i]);
            } else {
                $order_param = $first_order_param;
            }

            $sort_by_str .= (' ' . $order_param);
            $i++;
        }

        return $sort_by_str;
    }
}

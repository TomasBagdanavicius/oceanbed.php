<?php

declare(strict_types=1);

namespace LWP\Common\Array\Arrays {


    use LWP\Common\String\Clause\SortByComponent;
    use LWP\Common\Exceptions\ElementNotFoundException;

    // Add to array and preserve the original key in case it matches the provided key by starting a multidimensional branch.

    function addPreserved(array &$array, string $key, mixed $value, bool $merge_in = false): void
    {

        if (!isset($array[$key]) && !array_key_exists($key, $array)) {

            $array[$key] = $value;

        } else {

            $a = &$array[$key];

            if (is_array($a)) {

                if ($merge_in && is_array($value)) {
                    $a = array_merge($a, $value);
                } else {
                    $a[] = $value;
                }

            } else {

                if ($merge_in && is_array($value)) {
                    $a = array_merge([$a], $value);
                } else {
                    $a = [$a, $value];
                }
            }
        }
    }


    // Split array values at delimiter and put the first part to key.

    function splitToKey(array $array, string $delimiter = ':'): array
    {

        $result = [];

        foreach ($array as $key => $value) {

            $parts = explode($delimiter, $value, 2);

            if (count($parts) == 2) {
                addPreserved($result, trim($parts[0]), trim($parts[1]));
            } else {
                addPreserved($result, $key, $value);
            }
        }

        return $result;
    }


    // Transform one level array by moving each key to the value and adding divider in between the added key and value.
    // [key] => [value] to [] => [key: value]

    function keyPrependToValue(array $array, string $divider = ': '): array
    {

        $result = [];

        foreach ($array as $key => $value) {

            $result[] = ($key . $divider . $value);
        }

        return $result;
    }


    // Insert another array at a chosen position.

    function insertAssociative(array $array, array $to_add, int $pos): array
    {

        return (array_slice($array, 0, $pos, true) + $to_add + array_slice($array, $pos, null, true));
    }


    // Obtain columns for array multisort.

    function multisortPrepare(array $array, SortByComponent $sort_by): array
    {

        $columns = [];
        $sort_by_data = $sort_by->getData();

        foreach ($sort_by_data as $field => $data) {

            $columns[$field] = array_column($array, $field);
        }

        return $columns;
    }


    // Multisort by columns.

    function sortByColumns(array &$array, array $array_columns, SortByComponent $sort_by, bool $preserve_keys = false): array
    {

        $volume = [];
        $sort_by_data = $sort_by->getData();

        foreach ($sort_by_data as $field => $data) {

            if (isset($data['order'])) {

                $volume[] = $array_columns[$field];
                $volume[] = constant('SORT_' . $data['order']->name);
            }
        }

        if (!empty($volume)) {

            if (!$preserve_keys) {
                $params = array_merge($volume, [&$array]);
            } else {
                $keys = array_keys($array);
                $params = array_merge($volume, [&$array], [&$keys]);
            }

            call_user_func_array('array_multisort', $params);

            if ($preserve_keys) {
                $array = array_combine($keys, $array);
            }
        }

        return $array;
    }


    /**
     * Recursively flattens a multidimensional array into a one-dimensional array.
     *
     * @param array $array The input array to be flattened.
     * @return array The resulting one-dimensional array.
     */
    function flatten(array $array): array
    {

        $result = [];

        foreach ($array as $value) {

            if (is_array($value)) {
                $result = array_merge($result, flatten($value));
            } else {
                $result[] = $value;
            }
        }

        return $result;
    }


    // Flattens a multidimensional array to a single dimension array by using array keys to construct a unique path key.

    function flattenWithKeyPath(mixed $data, callable $callback = null, string $prefix = null, array &$result = []): array
    {

        if (!is_array($data) && !is_object($data)) {

            $result[$prefix] = $data;

            return $result;

        } else {

            foreach ($data as $key => $val) {

                if (!is_callable($callback)) {

                    $next_prefix = ($prefix)
                        ? ($prefix . '[' . $key . ']')
                        : $key;

                } else {

                    $next_prefix = $callback($prefix, $key);

                    if (!is_string($next_prefix)) {
                        throw new \TypeError(sprintf("Callback in function \"%s\" must return a string type.", __FUNCTION__));
                    }
                }

                flattenWithKeyPath($val, $callback, $next_prefix, $result);
            }

            return $result;
        }
    }


    // Probes the given array by a path, eg. [foo][bar].
    // throws, because "null" is a valid return value

    function fetchByPath(array $array, string $path): mixed
    {

        #review: this should probably be replaced with string analysis solution
        preg_match_all('#\[[^\]]*[\]}]#', $path, $matches);

        foreach ($matches[0] as $match) {

            $match = trim($match, '[]');

            if (isset($array[$match])) {
                $array = &$array[$match];
            } else {
                throw new ElementNotFoundException("Array path \"$path\" was not found.");
            }
        }

        return $array;
    }


    // Checks if 2 progressing integers in each of the 2 arrays intersect, eg. [3,6] intersects with [4,8] whereas [3,6] does not intersect with [7,10].

    function isDualIntegerIntersecting(array $array1, array $array2): bool
    {

        $error_format_1 = "Parameter #%d in function \"%s\" must contain exactly 2 elements.";

        if (count($array1) != 2) {
            throw new \Error(sprintf($error_format_1, 1, __FUNCTION__));
        } elseif (count($array2) != 2) {
            throw new \Error(sprintf($error_format_1, 2, __FUNCTION__));
        }

        $array1 = array_values($array1);
        $array2 = array_values($array2);

        $error_format_2 = "Both elements in parameter #%d in function \"%s\" must be of integer type.";

        if (!is_integer($array1[0]) || !is_integer($array1[1])) {
            throw new \Error(sprintf($error_format_2, 1, __FUNCTION__));
        } elseif (!is_integer($array2[0]) || !is_integer($array2[1])) {
            throw new \Error(sprintf($error_format_2, 2, __FUNCTION__));
        }

        $error_format_3 = "First element in parameter #%d in function \"%s\" cannot be higher or equal to the second element.";

        if ($array1[0] > $array1[1]) {
            throw new \Error(sprintf($error_format_3, 1, __FUNCTION__));
        } elseif ($array2[0] > $array2[1]) {
            throw new \Error(sprintf($error_format_3, 2, __FUNCTION__));
        }

        return !($array2[0] > $array1[1] || $array2[1] < $array1[0]);
    }


    // Detaches chosen elements from the given array and returns a newly built array with detached values.

    function detachElements(array &$array, array $elements_to_detach): array
    {

        $result = [];

        foreach ($array as $key => $value) {

            if (in_array($key, $elements_to_detach)) {

                $result[$key] = $value;
                unset($array[$key]);
            }
        }

        return $result;
    }


    // Merge two arrays recursively by not preserving values for matching keys.

    function mergeRecursiveDistinct(array &$array1, array &$array2): array
    {

        $result = $array1;

        foreach ($array2 as $key => &$value) {

            if (is_array($value) && isset($result[$key]) && is_array($result[$key])) {
                $result[$key] = mergeRecursiveDistinct($result[$key], $value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }


    // Sort an array by key recursively

    function ksortRecursive(array &$array): array
    {

        foreach ($array as $key => &$value) {

            if (is_array($value)) {
                ksortRecursive($value);
            }
        }

        return ksort($array);
    }


    /**
     * Returns a closure used for sorting arrays based on a numeric element.
     *
     * @param string $element_name The name of the element to compare.
     * @return \Closure The closure used for sorting.
     */
    function getByNumericElementSorter(string $element_name): \Closure
    {

        return function (array $a, array $b) use ($element_name): int {
            if (!isset($a[$element_name]) && !isset($b[$element_name])) {
                return 0;
            } elseif (!isset($a[$element_name])) {
                return 1;
            } elseif (!isset($b[$element_name])) {
                return -1;
            } else {
                return ($a[$element_name] <=> $b[$element_name]);
            }
        };
    }


    /**
     * Sorts an array by a numeric element.
     *
     * @param array      &$array       The array to be sorted.
     * @param string|int $element_name The name or index of the element to compare.
     * @return void
     */
    function sortByNumericElement(array &$array, string|int $element_name): void
    {

        usort($array, getByNumericElementSorter($element_name));
    }


    /**
     * Checks if the values of two arrays match.
     *
     * The order of elements is not relevant.
     *
     * @param array $array1 The first array to compare.
     * @param array $array2 The second array to compare.
     * @return bool True if the values of both arrays match, false otherwise.
     */
    function valuesMatch(array $array1, array $array2): bool
    {

        return (!array_diff($array1, $array2) && !array_diff($array2, $array1));
    }


    /**
     * Generates a unique string not found in the given array.
     *
     * @param array  $array  An array of strings to check for uniqueness.
     * @param string $str    The base string to generate a unique string from.
     * @param int    $index  (Optional) The starting index for generating unique strings.
     *
     * @return string A unique string not present in the array.
     */
    function generateStringNotIn(array $array, string $str, int $index = 2): string
    {

        $new_str = $str;

        while (in_array($new_str, $array)) {
            $new_str = ($str . $index);
            $index++;
        }

        return $new_str;
    }
}

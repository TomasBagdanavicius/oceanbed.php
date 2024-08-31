<?php

declare(strict_types=1);

namespace LWP\Network\Uri;

use LWP\Common\Array\ArrayCollection;
use LWP\Network\Uri\Uri;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\addPreserved;

class SearchParams extends ArrayCollection implements \Stringable
{
    public function __construct(
        array $data = []
    ) {

        parent::__construct();

        if (!empty($data)) {
            $this->setMass($data);
        }
    }


    // Builds the query string.

    public function __toString(): string
    {

        return (!empty($this->data))
            ? http_build_query($this->data, '', ini_get('arg_separator.output'), PHP_QUERY_RFC3986)
            : '';
    }


    // Outputs the query string with the prepended URI query prefix.

    public function outputWithPrefix(): string
    {

        return (Uri::QUERY_COMPONENT_PREFIX . $this->__toString());
    }


    // Creates a new instance from string.

    public static function fromString(string $query_string): self
    {

        // Ignore leading question mark.
        if ($query_string != '' && $query_string[0] == Uri::QUERY_COMPONENT_PREFIX) {
            $query_string = substr($query_string, 1);
        }

        parse_str($query_string, $result);

        return new self($result);
    }


    // Sets a new pair value.

    public function set(int|string $key, mixed $element, array $context = [], int $pos = null): null|int|string
    {

        if ($this->containsKey($key)) {

            if (!is_array($element)) {

                addPreserved($this->data, $key, $element);

            } else {

                foreach ($element as $val) {

                    addPreserved($this->data, $key, $val);
                }
            }

            return $key;

        } else {

            return parent::set($key, $element, $context, $pos);
        }
    }


    // Adds a parameter with its value.

    public function add(mixed $element, array $context = []): ?int
    {

        if (is_array($element)) {

            if (empty($element)) {
                throw new \UnexpectedValueException("Array value cannot be empty.");
            }

            $keys = $this->setMass($element);
            $next_index_id = $keys[(count($keys) - 1)];

        } elseif (is_string($element)) {

            $next_index_id = parent::add($element);
        }

        return $next_index_id;
    }


    // Adds parameter without preserving the existing value(s), if any.

    public function replace(string $name, string|int|float $value): void
    {

        $this->data[$name] = $value;
    }


    // Sorts all parameters contained in this object.

    public function sort(): void
    {

        ksort($this->data);
    }


    // Merges an array of parameters into the object.

    public function merge(array $params): void
    {

        $this->data = array_merge_recursive($this->data, $params);
    }
}

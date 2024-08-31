<?php

declare(strict_types=1);

namespace LWP\Network;

use LWP\Common\Indexable;
use LWP\Common\Collectable;
use LWP\Common\Array\ArrayCollection;
use LWP\Common\String\Str;
use LWP\Common\String\Format;
use LWP\Common\Common;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\keyPrependToValue;
use function LWP\Common\Array\Arrays\addPreserved;
use function LWP\Common\Array\Arrays\splitToKey;

class Headers extends ArrayCollection implements Indexable, Collectable, \Stringable
{
    use \LWP\Common\Collections\CollectionStateTrait;
    use \LWP\Common\IndexableTrait;
    use \LWP\Common\CollectableTrait;


    public const FIELD_VALUE_SEPARATOR = ':';

    private int $size = 0;


    public function __construct(
        array $data = [],
    ) {

        parent::__construct();

        /* Add all data individually, because there are custom tasks when setting
        a new header, eg. making the key lowercase. This isn't bad practise, when
        normally a dataset isn't too big. */
        $this->setMass($data);
    }


    // Builds a string containing each header on a separate line.

    public function __toString(): string
    {

        $result = '';

        foreach ($this->data as $name => $value) {

            if (!is_array($value)) {

                $result .= ($name . self::FIELD_VALUE_SEPARATOR . ' ' . $value . "\r\n");

            } else {

                foreach ($value as $val) {

                    if (is_string($val)) {

                        $result .= ($name . self::FIELD_VALUE_SEPARATOR . ' ' . $val . "\r\n");
                    }
                }
            }
        }

        return $result;
    }


    // Gets the string length of all header lines.

    public function getSize(): int
    {

        return $this->size;
    }


    // Output the result to an array, which has header field names prepended to array values.

    public function toSequentialArray(): array
    {

        return keyPrependToValue($this->toArray());
    }


    // Creates a new object instance from a multi-line string.

    public static function fromString(string $headers_string): self
    {

        return new self(self::parse($headers_string));
    }


    // Checks if the element type is supported. This supports string and array elements.

    public static function assertElementType(string $function_name, mixed $element): void
    {

        if (!is_string($element) && !is_array($element)) {
            Common::throwTypeError(2, $function_name, 'string or array', gettype($element));
        }
    }


    // Sets a new header field.

    public function set(int|string $key, mixed $element, array $context = [], int $pos = null): null|int|string
    {

        self::assertElementType(__FUNCTION__, $element);

        // Will keep it lowercase, because array keys are case-sensitive.
        $key = strtolower($key);
        $contains_key = $this->containsKey($key);

        if (is_string($element)) {

            if ($contains_key) {

                addPreserved($this->data, $key, $element);
            }

            // +4 counts the field name value separator, whitespace, carriage return, newline.
            $this->size += (strlen($key) + strlen($element) + 4);

        } else {

            foreach ($element as $val) {

                $this->size += (strlen($key) + strlen($val) + 4);

                if ($contains_key) {

                    addPreserved($this->data, $key, $val);
                }
            }
        }

        return (!$contains_key)
            ? parent::set($key, $element, $context, $pos)
            : $key;
    }


    // This collection method is invalid in this class.

    public function add(mixed $element, array $context = []): null|int|string
    {

        throw new \LogicException(sprintf("Method \"add\" is not allowed in class %s", self::class));
    }


    // Gets string length for the given data.

    public static function getDataSize(int|string $key, string|array $element): int
    {

        $size = 0;
        // +4 counts the field name value separator, whitespace, carriage return, newline.
        $keylen = (strlen($key) + 4);

        if (is_string($element)) {

            $size += (strlen($element) + $keylen);

        } else {

            foreach ($element as $val) {
                $size += (strlen($val) + $keylen);
            }
        }

        return $size;
    }


    // Updates element value by a given index key.

    public function update(int|string $key, mixed $element): ?bool
    {

        self::assertElementType(__FUNCTION__, $element);

        if ($this->containsKey($key)) {
            $data_copy = $this->data[$key];
        }

        if ($result = parent::update($key, $element)) {
            $this->size -= self::getDataSize($key, $data_copy);
        }

        $this->size += self::getDataSize($key, $element);

        return $result;
    }


    // Removes an element by a given key name.

    public function remove(int|string $key): mixed
    {

        if ($removed = parent::remove($key)) {
            $this->size -= self::getDataSize($key, $removed);
        }

        return $removed;
    }


    // Completely empties the dataset.

    public function clear(): void
    {

        parent::clear();

        $this->size = 0;
    }


    // An alias function for the "containsKey" method.

    public function hasHeader(int|string $name): bool
    {

        return $this->containsKey($name);
    }


    // An alias function for the "get" method.

    public function getHeader(int|string $name): mixed
    {

        return $this->get($name);
    }


    // An alias function for the "set" method.

    public function setHeader(int|string $name, mixed $value): null|int|string
    {

        return $this->set($name, $value);
    }


    // Adds current date.

    public function addCurrentDate(): ?string
    {

        return $this->set('date', date('Y-m-d\TH:i:s'));
    }


    // A helper function to tell if content type is set to JSON MIME media type.

    public function isContentTypeJson(): bool
    {

        return ($this->containsKey('content-type') && substr($this->get('content-type'), 0, 16) == 'application/json');
    }


    /* Parsing */

    // Parses a single header field into field name and field value.

    public static function parseField(string $field): array
    {

        $separator_pos = strpos($field, self::FIELD_VALUE_SEPARATOR);

        if ($separator_pos === false) {

            throw new \Exception(sprintf("Invalid header field \"%s\": missing the required \"%s\" separator.", $field, self::FIELD_VALUE_SEPARATOR));
        }

        return [
            'name' => substr($field, 0, $separator_pos),
            'value' => trim(substr($field, ($separator_pos + 1))),
        ];
    }


    // Parses a multi-line headers string.

    public static function parse(string $headers_str): array
    {

        return splitToKey(Str::splitShortIntoLines(trim($headers_str)), self::FIELD_VALUE_SEPARATOR);
    }


    // Parses something similar to "Content-Disposition" or "Content-Type" header's field value.

    public static function parseContent(string $str, string $delimiter = ';'): array
    {

        $result = [];
        $parts = explode($delimiter, $str);

        foreach ($parts as $key => $part) {

            $part = trim($part);

            if ($part != '') {

                if ($key == 0) {

                    $result[0] = $part;

                } else {

                    $p = explode('=', $part);

                    // In case the value is incorrectly formatted.
                    $result[strtolower($p[0])] = (isset($p[1]))
                        ? Format::trimMatchingQuotes($p[1])
                        : null;
                }
            }
        }

        return $result;
    }


    // Parses "www-authenticate" header field's value.

    public static function parseWWWAuthenticate(string $field_value): array
    {

        // There are no scheme names that would contain a whitespace (https://www.iana.org/assignments/http-authschemes/http-authschemes.xhtml).
        $parts = explode(' ', $field_value, 2);

        $result = [
            'type' => $parts[0],
            'params' => [],
        ];

        if (isset($parts[1])) {

            $params = explode(',', $parts[1]);

            foreach ($params as $param) {

                $param = trim($param);

                if ($param != '') {

                    $p = explode('=', $param);

                    // In case the value is incorrectly formatted.
                    $result['params'][strtolower($p[0])] = (isset($p[1]))
                        ? Format::trimMatchingQuotes($p[1])
                        : null;
                }
            }
        }

        return $result;
    }


    // Gets indexable data representing this object.

    public function getIndexableData(): array
    {

        return $this->toArray();
    }


    //

    public function getIndexablePropertyList(): array
    {

        return array_keys($this->toArray());
    }
}

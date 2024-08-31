<?php

declare(strict_types=1);

namespace LWP\Common;

use LWP\Common\String\Str;
use LWP\Common\Exceptions\ConversionException;
use LWP\Common\Enums\BoundaryEnum;

class ComplianceComparison
{
    public static \Transliterator|string $transliterator = 'Any-Latin; Latin-ASCII';


    public function __construct(
        protected readonly mixed $primary_value,
        public bool $strict_type = false,
        public bool $case_sensitive = true,
        public bool $accent_sensitive = true,
    ) {

    }


    //

    public function isEqualTo(mixed $secondary_value): bool
    {

        if (is_string($this->primary_value) && is_string($secondary_value)) {

            return Str::compare($this->primary_value, $secondary_value, $this->case_sensitive, $this->accent_sensitive);

        } else {

            return (!$this->strict_type)
                ? ($this->primary_value == $secondary_value)
                : ($this->primary_value === $secondary_value);
        }
    }


    //

    public function isNotEqualTo(mixed $secondary_value): bool
    {

        return !$this->isEqualTo($secondary_value);
    }


    //

    public function isLessThan(mixed $secondary_value): bool
    {

        if ($this->strict_type && !self::twoTypesMatch($this->primary_value, $secondary_value, true)) {
            throw new \Exception("In strict type only elements of the same type can be compared");
        }

        return ($this->primary_value < $secondary_value);
    }


    //

    public function isGreaterThan(mixed $secondary_value): bool
    {

        if ($this->strict_type && !self::twoTypesMatch($this->primary_value, $secondary_value, true)) {
            throw new \Exception("In strict type only elements of the same type can be compared");
        }

        return ($this->primary_value > $secondary_value);
    }


    //

    public function isLessThanOrEqualTo(mixed $secondary_value): bool
    {

        if ($this->strict_type && !self::twoTypesMatch($this->primary_value, $secondary_value, true)) {
            throw new \Exception("In strict type only elements of the same type can be compared");
        }

        return ($this->primary_value <= $secondary_value);
    }


    //

    public function isGreaterThanOrEqualTo(mixed $secondary_value): bool
    {

        if ($this->strict_type && !self::twoTypesMatch($this->primary_value, $secondary_value, true)) {
            throw new \Exception("In strict type only elements of the same type can be compared");
        }

        return ($this->primary_value >= $secondary_value);
    }


    //

    public function contains(mixed $secondary_value): bool
    {

        if (is_string($this->primary_value) && is_string($secondary_value)) {

            $function_name_sensitive = 'mb_strpos';
            $function_name_insensitive = 'mb_stripos';
            $value_1 = $this->primary_value;
            $value_2 = $secondary_value;

            if (!$this->accent_sensitive) {

                $value_1 = transliterator_transliterate(self::$transliterator, $value_1);
                $value_2 = transliterator_transliterate(self::$transliterator, $value_2);
                $function_name_sensitive = 'strpos';
                $function_name_insensitive = 'stripos';
            }

            if ($this->case_sensitive) {
                return ($function_name_sensitive($value_1, $value_2) !== false);
            } else {
                return ($function_name_insensitive($value_1, $value_2) !== false);
            }

        } elseif (is_array($this->primary_value)) {

            if (is_object($secondary_value) || is_resource($secondary_value)) {
                throw new \Exception("Cannot check if object or resource is contained by an array");
            }

            if (!$this->primary_value) {
                return false;
            }

            foreach ($this->primary_value as $key => $value) {

                $instance = new self($value, $this->strict_type, $this->case_sensitive, $this->accent_sensitive);

                if ($instance->isEqualTo($secondary_value)) {
                    return true;
                }
            }

            return false;

        } else {

            if (
                !is_string($this->primary_value)
                && !Str::canConvertToString($this->primary_value)
            ) {
                throw new ConversionException("Primary value cannot be converted to string for comparison");
            }

            $value_1 = (string)$this->primary_value;

            if (
                !is_string($secondary_value)
                && !Str::canConvertToString($secondary_value)
            ) {
                throw new ConversionException("Secondary value cannot be converted to string for comparison");
            }

            if ($this->strict_type && !self::typesMatch($this->primary_value, $secondary_value)) {
                return false;
            }

            $value_2 = (string)$secondary_value;

            return (mb_strpos($value_1, $value_2) !== false);

        }
    }


    //

    public function doesNotContain(mixed $secondary_value): bool
    {

        return $this->contains($secondary_value);
    }


    //

    public function inBoundary(BoundaryEnum $boundary, mixed $secondary_value): bool
    {

        if (is_string($this->primary_value) && is_string($secondary_value)) {

            $value1 = $this->primary_value;
            $value2 = $secondary_value;
            $value1_len = mb_strlen($value1);
            $value2_len = mb_strlen($value2);

            if ($value2_len > $value1_len) {
                return false;
            }

            if (!$this->accent_sensitive) {

                $value1 = transliterator_transliterate(self::$transliterator, $value1);
                $value2 = transliterator_transliterate(self::$transliterator, $value2);

                if ($boundary === BoundaryEnum::START) {

                    return (substr_compare(
                        $value1,
                        $value2,
                        offset: 0,
                        length: $value2_len,
                        case_insensitive: !$this->case_sensitive
                    ) === 0);

                } else {

                    return (substr_compare(
                        $value1,
                        $value2,
                        offset: -$value2_len,
                        case_insensitive: !$this->case_sensitive
                    ) === 0);
                }

            } else {

                $substr = ($boundary === BoundaryEnum::START)
                    ? mb_substr($value1, 0, $value2_len)
                    : mb_substr($value1, -$value2_len);

                return ($this->case_sensitive)
                    ? ($substr === $value2)
                    : (strcasecmp($substr, $value2) === 0);
            }

        } elseif (is_array($this->primary_value)) {

            if (is_object($secondary_value) || is_resource($secondary_value)) {
                throw new \Exception("Cannot check if object or resource is contained by an array");
            }

            if (!$this->primary_value) {
                return false;
            }

            $instance = new self(
                $this->primary_value[array_key_first($this->primary_value)],
                $this->strict_type,
                $this->case_sensitive,
                $this->accent_sensitive
            );

            return $instance->isEqualTo($secondary_value);
        }
    }


    //

    public function startsWith(mixed $secondary_value): bool
    {

        return $this->inBoundary(BoundaryEnum::START, $secondary_value);
    }


    //

    public function endsWith(mixed $secondary_value): bool
    {

        return $this->inBoundary(BoundaryEnum::END, $secondary_value);
    }


    //

    public static function twoTypesMatch(mixed $value1, mixed $value2, bool $ignore_int_float = false): bool
    {

        $value1_type = gettype($value1);
        $value2_type = gettype($value2);

        if ($value1_type === $value2_type) {
            return true;
        } elseif ($ignore_int_float && ($value1_type === 'integer' || $value1_type === 'double') && ($value2_type === 'integer' || $value2_type === 'double')) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Checks if all parameters have the same type.
     *
     * @param mixed ...$params An infinite number of parameters to check.
     * @return bool True if all parameters have the same type, false otherwise.
     */
    public static function typesMatch(...$params): bool
    {

        $types = [];

        foreach ($params as $param) {
            $types[] = gettype($param);
        }

        return (count(array_unique($types)) === 1);
    }
}

<?php

declare(strict_types=1);

namespace LWP\Common;

use LWP\Common\Enums\StandardOrderEnum;
use LWP\Common\Enums\KeyValuePairEnum;
use LWP\Common\Exceptions\ClassNotFoundException;
use LWP\Common\Exceptions\NotFoundException;

class Common
{
    //

    public static function throwTypeError(
        int $arg_num,
        string $func_name,
        string $required_type,
        string $given_type
    ): never {

        throw new \TypeError(sprintf(
            "Argument #%d passed to %s() must be of the type %s, %s given",
            $arg_num,
            $func_name,
            $required_type,
            $given_type
        ));
    }


    //

    public static function assertClassNameExistence(string $class_name, bool $autoload = true): void
    {

        if (!class_exists($class_name, $autoload)) {
            throw new ClassNotFoundException(sprintf(
                "Class \"%s\" was not found",
                $class_name
            ));
        }
    }


    //

    public static function assertSubClass(string $main_class_name, string $parent_class_name): void
    {

        if (!is_subclass_of($main_class_name, $parent_class_name)) {
            throw new \Exception(sprintf(
                "Class %s is not a subclass of %s",
                $main_class_name,
                $parent_class_name
            ));
        }
    }


    //

    public static function assertEnumClass(string $class_name)
    {

        if (!enum_exists($class_name)) {
            throw new NotFoundException(sprintf(
                "Enum %s was not found",
                $class_name
            ));
        }
    }


    //

    public static function getNamespaceDirname(string $namespace_name): string
    {

        $divider_pos = strrpos($namespace_name, '\\');

        return ($divider_pos !== false)
            ? substr($namespace_name, 0, $divider_pos)
            : $namespace_name;
    }


    //

    public static function getNamespaceBasename(string $namespace_name): string
    {

        $divider_pos = strrpos($namespace_name, '\\');

        return ($divider_pos !== false)
            ? substr($namespace_name, ($divider_pos + 1))
            : $namespace_name;
    }


    //

    public static function toNumber(string $val): false|int|float
    {

        if (!is_numeric($val)) {
            return false;
        }

        return ($val == (int)$val)
            ? (int)$val
            : (float)$val;
    }


    // Finds enum case in a given enum class
    // The built-in `BackedEnum::tryFrom` method searches by enum value. Also, this function is required, when enum class does not have helper trait included.

    public static function findEnumCase(
        string $enum_class,
        string $name,
        bool $case_sensitive = true,
        KeyValuePairEnum $element = KeyValuePairEnum::KEY
    ): ?\UnitEnum {

        self::assertEnumClass($enum_class);
        $cases = $enum_class::cases();
        $element = match ($element) {
            KeyValuePairEnum::KEY => 'name',
            KeyValuePairEnum::VALUE => 'value'
        };

        foreach ($cases as $case) {

            if (
                // Case-sensitive
                ($case_sensitive && $case->{$element} == $name)
                // Case-insensivite
                || (!$case_sensitive && strcasecmp($case->{$element}, $name) === 0)
            ) {
                return $case;
            }
        }

        return null;
    }


    // Adds additional data into the universal data payload

    public static function addToUniversalPayload(array &$payload, array $result, bool $preserve_keys = false): void
    {

        if ($result) {

            if (isset($result['status'])) {
                $payload['status'] = $result['status'];
            }

            foreach ($result['data'] as $name => $data) {

                if (isset($payload['data'][$name])) {

                    if (!$preserve_keys) {
                        $payload['data'][$name] = [...$payload['data'][$name], ...$data];
                    } else {
                        $payload['data'][$name] += $data;
                    }

                } else {

                    $payload['data'][$name] = $data;
                }
            }
        }
    }


    //

    public static function applyModifiers(mixed $value, array $modifiers): void
    {

        foreach ($modifiers as $modifier) {

            $closure = null;
            $closure_params = [];

            if (is_array($modifier)) {
                $closure = ($modifier['closure'] ?? null);
                $closure_params = ($modifier['params'] ?? []);
            } elseif ($modifier instanceof \Closure) {
                $closure = $modifier;
            }

            if ($closure) {
                $closure($value, ...$closure_params);
            }
        }
    }
}

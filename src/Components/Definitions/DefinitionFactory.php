<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Components\Definitions\Exceptions\DefinitionNotFoundException;

class DefinitionFactory
{
    //

    public static function createNew(
        string $name,
        string|int|float|array|bool|null|\Closure $value
    ): Definition {

        return new (self::getDefinitionClassName($name))($value);
    }


    //

    public static function definitionNameToCamelcase(string $name): string
    {

        return implode(array_map('ucfirst', explode('_', strtolower($name))));
    }


    //

    public static function getDefinitionClassName(string $name): ?string
    {

        self::assertDefinitionExistence($name);

        return (__NAMESPACE__
            . '\\'
            . self::definitionNameToCamelcase($name)
            . 'Definition');
    }


    //

    public static function assertDefinitionExistence(string $name): void
    {

        if (!self::definitionExists($name)) {
            throw new DefinitionNotFoundException(sprintf("Definition \"%s\" was not found", $name));
        }
    }


    //

    public static function definitionExists(string $name): bool
    {

        return in_array(
            $name,
            self::getFullSupportedDefinitionList()
        );
    }


    //

    public static function getFullSupportedDefinitionList(): array
    {

        return [
            /* Main */
            'type', // Primary
            'default',
            'allow_empty',
            'nullable',
            'required',
            'readonly',
            /* Constraints */
            'min',
            'max',
            'range',
            'in_set', // Primary
            'not_in_set', // Primary
            'charset',
            'set_access',
            /* Formatting rules */
            'join', // Primary
            'pre_trim',
            'trim',
            'format',
            'number_format',
            'pre_number_format',
            'tagname',
            'calc',
            /* Model based */
            'alias', // Primary
            'match',
            'mismatch',
            'groups',
            'dependencies',
            'searchable',
            /* Shared Amounts */
            'max_sum',
            'min_sum',
            'required_count',
            'max_chars',
            'min_chars',
            /* Dataset based */
            'unique',
            'relationship',
            'virtual',
            /* Misc */
            'title',
            'description',
        ];
    }
}

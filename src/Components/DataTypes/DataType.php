<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes;

use LWP\Common\Common;
use LWP\Components\Constraints\Exceptions\UnsupportedConstraintException;
use LWP\Components\DataTypes\DataTypeConverter;

abstract class DataType
{
    //

    abstract public static function getSupportedConstraintClassNameList(): ?array;


    //

    abstract public static function getSupportedDefinitionList(): ?array;


    //

    abstract public static function hasBuilder(): bool;


    //

    public static function getConverterClassName(): string
    {

        return (Common::getNamespaceDirname(static::class)
            . '\To'
            . Common::getNamespaceBasename(static::class)
            . 'Converter');
    }


    //

    public static function getConverterClassObject(): DataTypeConverter
    {

        return new (self::getConverterClassName())();
    }


    //

    public static function getValidatorClassName(): string
    {

        return (static::class . 'Validator');
    }


    //

    public static function getValidatorClassObject(mixed $value, array $other_constructor_params = []): DataTypeValidator
    {

        return new (static::getValidatorClassName())($value, ...$other_constructor_params);
    }


    //

    public static function getBuilderClassName(): string
    {

        return (static::class . 'Builder');
    }


    //

    public static function getValueDescriptorClassName(): string
    {

        return (static::class . 'ValueDescriptor');
    }


    //

    public static function getValueContainerClassName(): string
    {

        return (static::class . 'ValueContainer');
    }


    //

    public static function getDefinition(): array
    {

        return [
            'type' => static::TYPE_NAME,
        ];
    }


    //

    public static function isSupportedConstraint(string $constraint_class_name): ?bool
    {

        if (!$supported_constraint_list = static::getSupportedConstraintClassNameList()) {
            return null;
        }

        return in_array($constraint_class_name, $supported_constraint_list);
    }


    //

    public static function isSupportedFormattingRule(string $formatting_rule_class_name): ?bool
    {

        if (!$supported_formatting_rule_list = static::getSupportedFormattingRuleList()) {
            return null;
        }

        return in_array($formatting_rule_class_name, $supported_formatting_rule_list);
    }


    //

    public static function assertConstraint(string $constraint_class_name): bool
    {

        if (!self::isSupportedConstraint($constraint_class_name)) {
            throw new UnsupportedConstraintException(sprintf(
                "Constraint \"%s\" is not supported.",
                $constraint_class_name
            ));
        }

        return true;
    }


    //

    public static function testEmpty(mixed $value): bool
    {

        return false;
    }
}

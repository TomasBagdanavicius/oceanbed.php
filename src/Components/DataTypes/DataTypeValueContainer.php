<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes;

use LWP\Common\String\Str;
use LWP\Components\DataTypes\DataType;
use LWP\Components\DataTypes\DataTypeValueConstraintValidator;
use LWP\Components\Definitions\DefinitionCollection;
use LWP\Common\Exceptions\NotFoundException;
use LWP\Components\Rules\FormattingRule;
use LWP\Components\Rules\Exceptions\UnsupportedFormattingRuleException;
use LWP\Components\Constraints\ConstraintCollection;
use LWP\Components\DataTypes\Custom\DateTime\DateTimeDataTypeValueContainer;
use LWP\Components\DataTypes\DataTypeValueDescriptor;
use LWP\Components\DataTypes\Interfaces\ConstructorAcceptsFormattingRuleInterface;
use LWP\Common\Exceptions\FormatError;
use LWP\Components\Rules\Exceptions\FormattingRuleModificationException;
use LWP\Common\Enums\ValidityEnum;

abstract class DataTypeValueContainer
{
    protected ?object $parser = null;


    public function __construct(
        protected mixed $value,
        protected ?DataTypeValueDescriptor $value_descriptor = null
    ) {

    }


    // Subclasses should define return type for strictness.

    abstract public function getValue(): mixed;


    //

    public function getDescriptor(): ?DataTypeValueDescriptor
    {

        return $this->value_descriptor;
    }


    // Expecting all data types to have parsers. When custom parser is used, this function should be created in subclass.

    public function getParser(): object // Can be external class object, eg. DateTime or NumberDataTypeParser, etc.
    {if (!$this->parser) {
        $this->parser = new (self::getGenericParserClassName())($this);
    }

        return $this->parser;
    }


    // Gets data type descriptor's fully qualified class name.

    public static function getDescriptorClassName(): string
    {

        return Str::rtrimSubstring(static::class, 'ValueContainer');
    }


    // Gets data type descriptor's class object.

    public static function getDescriptorClassObject(): DataType
    {

        return new (self::getDescriptorClassName())();
    }


    // Builds generic parser class name with no strings attached.

    public static function getGenericParserClassName(): string
    {

        return (self::getDescriptorClassName() . 'Parser');
    }


    // Gets data type constraint validator's fully qualified class name.

    public static function getConstraintValidatorClassName(): string
    {

        return (self::getDescriptorClassName() . 'ValueConstraintValidator');
    }


    // Gets data type constraint validator's class object.

    public function getConstraintValidatorClassObject(
        ?ConstraintCollection $constraint_collection = null,
        ?int $opts = DataTypeValueConstraintValidator::THROW_ERROR_IMMEDIATELLY
    ): DataTypeValueConstraintValidator {

        return new (self::getConstraintValidatorClassName())($this, $constraint_collection, $opts);
    }


    // Modifies this value by a chosen formatting rule and returns a fresh instance of static object.

    public function modifyByFormattingRule(FormattingRule $formatting_rule): static
    {

        $data_type_descriptor_class_object = static::getDescriptorClassObject();

        if (!$data_type_descriptor_class_object::isSupportedFormattingRule($formatting_rule::class)) {
            throw new UnsupportedFormattingRuleException(sprintf(
                "Formatting rule %s is not supported with \"%s\" data type.",
                $formatting_rule::class,
                $data_type_descriptor_class_object::TYPE_NAME
            ));
        }

        // When value descriptor is available, check if this value hasn't been already formatted by a matching rule.
        if ($this->value_descriptor && $this->value_descriptor->hasMatchingFormattingRule($formatting_rule)) {
            return $this;
        }

        $formatter = $formatting_rule->getFormatter();

        if (!$formatter->canFormat($this->value)) {
            throw new UnsupportedFormattingRuleException(sprintf(
                "Formatting rule %s cannot format given \"%s\" data type.",
                $formatting_rule::class,
                $data_type_descriptor_class_object::TYPE_NAME
            ));
        }

        try {
            $re_formatted_value = $formatter->format($this->value);
        } catch (FormatError $exception) {
            throw new FormattingRuleModificationException(sprintf(
                "Could not modify by formatting rule %s: %s",
                $formatting_rule::class,
                $exception->getMessage()
            ), previous: $exception);
        }

        $this->value = $re_formatted_value;

        if (!$this->value_descriptor) {
            $value_descriptor_class_name = self::getDescriptorClassName()::getValueDescriptorClassName();
            $this->value_descriptor = new $value_descriptor_class_name(ValidityEnum::VALID);
        }

        $this->value_descriptor->addFormattingRule($formatting_rule);

        return $this;

        #review: if no longer needed, remove
        // If constructor accepts formatting rule, provide it.
        /* return ( is_subclass_of(static::class, ConstructorAcceptsFormattingRuleInterface::class) )
            ? new static($re_formatted_value, formatting_rule: $formatting_rule)
            : new static($re_formatted_value); */
    }


    // Asserts a definition array against current data type.

    public static function fromDefinitionArray(array $definition_array, mixed $value): self
    {

        return self::fromDefinitionCollection(DefinitionCollection::fromArray($definition_array), $value);
    }


    // Asserts a definition collection against current data type.

    public static function fromDefinitionCollection(DefinitionCollection $definition_collection, mixed $value): self
    {

        if (!$definition_collection->containsKey('type')) {
            throw new NotFoundException("Missing the \"type\" definition in the definition collection.");
        } elseif ($definition_collection->get('type')->getValue() != static::getDescriptorClassName()::TYPE_NAME) {
            throw new \Exception(sprintf("Type definition must be set to \"%s\" value in the definition collection.", static::getDescriptorClassName()::TYPE_NAME));
        }

        return new static($value);
    }


    // A non-static router method for "isEmptyStatic".

    public function isEmpty(): bool
    {

        return self::isEmptyStatic($this->value);
    }


    // Tells if current value can be treated as empty value.

    public static function isEmptyStatic(mixed $value): bool
    {

        return static::getDescriptorClassObject()::testEmpty($value);
    }


    //

    public static function getConvertFormattingRuleClassName(): ?string
    {

        return null;
    }
}

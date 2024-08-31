<?php

declare(strict_types=1);

namespace LWP\Components\Properties;

use LWP\Common\Indexable;
use LWP\Common\Collectable;
use LWP\Common\Enums\ValidityEnum;
use LWP\Common\Exceptions\AbortException;
use LWP\Common\Exceptions\ApplicationAbortException;
use LWP\Common\Exceptions\ReadOnlyException;
use LWP\Common\Exceptions\NotFoundException;
use LWP\Components\DataTypes\DataTypeValueContainer;
use LWP\Components\Attributes\NoDefaultValueAttribute;
use LWP\Components\Rules\FormattingRule;
use LWP\Components\Definitions\DefinitionCollection;
use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;
use LWP\Components\DataTypes\Natural\NaturalDataTypeFactory;
use LWP\Components\DataTypes\DataTypeFactory;
use LWP\Components\Rules\FormattingRuleCollection;
use LWP\Components\DataTypes\Exceptions\DataTypeConversionException;
use LWP\Components\DataTypes\Natural\Null\NullDataTypeValueContainer;
use LWP\Components\Rules\Exceptions\UnsupportedFormattingRuleException;
use LWP\Components\Properties\Exceptions\PropertyValueSetException;
use LWP\Components\Properties\Exceptions\PropertyDefaultValueNotAvailableException;
use LWP\Components\Definitions\Exceptions\UnsupportedDefinitionCollectionException;
use LWP\Components\Model\AbstractModel;
use LWP\Components\Properties\Exceptions\PropertyNotFoundException;
use LWP\Components\DataTypes\DataTypeConverter;

class BaseProperty implements Indexable, Collectable
{
    use \LWP\Common\IndexableTrait;
    use \LWP\Common\CollectableTrait;
    use HooksTrait;


    // Main storage for the value.
    protected mixed $value;
    protected DataTypeValueContainer $value_container;
    /* Type checking will not be performed. This is to identify what data type
    was used for storing. */
    public readonly string $data_type_class_name;
    public readonly string $data_type_descriptor_class_name;
    public readonly DataTypeConverter $data_type_converter;
    // Post-formatting rules in general context.
    protected FormattingRuleCollection $formatting_rule_collection;
    // Resolved default value.
    protected mixed $default_value;
    protected DataTypeValueContainer $default_value_container;


    public function __construct(
        public readonly string $property_name,
        public readonly string $data_type_name,
        protected DataTypeValueContainer|\Closure|NoDefaultValueAttribute $default_value_obligation = new NoDefaultValueAttribute(),
        public readonly bool $readonly = false,
        public ?string $description = null,
        public ?string $title = null
    ) {

        $this->data_type_class_name = DataTypeFactory::getDataTypeValueClassNameByTypeName($data_type_name);
        $this->data_type_descriptor_class_name = $this->data_type_class_name::getDescriptorClassName();
        $this->data_type_converter = $this->data_type_descriptor_class_name::getConverterClassObject();

        if ($default_value_obligation instanceof DataTypeValueContainer) {

            // Data type conversion is not feasible.
            if (!$this->data_type_converter::canConvertFrom($default_value_obligation)) {

                throw new DataTypeConversionException(sprintf(
                    "Invalid default value data type (%s): value cannot be converted to \"%s\" type.",
                    $data_type_name,
                    $default_value_obligation::class
                ));
            }
        }
    }


    // Attempts to set property value using params that can be accessed publicly.

    public function setValue(mixed $value): mixed
    {

        return self::setValueInternal($value);
    }


    // Sets the value internally.

    protected function setValueInternal(mixed $value, array $options = []): mixed
    {

        $default_options = [
            'readonly' => true,
            'validate_data_type' => true,
            'nullable' => true
        ];
        $options = [...$default_options, ...$options];

        if ($options['readonly'] === true && $this->readonly) {

            $has_any_value = ($this->hasMainValue() || $this->hasDefaultValue());

            if ($has_any_value) {
                throw new ReadOnlyException(sprintf(
                    "Cannot modify existing value of a read-only property \"%s\"",
                    $this->property_name
                ));
            }
        }

        $is_data_type_value_container = ($value instanceof DataTypeValueContainer);

        // Validates data type
        if (
            // Backed up by option
            $options['validate_data_type'] === true
            // Non-null
            && ($options['nullable'] && $value !== null || !$options['nullable'])
            // Descriptor is indicating that it's valid
            #review: does validity matter when validating data type?
            #&& (!$is_data_type_value_container || ((($descriptor = $value->getDescriptor()) && $descriptor->validity !== ValidityEnum::VALID) || !$descriptor))
        ) {

            $params = [
                $value
            ];
            $formatting_rule_for_convert = $this->getDataTypeConvertFormattingRule();

            if ($formatting_rule_for_convert) {
                $params['formatting_rule'] = $formatting_rule_for_convert;
            }

            $data_type_value_container = $this->data_type_converter::convert(...$params);

            // Convert to strict type
            if (!$is_data_type_value_container) {
                $value = $data_type_value_container->getValue();
            }
        }

        $set_value_handle = $this->getSetValueCallbacksHandle();

        /* There's nothing odd about both callback sets being fired immediatelly
        one after another. Method "setValue" in "BaseProperty" doesn't have any
        tasks to run in between. */
        $set_value_handle('fireOnBeforeSetValueCallbacks', $value);
        $set_value_handle('fireOnAfterSetValueCallbacks', $value);

        if ($is_data_type_value_container) {
            $this->setupValue($value->getValue(), value_container: $value);
        } else {
            /* Will not build the value container. The policy is to build it
            on-demand when needed using "setupValueContainer". */
            $this->setupValue($value);
        }

        return $this->value;
    }


    // Run checks for when attempting to set value through private channel.

    protected function assertValuePrivate(AbstractModel $host_model, mixed $value, array $options = []): void
    {

        if (!$host_model->propertyExists($this->property_name)) {
            throw new \RuntimeException(
                "Illegal attempt to set value through private channel: property is not assigned to model."
            );
        }

        if ($host_model->getPropertyByName($this->property_name) !== $this) {
            throw new \RuntimeException(
                "Illegal attempt to set value through private channel: property not found in model."
            );
        }
    }


    // Attempts to set the given value through a private channel.

    public function setValuePrivate(AbstractModel $host_model, mixed $value, array $options = []): mixed
    {

        self::assertValuePrivate($host_model, $value, $options);

        return static::setValueInternal($value, $options);
    }


    // Gets a handler that will fire a given hook and manage the value that comes out of it.

    protected function getSetValueCallbacksHandle(): \Closure
    {

        return function (
            string $method_name,
            mixed &$value,
            \Closure $on_value_mismatch = null
        ): void {

            $saved_value = $value;

            try {
                $value = $this->{$method_name}($value);
                // "AbortException" is in the list just to be mentioned.
            } catch (AbortException|\Throwable $exception) {
                throw new PropertyValueSetException(
                    sprintf(
                        "Value for property \"%s\" could not be set: aborted by application",
                        $this->property_name
                    ),
                    previous: $exception
                );
            }

            // Strict match is required.
            if ($value !== $saved_value) {

                $run_converter = true;

                if ($on_value_mismatch) {
                    $run_converter = $on_value_mismatch($value, $saved_value);
                }

                if ($run_converter) {

                    if (!$this->data_type_converter::canConvertFrom($value)) {
                        throw new \RuntimeException(sprintf(
                            "Filtered value must match the original data type \"%s\"",
                            $this->data_type_name
                        ));
                    }

                    /* Value as "DataTypeValueContainer" is not stricly needed, but since converter is run above, all these resources can be efficiently used to generate a value container. */
                    $value = $this->data_type_converter::convert($value);
                }
            }
        };
    }


    // Arranges all things that establish a qualified value entity.

    protected function setupValue(
        mixed $value,
        ?DataTypeValueContainer $value_container = null
    ): void {

        $this->value = $value;

        if ($value_container !== null) {
            $this->value_container = $value_container;
            // Removes existing value container if its value does not match the new one
        } elseif (isset($this->value_container) && $this->value_container->getValue() !== $value) {
            unset($this->value_container);
        }
    }


    // Ensures a proper value container is set up.

    protected function setupValueContainer(): DataTypeValueContainer
    {

        if (isset($this->value_container)) {
            return $this->value_container;
        }

        $data_type_value_container = ($this->value !== null)
            ? $this->data_type_converter::convert($this->value)
            : new NullDataTypeValueContainer();

        $this->setupValue($this->value, $data_type_value_container);

        return $data_type_value_container;
    }


    // Tells if the main value was set without any resolution activities.

    public function hasMainValue(): bool
    {

        /* "isset" checking is insufficient, because in this program "null" is considered a valid value. Essentially, the goal is to determine if value is defined. */
        try {

            $this->value;
            return true;

            // Eg. "Typed property BaseProperty::$value must not be accessed before initialization".
        } catch (\Error) {

            return false;
        }
    }


    // Alias of "hasMainValue".

    public function hasValue(): bool
    {

        return $this->hasMainValue();
    }


    // Tells if default value is available by attempting to resolve it.

    public function hasDefaultValue(): bool
    {

        try {
            $this->getDefaultValue();
        } catch (PropertyDefaultValueNotAvailableException) {
            return false;
        }

        return true;
    }


    // Dissolves the main value.

    public function unsetMainValue(): void
    {

        unset($this->value, $this->value_container);

        $this->fireOnUnsetValueCallbacks();
    }


    // Alias of "unsetMainValue".

    public function unsetValue(): void
    {

        $this->unsetMainValue();
    }


    // Unsets the default value.

    protected function unsetDefaultValue(): void
    {

        unset($this->default_value, $this->default_value_container);

        $this->default_value_obligation = new NoDefaultValueAttribute();
    }


    // Gets the main value without value resolution.

    public function getMainValue(): mixed
    {

        if ($this->hasMainValue()) {
            return $this->value;
        } else {
            throw new Exceptions\PropertyValueNotSetException(sprintf(
                "Value for property \"%s\" is not set.",
                $this->property_name
            ));
        }
    }


    // Resolves the value (eg. if no base value is set, checks if default value is available, etc.).

    public function getValue(): mixed
    {

        $this->fireOnBeforeGetValueCallbacks();

        // Using "try catch" block instead of simple "hasMainValue" condition in order to, when necessary, capture the exception and add it to "previous" exception.
        try {

            $value = $this->getMainValue();

        } catch (Exceptions\PropertyValueNotSetException $exception) {

            if ($this->hasDefaultValueOpportunity()) {

                try {
                    return $this->getDefaultValue();
                } catch (PropertyDefaultValueNotAvailableException $exception) {
                    // Save exception and continue.
                }
            }

            throw new Exceptions\PropertyValueNotAvailableException(
                "Value for property \"{$this->property_name}\" is not available.",
                previous: $exception
            );
        }

        /* Post Formatting */

        if ($this->hasFormattingRuleCollection()) {

            try {

                // Setup value container on-demand.
                $data_type_value_container = $this->setupValueContainer();

            } catch (DataTypeConversionException $exception) {

                throw new Exceptions\PropertyValueNotAvailableException(
                    "Value for property \"$this->property_name\" is not available",
                    previous: $exception
                );
            }

            foreach ($this->formatting_rule_collection as $formatting_rule) {

                try {
                    $data_type_value_container = $data_type_value_container->modifyByFormattingRule($formatting_rule);
                    /* #review: Normally, unsupported formatting rules shouldn't appear in "formatting_rule_collection", but leaving the filter since there are sub-classes that also have access to "formatting_rule_collection". */
                } catch (UnsupportedFormattingRuleException) {
                    // Continue
                }
            }

            $value = $data_type_value_container->getValue();
        }

        return $this->fireOnAfterGetValueCallbacks($value);
    }


    // Tells if default value resolution is possible.
    /* If true, that does not necessarily mean that a valid default value is available. */

    public function hasDefaultValueOpportunity(): bool
    {

        return !($this->default_value_obligation instanceof NoDefaultValueAttribute);
    }


    // Handles a default value that is provided as a closure object.

    protected function resolveClosureAsDefaultValueProvider(
        \Closure $closure,
        bool $return_on_null = false
    ): DataTypeValueContainer|NoDefaultValueAttribute|\Exception {

        try {

            // Keep only the callback call line in the "try" block, because the "catch" block will capture all exceptions and we don't want any noise from other code.
            $application_default_value = ($closure)($this);

            // Application exception.
        } catch (\Throwable $exception) {

            return new ApplicationAbortException(
                "Could not determine default value for property \"$this->property_name\" due to application error.",
                previous: $exception
            );
        }

        if ($return_on_null && $application_default_value === null) {
            return new NullDataTypeValueContainer();
        }

        if ($application_default_value instanceof NoDefaultValueAttribute) {
            return $application_default_value;
        }

        if (!$this->data_type_converter::canConvertFrom($application_default_value)) {

            return new \RuntimeException(
                "Application provided default value cannot be converted to \"$this->data_type_name\" type."
            );
        }

        if ($application_default_value instanceof DataTypeValueContainer) {
            return $application_default_value;
        }

        return $this->data_type_converter::convert($application_default_value);
    }


    // Sets up default value entity.

    protected function setupDefaultValue(DataTypeValueContainer $data_type_value_container): mixed
    {

        $this->default_value = $data_type_value_container->getValue();
        $this->default_value_container = $data_type_value_container;

        return $this->default_value;
    }


    // Resolves the default value.

    public function getDefaultValue(): mixed
    {

        try {

            $default_value = $this->default_value;
            return $default_value;

        } catch (\Error) {

            // Continue.
        }

        if ($this->hasDefaultValueOpportunity()) {

            if ($this->default_value_obligation instanceof \Closure) {

                $closure_resolution = $this->resolveClosureAsDefaultValueProvider($this->default_value_obligation);

                if (!($closure_resolution instanceof \Throwable)) {
                    return $this->setupDefaultValue($closure_resolution);
                } else {
                    $exception = $closure_resolution;
                }

            } elseif ($this->default_value_obligation instanceof DataTypeValueContainer) {

                return $this->setupDefaultValue($this->default_value_obligation);
            }
        }

        throw new PropertyDefaultValueNotAvailableException(
            "Default value for property \"{$this->property_name}\" is not available.",
            previous: ($exception ?? null)
        );
    }


    // Gets the formatting rule collection.

    public function getFormattingRuleCollection(): ?FormattingRuleCollection
    {

        return ($this->formatting_rule_collection ?? null);
    }


    // Checks if given formatting rule is supported.

    public function assertFormattingRuleSupport(FormattingRule $formatting_rule): void
    {

        if (!$this->data_type_descriptor_class_name::isSupportedFormattingRule($formatting_rule::class)) {

            throw new UnsupportedFormattingRuleException(sprintf(
                "Formatting rule %s is not supported with \"%s\" data type.",
                $formatting_rule::class,
                $this->data_type_descriptor_class_name::TYPE_NAME
            ));
        }
    }


    // Registers a given formatting rule.

    public function setFormattingRule(FormattingRule $formatting_rule): void
    {

        $this->assertFormattingRuleSupport($formatting_rule);

        $this->formatting_rule_collection ??= new FormattingRuleCollection();
        $this->formatting_rule_collection->add($formatting_rule);
    }


    // Tells if given formatting rule class name is registered.

    public function hasFormattingRule(string $formatting_rule_class): bool
    {

        return ($this->hasFormattingRuleCollection() && $this->formatting_rule_collection->containsKey($formatting_rule_class));
    }


    // Tells if a non-empty formatting rule collection is present.

    public function hasFormattingRuleCollection(): bool
    {

        return (
            isset($this->formatting_rule_collection)
            && $this->formatting_rule_collection->count()
        );
    }


    // Returns formatting rule of the property's data type that can be used when running data type conversion.

    public function getDataTypeConvertFormattingRule(): ?FormattingRule
    {

        $formatting_rule_for_convert = $this->data_type_class_name::getConvertFormattingRuleClassName();

        if (
            $formatting_rule_for_convert
            && isset($this->formatting_rule_collection)
            && ($formatting_rule = $this->formatting_rule_collection->get($formatting_rule_for_convert))
        ) {
            return $formatting_rule;
        }

        return null;
    }


    //

    public function getIndexablePropertyList(): array
    {

        return [
            'name',
            'data_type',
            'readonly',
        ];
    }


    // Returns value of a given indexable property.

    public function getIndexablePropertyValue(string $property_name): mixed
    {

        $this->assertIndexablePropertyExistence($property_name);

        return match ($property_name) {
            'name' => $this->property_name,
            'data_type' => $this->data_type_name,
            'readonly' => $this->readonly,
        };
    }


    // Imports formatting rules into a given property from a definition collection.

    public static function setFormattingRulesFromDefinitionCollection(
        DefinitionCollection $definition_collection,
        self &$property
    ): void {

        $formatting_definitions = $definition_collection->matchBySingleCondition('category', DefinitionCategoryEnum::FORMATTING->value);

        if ($formatting_definitions->count() !== 0) {

            foreach ($formatting_definitions as $definition_name => $definition) {

                /* #review: Looking for formatting rules that are strictly non-pre. Even though "TagnameFormattingRule" in "EnhancedProperty" is a known pre-formatting rule, here the assessment is different and tagname rule can be accepted. What is not allowed are explicit pre rules. */
                if (!str_starts_with($definition_name, 'pre_') && $definition->canProduceClassObject()) {

                    $property->setFormattingRule($definition->produceClassObject());
                }
            }
        }
    }


    // Creates a new property instance from a given definition data array.

    final public static function fromDefinitionArray(
        string $property_name,
        array $definition_data_array
    ): self {

        return static::fromDefinitionCollection(
            $property_name,
            DefinitionCollection::fromArray($definition_data_array)
        );
    }


    // Creates a new base property instance from a given definition collection.

    public static function fromDefinitionCollection(
        string $property_name,
        DefinitionCollection $definition_collection
    ): self {

        $base_property = new self(
            ...self::setupParamsFromDefinitionCollection($property_name, $definition_collection)
        );

        self::setFormattingRulesFromDefinitionCollection($definition_collection, $base_property);

        return $base_property;
    }


    // Sets up constructor method params (optimized for this class) from a definition collection.

    public static function setupParamsFromDefinitionCollection(
        string $property_name,
        DefinitionCollection $definition_collection
    ): array {

        $params = [
            'property_name' => $property_name,
        ];

        // Type is required in "Property" class context, even though there are other primary definitions.
        if (!$definition_collection->containsKey('type')) {
            throw new NotFoundException(
                "Property \"$property_name\" is missing the \"type\" definition in the definition collection."
            );
        } elseif ($definition_collection->getTypeValue() === 'group') {
            throw new UnsupportedDefinitionCollectionException(
                "Cannot create property from a definition collection with \"group\" type."
            );
        } else {
            $params['data_type_name'] = $definition_collection->getTypeValue();
        }

        if ($definition_collection->containsKey('default')) {

            $default_value = $definition_collection->get('default')->getValue();

            // Don't data type closures.
            if (!($default_value instanceof \Closure)) {
                $default_value = NaturalDataTypeFactory::createDataTypeValueFromMixedTypeVariable($default_value);
            }

        } else {

            $default_value = new NoDefaultValueAttribute();
        }

        $params['default_value_obligation'] = $default_value;
        $other_params = [
            'readonly',
            'description',
            'title'
        ];

        foreach ($other_params as $other_param) {

            if ($definition_collection->containsKey($other_param)) {
                $params[$other_param] = $definition_collection->get($other_param)->getValue();
            }
        }

        return $params;
    }


    // Throws exception for when a property is not found.

    public static function throwPropertyNotFoundException(string $property_name, ?string $custom_message_format = null): never
    {

        throw new PropertyNotFoundException(
            sprintf(($custom_message_format ?: "Property \"%s\" was not found."), $property_name),
            property_name: $property_name
        );
    }
}

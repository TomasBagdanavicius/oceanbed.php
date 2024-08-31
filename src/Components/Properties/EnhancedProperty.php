<?php

declare(strict_types=1);

namespace LWP\Components\Properties;

use LWP\Components\DataTypes\DataTypeValueContainer;
use LWP\Components\Attributes\NoDefaultValueAttribute;
use LWP\Components\Definitions\DefinitionCollection;
use LWP\Components\Constraints\Constraint;
use LWP\Components\Constraints\ConstraintCollection;
use LWP\Components\Violations\ViolationCollection;
use LWP\Components\Messages\MessageCollection;
use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;
use LWP\Components\DataTypes\Exceptions\DataTypeConversionException;
use LWP\Components\Violations\InSetViolation;
use LWP\Components\Violations\DataTypeViolation;
use LWP\Components\DataTypes\DataTypeValueConstraintValidator;
use LWP\Components\DataTypes\Exceptions\DataTypeError;
use LWP\Components\Violations\RequiredObligationViolation;
use LWP\Components\Rules\FormattingRule;
use LWP\Components\DataTypes\Natural\NaturalDataTypeFactory;
use LWP\Common\Enums\AccessLevelsEnum;
use LWP\Common\Enums\ValidityEnum;
use LWP\Common\Exceptions\ValueNotAvailableException;
use LWP\Components\DataTypes\Natural\Null\NullDataTypeValueContainer;
use LWP\Components\Properties\Exceptions\PropertyDependencyException;
use LWP\Components\Violations\Violation;
use LWP\Components\DataTypes\ValueOriginEnum;
use LWP\Components\Model\EnhancedPropertyModel;
use LWP\Components\Properties\Enums\HookNamesEnum;
use LWP\Components\Violations\GenericViolation;
use LWP\Components\Model\AbstractModel;
use LWP\Components\DataTypes\Custom\Number\NumberDataTypeValueContainer;
use LWP\Components\DataTypes\Custom\Number\NumberDataTypeValueDescriptor;
use LWP\Components\Properties\Exceptions\PropertyTypeError;
use LWP\Components\Rules\Exceptions\FormattingRuleModificationException;
use LWP\Components\Properties\Exceptions\PropertyValueContainsErrorsException;
use LWP\Components\Properties\Exceptions\PropertyValueNotAvailableException;

class EnhancedProperty extends BaseProperty
{
    /* Phases for when formatting rules should be run. */
    public const PHASE_PRE = 1;
    public const PHASE_POST = 2;

    // Origin type of the value.
    protected ValueOriginEnum $value_origin;
    // Stores the invalid value, which might be used for info purposes.
    protected mixed $invalid_value;
    // Stores the so called "parked" value, which acts as on-hold feature.
    protected mixed $parked_value;
    // Pre-formatting rules.
    #review: consider converting this to "FormattingRuleCollection" object.
    protected array $pre_formatting_rules = [];
    protected ConstraintCollection $constraint_collection;
    protected ViolationCollection $violation_collection;


    public function __construct(
        string $property_name,
        string $data_type_name,
        DataTypeValueContainer|\Closure|NoDefaultValueAttribute $default_value_obligation = new NoDefaultValueAttribute(),
        // Null when undetermined.
        protected ?bool $required = null,
        bool $readonly = false,
        ?string $description = null,
        ?string $title = null,
        public readonly bool $allow_empty = true,
        public readonly bool $nullable = false,
        protected AccessLevelsEnum $set_access = AccessLevelsEnum::PUBLIC
    ) {

        try {

            parent::__construct(
                $property_name,
                $data_type_name,
                $default_value_obligation,
                $readonly,
                $description,
                $title
            );

            // Intercept data type conversion exception.
            #review: This logic could also preceed parent initialization.
        } catch (DataTypeConversionException $exception) {

            // Sets default value explicitly to null.
            /* It can be mentioned that, when there is no default value and property is nullable, it will resolve to null. */
            if ($nullable && ($default_value_obligation instanceof NullDataTypeValueContainer)) {
                $this->setupDefaultValue(new NullDataTypeValueContainer());
            } else {
                throw $exception;
            }
        }

        if (!isset($this->default_value_container)) {

            if (($default_value_obligation instanceof DataTypeValueContainer)) {

                $normalized_value_container = ($default_value_obligation::class !== $this->data_type_class_name)
                    // Convert to defined type.
                    ? $this->data_type_converter::convert($default_value_obligation)
                    : $default_value_obligation;

                $this->setupDefaultValue($normalized_value_container);

            } elseif (!($default_value_obligation instanceof NoDefaultValueAttribute)) {

                $closure_resolution = $this->resolveClosureAsDefaultValueProvider($default_value_obligation, $nullable);

                // Support for nullable.
                if ($nullable && ($closure_resolution instanceof NullDataTypeValueContainer)) {
                    $this->setupDefaultValue(new NullDataTypeValueContainer());
                } elseif (($closure_resolution instanceof DataTypeValueContainer)) {
                    $this->setupDefaultValue($closure_resolution);
                } elseif (($closure_resolution instanceof \Throwable)) {
                    throw $closure_resolution;
                }
            }
        }

        if (isset($this->default_value_container)) {

            if (!$allow_empty && $this->default_value_container->isEmpty()) {
                throw new \RuntimeException(
                    "Default value cannot be empty."
                );
            }

            $this->setValueInternal(
                $this->default_value_container,
                value_origin: ValueOriginEnum::DEFAULT
            );
        }
    }


    //

    public function getAccessLevel(): AccessLevelsEnum
    {

        return $this->set_access;
    }


    // Sets the "required" state.

    public function setRequired(?bool $required_state, bool $release_parked_value = true): void
    {

        $this->required = $required_state;

        if (
            $release_parked_value
            && ($required_state === null || $required_state === true)
            && $this->hasParkedValue()
        ) {

            $this->releaseParkedValue();
        }
    }


    // Tells if this property is required.

    public function isRequired(): ?bool
    {

        return $this->required;
    }


    // Alias of "isRequired" method.

    public function getRequired(): ?bool
    {

        return $this->isRequired();
    }


    // Determines what issue should be raised in the late assessment stage of the required state.

    public function getRequiredLateIssue(): ?Violation
    {

        if ($this->isRequired()) {

            return new RequiredObligationViolation(
                required_obligation: true,
                value: false
            );
        }

        return null;
    }


    // Handles things when property value is a violation or violation collection.
    /* Can be used with user-application injected violations. */

    public function preemptViolation(mixed $value): void
    {

        $is_violation = ($value instanceof Violation);

        if (
            $is_violation
            || ($value instanceof ViolationCollection)
        ) {

            if ($is_violation) {
                $this->setViolation($value);
            } else {
                $this->setViolationCollection($value);
            }

            throw new PropertyValueContainsErrorsException("Provided value contains errors.", $this->property_name);
        }
    }


    // Attempts to set property value using params that can be accessed publicly.

    public function setValue(
        mixed $value,
        ValueOriginEnum $value_origin = ValueOriginEnum::MAIN
    ): mixed {

        return self::setValueInternal($value, value_origin: $value_origin);
    }


    // Pre-formats and attempts to set the given value, provided that it passes all validation tests.

    protected function setValueInternal(
        mixed $value,
        array $options = [],
        ValueOriginEnum $value_origin = ValueOriginEnum::MAIN
    ): mixed {

        $default_options = [
            'set_access' => true,
            #review: it appears that 'required_state' is not used anywhere and is always `true`; remove it if it doesn't get used
            'required_state' => true,
        ];

        $options = [...$default_options, ...$options];

        if (
            $options['set_access'] === true
            && $this->set_access === AccessLevelsEnum::PRIVATE
            && $value_origin !== ValueOriginEnum::DEFAULT
            && $value_origin !== ValueOriginEnum::INTERNAL
        ) {
            throw new Exceptions\PropertySetAccessRestrictedException(sprintf(
                "Value for property \"%s\" can be set through private channel only",
                $this->property_name
            ));
        }

        if (
            $options['required_state'] === true
            && $this->getRequired() === false
            // No violation when nullable is fulfilled
            && ($value !== null || !$this->nullable)
        ) {

            $required_obligation_violation = new RequiredObligationViolation(
                required_obligation: false,
                value: true
            );

            $this->parked_value = $value;

            $this->setViolation(
                $required_obligation_violation,
                new Exceptions\PropertyStateException(
                    $required_obligation_violation->getErrorMessageString()
                )
            );
        }

        if (
            ($value instanceof DataTypeValueContainer)
            && ($descriptor = $value->getDescriptor())
            && $descriptor->value_origin
        ) {
            $value_origin = $descriptor->value_origin;
        }

        $is_default_value_origin = ($value_origin === ValueOriginEnum::DEFAULT);

        /* Start with natural data type for possible pre-formatting. Real data type will be determined through conversion after pre-formatting. */
        $data_type_value_container = (!($value instanceof DataTypeValueContainer))
            ? NaturalDataTypeFactory::createDataTypeValueFromMixedTypeVariable($value)
            : $value;
        $natural_data_type_value_container_class = $data_type_value_container::class;

        $set_value_handle = parent::getSetValueCallbacksHandle();

        $set_value_handle(
            'fireOnBeforeSetValueCallbacks',
            $value,
            function (mixed $value, mixed $saved_value): bool {

                // Checks if this is a violation injection.
                $this->preemptViolation($value);

                return true;
            }
        );

        if (!$is_default_value_origin) {

            // Manage nullables.
            if (!$this->nullable || $value !== null) {

                /* Pre-formatting */

                if ($this->pre_formatting_rules) {

                    // Collect unsupported rules to later check using real data type value container.
                    $unsupported_pre_formatting_rules = [];
                    $descriptor_class_name = $data_type_value_container::getDescriptorClassName();

                    foreach ($this->pre_formatting_rules as $pre_formatting_rule) {

                        if ($descriptor_class_name::isSupportedFormattingRule($pre_formatting_rule::class)) {
                            $data_type_value_container = $data_type_value_container->modifyByFormattingRule($pre_formatting_rule);
                        } else {
                            $unsupported_pre_formatting_rules[] = $pre_formatting_rule;
                        }
                    }
                }

                if ($data_type_value_container::class !== $this->data_type_class_name) {

                    try {

                        $params = [
                            $data_type_value_container
                        ];
                        $formatting_rule_for_convert = $this->getDataTypeConvertFormattingRule();

                        if ($formatting_rule_for_convert) {
                            $params['formatting_rule'] = $formatting_rule_for_convert;
                        }

                        $data_type_value_container = $this->data_type_converter::convert(...$params);

                    } catch (DataTypeConversionException $exception) {

                        $incorrect_type = $data_type_value_container::getDescriptorClassName()::TYPE_NAME;

                        $violation = new InSetViolation(
                            $this->data_type_converter::getAllSupportedTypeList(),
                            $incorrect_type,
                            $incorrect_type
                        );

                        // Custom error message for the violation.
                        $violation->setErrorMessageString(sprintf(
                            "Incorrect property value: given \"%s\" value could not be converted to \"%s\"",
                            $incorrect_type,
                            $this->data_type_name
                        ));

                        $this->setViolation(
                            $violation,
                            new PropertyTypeError(
                                sprintf(
                                    "Value of property \"%s\" must be of \"%s\" type",
                                    $this->property_name,
                                    $this->data_type_name
                                ),
                                $this->property_name,
                                previous: $exception
                            )
                        );

                    } catch (DataTypeError $error) {

                        $incorrect_type = ($data_type_value_container::getDescriptorClassName())::TYPE_NAME;

                        $violation = new DataTypeViolation(
                            $this->data_type_name,
                            $incorrect_type,
                            $incorrect_type
                        );

                        // Custom error message for the violation.
                        $violation->setErrorMessageString(
                            "Incorrect property type: value does not represent a valid \"$this->data_type_name\" data type."
                        );

                        $this->setViolation(
                            $violation,
                            new PropertyTypeError(
                                sprintf(
                                    "Value of property \"%s\" cannot be converted to type \"%s\"",
                                    $this->property_name,
                                    $this->data_type_name
                                ),
                                $this->property_name,
                                previous: $error
                            )
                        );
                    }
                }

                if (
                    // Has formatting rules that were unsupported by natural data type value
                    !empty($unsupported_pre_formatting_rules)
                    // Data type value has been successfully converted and it does not match the natural data type value class
                    && $natural_data_type_value_container_class !== $data_type_value_container::class
                ) {

                    $descriptor_class_name = $data_type_value_container::getDescriptorClassName();

                    foreach ($unsupported_pre_formatting_rules as $pre_formatting_rule) {

                        if ($descriptor_class_name::isSupportedFormattingRule($pre_formatting_rule::class)) {
                            $data_type_value_container = $data_type_value_container->modifyByFormattingRule($pre_formatting_rule);
                        }
                    }
                }

                if (!$this->allow_empty && $data_type_value_container->isEmpty($value)) {

                    /* Utilizing "RequiredObligationViolation", until "EmptyValueViolation" or similar is created and more widely used. "RequiredObligationViolation" fits the bill pretty well, because empty value can be seen as required violation as well. */
                    // Should obly to "true", but "false" is the value.
                    $required_violation = new RequiredObligationViolation(required_obligation: true, value: false);
                    $required_violation->setErrorMessageString(sprintf(
                        "Value for property \"%s\" cannot be empty",
                        $this->property_name
                    ));

                    $this->invalid_value = $value;

                    $this->setViolation(
                        $required_violation,
                        new PropertyValueContainsErrorsException(
                            "Provided value contains errors",
                            property_name: $this->property_name
                        )
                    );
                }

                /* Constraints Validation */

                if (
                    isset($this->constraint_collection)
                    && $this->constraint_collection->count() !== 0
                    // No value descriptor
                    && $data_type_value_container?->getDescriptor()?->validity === null
                ) {

                    $constraint_validator = $data_type_value_container->getConstraintValidatorClassObject(
                        $this->constraint_collection,
                        DataTypeValueConstraintValidator::SUPPRESS_ERROR
                    );

                    $validation_result = $constraint_validator->validate();

                    if ($validation_result instanceof ViolationCollection) {

                        $this->invalid_value = $value;

                        $this->setViolationCollection(
                            $validation_result,
                            new PropertyValueContainsErrorsException(
                                "Provided value contains errors",
                                property_name: $this->property_name
                            )
                        );
                    }
                }
            }
        }

        // Refresh basic value from the container where it might have been scrambled (eg. pre-formatting).
        $value = $data_type_value_container->getValue();

        $this->throttleHooks(HookNamesEnum::BEFORE_SET_VALUE);
        $this->throttleHooks(HookNamesEnum::AFTER_SET_VALUE);

        // If the default value came from default value resolution, don't block it in readonly check.
        if (!$is_default_value_origin) {

            $value = parent::setValueInternal($value, [
                'readonly' => false,
                'validate_data_type' => false,
                'nullable' => $this->nullable,
            ]);

        } else {

            $value = parent::setValueInternal($value, [
                'readonly' => false,
                'validate_data_type' => false,
                'nullable' => $this->nullable
            ]);
        }

        $this->unthrottleHooks(HookNamesEnum::BEFORE_SET_VALUE);
        $this->unthrottleHooks(HookNamesEnum::AFTER_SET_VALUE);

        $saved_value = $value;

        // This is required in the "on after set value" callbacks, which are fired below.
        $this->value_origin = $value_origin;

        $current_value = $data_type_value_container->getValue();

        try {

            $value = $this->fireOnAfterSetValueCallbacks($current_value);

        } catch (PropertyDependencyException $exception) {

            $this->invalid_value = $current_value;

            $this->setViolation(
                $exception->violation,
                new PropertyValueContainsErrorsException(
                    "Provided value contains errors.",
                    property_name: $this->property_name,
                    previous: $exception
                )
            );
        }

        // If after callback filterring values don't strictly match, rebuild data type value container.
        if ($value !== $saved_value) {

            if (!$this->data_type_converter::canConvertFrom($value)) {
                throw new \RuntimeException(sprintf(
                    "Filtered value must match the original data type \"%s\".",
                    $this->data_type_name
                ));
            }

            $data_type_value_container = $this->data_type_converter::convert($value);
        }

        $this->setupValue($value, $data_type_value_container, $value_origin);

        return $this->value;
    }


    // Run checks for when attempting to set value through private channel.

    protected function assertValuePrivate(AbstractModel $host_model, mixed $value, array $options = []): void
    {

        if (!($host_model instanceof EnhancedPropertyModel)) {
            throw new \RuntimeException(
                "Illegal attempt to set value through private channel: invalid host model."
            );
        }

        if (isset($options['set_access']) && $options['set_access'] === false && !$host_model->getSetAccessControlStack()) {
            throw new \RuntimeException(
                "Illegal attempt to set value through private channel: access control stack is empty."
            );
        }

        parent::assertValuePrivate($host_model, $value, $options);
    }


    // Attempts to set the given value through a private channel.

    public function setValuePrivate(
        AbstractModel $host_model,
        mixed $value,
        array $options = [],
        ValueOriginEnum $value_origin = ValueOriginEnum::MAIN
    ): mixed {

        self::assertValuePrivate($host_model, $value, $options);

        return static::setValueInternal($value, $options, $value_origin);
    }


    // Arranges all things that establish a value entity.

    protected function setupValue(
        mixed $value,
        ?DataTypeValueContainer $value_container = null,
        ValueOriginEnum $value_origin = ValueOriginEnum::MAIN
    ): void {

        parent::setupValue($value, $value_container);

        $this->resetViolationCollection();
        $this->value_origin = $value_origin;
    }


    // Dissolves the value entity.

    public function unsetValue(bool $keep_default = false): void
    {

        $this->throttleHooks(HookNamesEnum::UNSET_VALUE);
        parent::unsetValue();
        $this->unthrottleHooks(HookNamesEnum::UNSET_VALUE);

        $this->resetViolationCollection();

        unset(
            $this->value_origin,
            $this->invalid_value,
            $this->parked_value
        );

        if (!$keep_default) {

            unset(
                $this->default_value,
                $this->default_value_container
            );

            $this->default_value_obligation = new NoDefaultValueAttribute();
        }

        $this->fireOnUnsetValueCallbacks();
    }


    // Tells if it has a value.
    /* Nullable is not considered to be a qualified value. */

    public function hasValue(): bool
    {

        return ($this->hasMainValue() || $this->hasDefaultValue());
    }


    // Tells if the main value was set.

    public function hasMainValue(): bool
    {

        $base_has_main_value = parent::hasMainValue();

        if (!$base_has_main_value) {
            return false;
        }

        /* Check where the value originated. Default does not count as main. */
        return ($this->value_origin !== ValueOriginEnum::DEFAULT);
    }


    // Resolves the value.

    public function getValue(): mixed
    {

        try {

            $this->throttleHooks(HookNamesEnum::AFTER_GET_VALUE);
            $value = parent::getValue();
            $this->unthrottleHooks(HookNamesEnum::AFTER_GET_VALUE);

            // No value set, and no default value
        } catch (PropertyValueNotAvailableException $exception) {

            // Can be nullified
            if ($this->nullable && !$this->getRequiredLateIssue()) {
                $value = null;
            } else {
                throw $exception;
            }

            // Failed to format in `BaseProperty::getValue`
        } catch (FormattingRuleModificationException $exception) {

            #review: is it okay to just nullify
            if ($this->nullable) {
                $value = null;
            } else {
                $this->setViolation(
                    "Property could not be formatted",
                    // By design this needs to be `PropertyValueContainsErrorsException` to be recognized
                    new PropertyValueContainsErrorsException(
                        "Provided value contains errors",
                        property_name: $this->property_name,
                        previous: $exception
                    )
                );
            }
        }

        return $this->fireOnAfterGetValueCallbacks($value);
    }


    // Gets the value origin.

    public function getValueOrigin(): ?ValueOriginEnum
    {

        return ($this->value_origin ?? null);
    }


    // Sets the value origin.

    protected function setValueOrigin(ValueOriginEnum $value_origin): void
    {

        $this->value_origin = $value_origin;
    }


    // Checks if given alternative value property was set.

    protected function hasAltValueProperty(string $property_name): bool
    {

        // Null value is allowed, hence try.
        try {

            $this->{$property_name};
            return true;

        } catch (\Error) {

            return false;
        }
    }


    // Gets alternative value by a given property name.

    protected function getAltValueProperty(string $property_name): mixed
    {

        if ($this->hasAltValueProperty($property_name)) {

            return $this->{$property_name};

        } else {

            throw new ValueNotAvailableException(
                "Value for property \"parked_value\" is not available."
            );
        }
    }


    // Tells if the invalid value was set.

    public function hasInvalidValue(): mixed
    {

        return $this->hasAltValueProperty('invalid_value');
    }


    // Attempts to get the invalid value.

    public function getInvalidValue(): mixed
    {

        return $this->getAltValueProperty('invalid_value');
    }


    // Tells if the parked value was set.

    public function hasParkedValue(): bool
    {

        return $this->hasAltValueProperty('parked_value');
    }


    // Attempts to get the parked value.

    public function getParkedValue(): mixed
    {

        return $this->getAltValueProperty('parked_value');
    }


    // Sets the parked value as the main value, provided that parked value exists.

    protected function releaseParkedValue(): void
    {

        if ($this->hasParkedValue()) {

            $this->setValueInternal(
                $this->getParkedValue(),
                value_origin: ValueOriginEnum::PARKED
            );

            unset($this->parked_value);
        }
    }


    // Registers a new violation.

    public function setViolation(
        string|Violation $violation,
        ?\Throwable $error = null
    ): null|int|string {

        $this->violation_collection ??= new ViolationCollection();

        if (is_string($violation)) {

            $error_message_string = $violation;
            $violation = new GenericViolation();
            $violation->setErrorMessageString($error_message_string);
        }

        $violation_index = $this->violation_collection->set(
            spl_object_id($violation),
            $violation
        );

        if ($error) {
            throw $error;
        }

        return $violation_index;
    }


    // Imports an entire violation collection.

    public function setViolationCollection(
        ViolationCollection $violation_collection,
        ?\Throwable $error = null
    ): void {

        $this->violation_collection ??= new ViolationCollection();
        $this->violation_collection->importFromCollection($violation_collection);

        if ($error) {
            throw $error;
        }
    }


    // Truncates the violation collection.

    protected function resetViolationCollection(): void
    {

        unset($this->violation_collection);
    }


    // Unsets violation by given violation index key.

    public function unsetViolation(int|string $violation_index): void
    {

        $this->violation_collection->remove($violation_index);
    }


    // Returns violation collection.

    public function getViolationCollection(): ?ViolationCollection
    {

        return ($this->violation_collection ?? null);
    }


    // Tells if there are any active errors.

    public function hasErrors(): bool
    {

        return (
            isset($this->violation_collection)
            && $this->violation_collection->count()
        );
    }


    // Gets errors in a violation collection object format.

    public function getErrorsAsViolationCollection(): ?ViolationCollection
    {

        return ($this->hasErrors())
            ? $this->violation_collection
            : null;
    }


    // Gets errors in a message collection object format.

    public function getErrorsAsMessageCollection(): ?MessageCollection
    {

        $violation_collection = $this->getErrorsAsViolationCollection();

        if (!$violation_collection) {
            return null;
        }

        return $violation_collection->toErrorMessageCollection();
    }


    //

    public function getErrorsAsArray(): array
    {

        $errors = [];
        $violation_collection = $this->getErrorsAsViolationCollection();

        if ($violation_collection) {

            foreach ($violation_collection as $violation) {
                $errors[] = $violation->getErrorMessageString();
            }
        }

        return $errors;
    }


    // Registers a constraint for this property.

    public function setConstraint(Constraint $constraint): null|int|string
    {

        if (isset($this->default_value_container)) {

            $validation_result = $constraint->getValidator()
                ->validate($this->default_value_container);

            if ($validation_result instanceof Violation) {

                throw new \RuntimeException(
                    "Default value does not satisfy constraint conditions.",
                    previous: $validation_result->getExceptionObject()
                );
            }
        }

        $this->constraint_collection ??= new ConstraintCollection();

        return $this->constraint_collection->add($constraint);
    }


    // Registers a given formatting rule along with its phase.

    public function setFormattingRule(FormattingRule $formatting_rule, int $phase = self::PHASE_POST): void
    {

        if ($phase === self::PHASE_POST) {

            parent::setFormattingRule($formatting_rule);

        } elseif ($phase === self::PHASE_PRE) {

            $this->assertFormattingRuleSupport($formatting_rule);
            $this->pre_formatting_rules[$formatting_rule::class] = $formatting_rule;
        }
    }


    // Returns formatting rule of the property's data type that can be used when running data type conversion.

    public function getDataTypeConvertFormattingRule(int $phase = self::PHASE_POST): ?FormattingRule
    {

        if ($phase === self::PHASE_POST) {

            return parent::getDataTypeConvertFormattingRule();

        } elseif ($phase === self::PHASE_PRE) {

            $formatting_rule_for_convert = $this->data_type_class_name::getConvertFormattingRuleClassName();

            if (
                $formatting_rule_for_convert
                && isset($this->pre_formatting_rules[$formatting_rule_for_convert])
            ) {
                return $this->pre_formatting_rules[$formatting_rule_for_convert];
            }
        }
    }


    //

    public function getIndexablePropertyList(): array
    {

        return [...parent::getIndexablePropertyList(), ...[
            'required',
            'allow_empty',
        ]];
    }


    // Returns value of a given indexable property.

    public function getIndexablePropertyValue(string $property_name): mixed
    {

        $this->assertIndexablePropertyExistence($property_name);

        if ($property_name === 'required') {
            return $this->required;
        } elseif ($property_name === 'allow_empty') {
            return $this->allow_empty;
        } else {
            return parent::getIndexablePropertyValue($property_name);
        }
    }


    // Determines what formatting rule phase should be used for a given formatting rule and its associated definition name.
    #review: This could also be refactored and used in definitions, but there is no final decision.

    public static function detectFormattingRulePhase(string $formatting_rule_class_name, string $definition_name): int
    {

        /* Note: Looking for "pre_" or "post" prefix in definition names to identify pre-formatting rules vs. post-formatting rules. */

        if (str_starts_with($definition_name, 'pre_')) {

            return self::PHASE_PRE;

        } elseif (str_starts_with($definition_name, 'post_')) {

            return self::PHASE_POST;

        } else {

            $known_cases = [
                'LWP\Components\Rules\TagnameFormattingRule' => self::PHASE_PRE,
            ];

            return (array_key_exists($formatting_rule_class_name, $known_cases))
                ? $known_cases[$formatting_rule_class_name]
                : self::PHASE_POST;
        }
    }


    // Imports formatting rules into a given property from a definition collection.

    public static function setFormattingRulesFromDefinitionCollection(
        DefinitionCollection $definition_collection,
        BaseProperty &$property
    ): void {

        $formatting_definitions = $definition_collection->matchBySingleCondition(
            'category',
            DefinitionCategoryEnum::FORMATTING->value
        );

        if ($formatting_definitions->count() !== 0) {

            foreach ($formatting_definitions as $definition_name => $definition) {

                if ($definition->canProduceClassObject()) {

                    $formatting_rule = $definition->produceClassObject();
                    $pre_post = self::detectFormattingRulePhase($formatting_rule::class, $definition_name);

                    $property->setFormattingRule($formatting_rule, $pre_post);
                }
            }
        }
    }


    // Imports constraints into a given property from a definition collection.

    public static function setConstraintsFromDefinitionCollection(DefinitionCollection $definition_collection, self &$property): void
    {

        $constraint_definitions = $definition_collection->matchBySingleCondition(
            'category',
            DefinitionCategoryEnum::CONSTRAINT->value
        );

        if ($constraint_definitions->count()) {

            foreach ($constraint_definitions as $definition) {

                if ($definition->canProduceClassObject()) {
                    $property->setConstraint($definition->produceClassObject());
                }
            }
        }
    }


    // Sets up params for enhanced property constructor from a definition collection.

    final public static function setupParamsFromDefinitionCollection(string $property_name, DefinitionCollection $definition_collection): array
    {

        $params = parent::setupParamsFromDefinitionCollection($property_name, $definition_collection);
        $other_params = [
            'required',
            'allow_empty',
            'nullable',
            'set_access',
        ];

        foreach ($other_params as $other_param) {

            if ($definition_collection->containsKey($other_param)) {

                $definition_value = $definition_collection->get($other_param)->getValue();

                if ($other_param === 'set_access') {

                    $params[$other_param] = match ($definition_value) {
                        'public' => AccessLevelsEnum::PUBLIC,
                        'private' => AccessLevelsEnum::PRIVATE
                    };

                } else {

                    $params[$other_param] = $definition_value;
                }
            }
        }

        return $params;
    }


    // Creates a new enhanced property object from a given definition collection.

    final public static function fromDefinitionCollection(string $property_name, DefinitionCollection $definition_collection): self
    {

        $property = new self(...self::setupParamsFromDefinitionCollection($property_name, $definition_collection));

        self::setConstraintsFromDefinitionCollection($definition_collection, $property);
        self::setFormattingRulesFromDefinitionCollection($definition_collection, $property);

        return $property;
    }
}

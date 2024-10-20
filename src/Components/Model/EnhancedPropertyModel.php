<?php

declare(strict_types=1);

namespace LWP\Components\Model;

use LWP\Common\Common;
use LWP\Components\Datasets\Interfaces\DatasetFieldValueExtenderInterface;
use LWP\Components\Properties\Exceptions\PropertyValueNotAvailableException;
use LWP\Components\Definitions\DefinitionCollection;
use LWP\Components\Properties\EnhancedPropertyCollection;
use LWP\Components\Properties\Exceptions\PropertyValueContainsErrorsException;
use LWP\Components\Properties\Exceptions\PropertyTypeError;
use LWP\Components\Properties\EnhancedProperty;
use LWP\Components\Properties\AbstractPropertyCollection;
use LWP\Components\Properties\BaseProperty;
use LWP\Components\Properties\Exceptions\PropertyDependencyException;
use LWP\Components\Properties\Enums\HookNamesEnum;
use LWP\Components\Properties\Exceptions\PropertyStateException;
use LWP\Components\DataTypes\ValueOriginEnum;

class EnhancedPropertyModel extends BasePropertyModel
{
    #review: if no other class is using it, consider importing all traits into this.
    use ModelErrorHandlingTrait;


    public const COLLECT_ERRORS = 1;
    public const THROW_ERROR_IMMEDIATELY = 2;

    /* Available in "COLLECT_ERRORS" mode. Registers error count only when property value is set. */
    protected array $explicit_error_stats = [];
    /* Controls whether property values can be set through the private channel. Integer type chosen, because calls can be stacked in code. When it's zero, the stack is empty, meaning public channel should be used as normal. */
    protected int $set_access_control_stack = 0;


    public function __construct(
        ?AbstractPropertyCollection $property_collection = null,
    ) {

        parent::__construct(
            $property_collection ?: new EnhancedPropertyCollection()
        );
    }


    // Adds a new property object into the set.

    public function addProperty(BaseProperty $property): null|int|string
    {

        // Validate property type independently, because typing is limited to "BaseProperty".
        if (!($property instanceof EnhancedProperty)) {
            Common::throwTypeError(1, __FUNCTION__, EnhancedProperty::class, gettype($property));
        }

        return parent::addProperty($property);
    }


    // Sets property value.

    public function __set(string $property_name, mixed $property_value): void
    {

        try {

            $this->setPropertyValueInternal($property_name, $property_value);

            /* This "catch" block is used purposedly. "setPropertyValue" has to throw, when no value is set, therefore same exceptions need to be caught here. See "setPropertyValue" method's description for more information. */
        } catch (PropertyTypeError|PropertyValueContainsErrorsException|PropertyDependencyException|PropertyStateException $exception) {

            // This is specific to magic setter and getter.
            if ($this->error_handling_mode === self::THROW_ERROR_IMMEDIATELY) {
                throw $exception;
            }
        }
    }


    // Adjusts options that are used when setting property value internally.

    protected function adjustSetPropertyValueInternalOptions(&$options): void
    {

        if ($this->set_access_control_stack) {
            $options['set_access'] = false;
        }
    }


    // Attempts to set property value using params that can be accessed internally only.
    /* This method has to throw an error and ignore error handling mode, because its return value type must be mixed (eg. property value can be anything). Imagine that property value can be "false" or of "null" type, meaning that there is no way to inform about a failure other than exception. */

    protected function setPropertyValueInternal(
        string $property_name,
        mixed $property_value,
        ValueOriginEnum $value_origin = ValueOriginEnum::MAIN,
        array $options = [],
    ): mixed {

        $property_value = $this->fireOnBeforeSetValueCallbacks($property_value, [
            $property_name,
        ], $property_name);

        $property = $this->getPropertyByName($property_name);
        $this->adjustSetPropertyValueInternalOptions($options);

        try {

            // Checks if this is a violation injection.
            $property->preemptViolation($property_value);

            $this->throttleHooks(HookNamesEnum::BEFORE_SET_VALUE, $property_name);
            $set_value = parent::setPropertyValueInternal($property_name, $property_value, $value_origin, $options);
            $this->unthrottleHooks(HookNamesEnum::BEFORE_SET_VALUE, $property_name);

        } catch (PropertyTypeError|PropertyValueContainsErrorsException|PropertyDependencyException|PropertyStateException $exception) {

            if ($this->error_handling_mode === self::COLLECT_ERRORS) {

                if (!isset($this->explicit_error_stats[$property_name])) {
                    $this->explicit_error_stats[$property_name] = 1;
                } else {
                    $this->explicit_error_stats[$property_name]++;
                }

                throw $exception;

            } elseif ($this->error_handling_mode === self::THROW_ERROR_IMMEDIATELY) {

                $params = [
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode(),
                    'previous' => $this->getPropertyByName($property_name)
                        ->getErrorsAsViolationCollection()
                        ?->getFirst()
                        ?->getExceptionObject()
                ];

                if (
                    $exception instanceof PropertyValueContainsErrorsException
                    || $exception instanceof PropertyTypeError
                ) {
                    $params['property_name'] = $property_name;
                }

                throw new ($exception)(...$params);
            }
        }

        return $set_value;
    }


    // Gets given property's value.

    public function __get(string $property_name): mixed
    {

        $property = $this->getPropertyByName($property_name);

        // Contains errors.
        if ($property->hasErrors()) {

            $reason_error_msg_str = "Property \"$property_name\" contains errors.";

            throw new PropertyValueNotAvailableException(
                "Value for property \"$property_name\" is not available: $reason_error_msg_str",
                previous: new PropertyValueContainsErrorsException($reason_error_msg_str, $property_name)
            );

            // Good to fetch.
        } else {

            return $this->fireOnAfterGetValueCallbacks($property->getValue(), [
                $property_name,
            ], $property_name);
        }
    }


    // Gets value data for a given property.

    public function getValue(
        EnhancedProperty $property,
        bool $include_messages = false,
        bool $alt_values = false
    ): mixed {

        $result = [];

        try {

            $property_value = $this->__get($property->property_name);

            if (!$include_messages) {
                $result = $property_value;
            } else {
                $result['value'] = $property_value;
            }

            if ($property_value === null && ($violation = $property->getRequiredLateIssue())) {
                $result['errors'][] = $violation->getErrorMessageString();
            }

        } catch (PropertyValueNotAvailableException $exception) {

            if ($include_messages) {

                $property = $this->getPropertyByName($property->property_name);
                // Provides the reason explaining why value is not available.
                $previous_exception = $exception->getPrevious();

                // Contains errors.
                if (($previous_exception instanceof PropertyValueContainsErrorsException)) {

                    $violation_collection = $property->getErrorsAsViolationCollection();

                    foreach ($violation_collection as $violation) {

                        $result['errors'][] = $violation->getErrorMessageString();
                    }

                    // Include alternative (invalid, parked) values.
                    if ($alt_values) {

                        // Invalid value.
                        if ($property->hasInvalidValue()) {
                            $result['invalid_value'] = $property->getInvalidValue();
                        }

                        // Parked value.
                        if ($property->hasParkedValue()) {
                            $result['parked_value'] = $property->getParkedValue();
                        }
                    }

                    // Positive required state late evaluation.
                } elseif ($violation = $property->getRequiredLateIssue()) {

                    $result['errors'][] = $violation->getErrorMessageString();
                }
            }
        }

        return $result;
    }


    // Gets value by a given property name.

    public function getValueByPropertyName(string $property_name, bool $include_messages = false, bool $alt_values = false): mixed
    {

        return $this->getValue(
            $this->getPropertyByName($property_name),
            $include_messages,
            $alt_values
        );
    }


    // Gathers all values into a single array.

    public function getValues(
        ?array $filter_names = null,
        bool $include_messages = false,
        bool $add_index = false,
        bool $alt_values = false,
        bool $error_index = false
    ): array {

        $result = [];

        if ($add_index) {

            $result['__index'] = [
                'error_count' => 0
            ];

            if ($error_index) {
                $result['__index']['errors'] = [];
            }
        }

        foreach ($this->property_collection as $property_name => $property) {

            if (!$filter_names || in_array($property_name, $filter_names)) {

                $property_result = $this->getValue($property, $include_messages, $alt_values);

                if ($property_result !== []) {

                    if (
                        isset($property_result['value'])
                        && ($property_result['value'] instanceof DatasetFieldValueExtenderInterface)
                    ) {
                        $property_result['value'] = $property_result['value']->getOriginalValue();
                    }

                    $result[$property_name] = $property_result;

                    if ($add_index && isset($property_result['errors'])) {

                        $result['__index']['error_count']++;

                        if ($error_index) {
                            $result['__index']['errors'][$property_name] = $property_result['errors'];
                        }
                    }
                }
            }
        }

        return $result;
    }


    //

    public function getValuesIterator(?array $filter_names = null, bool $include_messages = false, bool $alt_values = false): EnhancedPropertyModelValuesIterator
    {

        return new EnhancedPropertyModelValuesIterator($this, $filter_names, $include_messages, $alt_values);
    }


    // Gathers all values into a single array with messages included.

    public function getValuesWithMessages(?array $filter_names = null, bool $add_index = false, bool $alt_values = false, bool $error_index = false): array
    {

        return $this->getValues($filter_names, true, $add_index, $alt_values, $error_index);
    }


    // Increases the size of the set access control stack.

    public function occupySetAccessControlStack(): int
    {

        $this->set_access_control_stack++;

        return $this->set_access_control_stack;
    }


    // Decreases the size of the set access control stack.

    public function deoccupySetAccessControlStack(): int
    {

        $this->set_access_control_stack--;

        return $this->set_access_control_stack;
    }


    // Gets set access control stack size number.

    public function getSetAccessControlStack(): int
    {

        return $this->set_access_control_stack;
    }


    // Creates a new enhanced property object from a given definition collection.

    public static function createPropertyFromDefinitionCollection(
        string $property_name,
        DefinitionCollection $definition_collection,
        AbstractModel $model
    ): EnhancedProperty {

        return EnhancedProperty::fromDefinitionCollection($property_name, $definition_collection);
    }
}

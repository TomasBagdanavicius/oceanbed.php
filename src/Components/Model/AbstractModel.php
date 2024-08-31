<?php

/*
Reasons why Model classes don't extend AbstractPropertyCollection (or any other property collection for that matter):
- Use of magic setters and getters. Any class with setters and getters works better as a standalone class.
- Method collisions with Array Collection, eg. "setMass".
- Potentially required to sanitize many ArrayCollection methods to achieve structural tidiness.
*/

declare(strict_types=1);

namespace LWP\Components\Model;

use LWP\Common\Conditions\ConditionGroup;
use LWP\Components\Properties\AbstractPropertyCollection;
use LWP\Components\Properties\BaseProperty;
use LWP\Components\Properties\Exceptions\PropertyValueNotAvailableException;
use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Definitions\DefinitionCollection;
use LWP\Components\Properties\Exceptions\PropertyNotFoundException;
use LWP\Common\Exceptions\ReadOnlyException;
use LWP\Common\Exceptions\AbortException;
use LWP\Components\Model\Exceptions\PropertyAddException;
use LWP\Components\Definitions\Exceptions\UnsupportedDefinitionCollectionException;
use LWP\Components\DataTypes\ValueOriginEnum;
use LWP\Components\Properties\Enums\HookNamesEnum;

abstract class AbstractModel implements \ArrayAccess
{
    use \LWP\Components\Properties\HooksTrait;


    // Internal queue of property values to be set.
    protected array $set_queue = [];
    protected array $on_flush_set_queue_callbacks = [];
    protected array $on_before_property_add_callbacks = [];
    protected array $on_after_property_add_callbacks = [];
    // Denotes if it's currently tracking changes.
    public bool $is_tracking_changes = false;
    // Associative array containing property names an values gathered during tracking.
    protected array $tracking_changes = [];
    protected array $on_clone_callbacks = [];
    // The definition collection set that it was last created from.
    protected static DefinitionCollectionSet $definition_collection_set_created_from;


    public function __construct(
        // Used in "__clone", therefore not "readonly".
        protected ?AbstractPropertyCollection $property_collection,
    ) {

    }


    // Creates a new object instance from a given definition collection.

    abstract public static function createPropertyFromDefinitionCollection(
        string $property_name,
        DefinitionCollection $definition_collection,
        AbstractModel $model
    ): BaseProperty;


    // Runs procedures on model clone.
    /* As per model cloning policy. */

    public function __clone(): void
    {

        $this->property_collection = clone $this->property_collection;

        foreach ($this->on_clone_callbacks as $callback) {
            $callback($this);
        }
    }


    // Registers a callback to be called when model is cloned.

    public function onClone(\Closure $callback): void
    {

        $this->on_clone_callbacks[] = $callback;
    }


    // Checks whether an offset exists

    public function offsetExists(mixed $offset): bool
    {

        return $this->__isset($offset);
    }


    // Retrieves element by offset

    public function offsetGet(mixed $offset): mixed
    {

        return $this->getPropertyValue($offset);
    }


    // Assigns a value to the specified offset

    public function offsetSet(mixed $offset, mixed $value): void
    {

        if ($offset === null || $offset === '') {
            throw new \Exception("Cannot set an empty offset in model");
        }

        if (!is_string($offset)) {
            throw new \TypeError("Offset must be a string");
        }

        $this->__set($offset, $value);
    }


    // Unsets an offset

    public function offsetUnset(mixed $offset): void
    {

        $this->__unset($offset);
    }


    // Adds a new property object into the set.

    public function addProperty(BaseProperty $property): null|int|string
    {

        try {

            $property = $this->fireOnBeforePropertyAddCallbacks($property);

        } catch (AbortException $exception) {

            throw new PropertyAddException(
                "Property \"{$property->property_name}\" could not be added: action aborted by application.",
                previous: $exception
            );
        }

        $property_index = $this->property_collection->add($property);

        $this->assignTrackingCallbackToProperty($property);
        $this->fireOnAfterPropertyAddCallbacks($property, $property_index);

        return $property_index;
    }


    // Removes property from the set.

    public function removeProperty(string $property_name): BaseProperty
    {

        return $this->property_collection->remove($property_name);
    }


    // Tells if the given property exists in the property collection.

    public function propertyExists(string $property_name): bool
    {

        return $this->property_collection->containsKey($property_name);
    }


    // Validates if the given property exists in the property collection.

    public function assertPropertyName(string $property_name): void
    {

        if (!$this->propertyExists($property_name)) {

            BaseProperty::throwPropertyNotFoundException($property_name);
        }
    }


    // Gets property object by a given property name.

    public function getPropertyByName(string $property_name): BaseProperty
    {

        $this->assertPropertyName($property_name);

        return $this->property_collection->get($property_name);
    }


    // Registers a callback that will be run before a new property is added.

    public function onBeforePropertyAdd(\Closure $callback, bool $once = false): int
    {

        $this->on_before_property_add_callbacks[] = [
            'callback' => $callback,
            'once' => $once,
        ];

        return array_key_last($this->on_before_property_add_callbacks);
    }


    // Registers a callback that will be run after a new property is added.

    public function onAfterPropertyAdd(\Closure $callback, bool $once = false): int
    {

        $this->on_after_property_add_callbacks[] = [
            'callback' => $callback,
            'once' => $once,
        ];

        return array_key_last($this->on_after_property_add_callbacks);
    }


    // Fires all "on before add property" hook callbacks.

    protected function fireOnBeforePropertyAddCallbacks(BaseProperty $property): mixed
    {

        if ($this->on_before_property_add_callbacks) {

            foreach ($this->on_before_property_add_callbacks as $index => $callback_data) {

                $property = $callback_data['callback']($property, $this);

                if ($callback_data['once']) {
                    unset($this->on_before_property_add_callbacks[$index]);
                }
            }
        }

        return $property;
    }


    // Fires all "on after add property" hook callbacks.

    protected function fireOnAfterPropertyAddCallbacks(BaseProperty $property, null|int|string $property_index): void
    {

        if ($this->on_after_property_add_callbacks) {

            foreach ($this->on_after_property_add_callbacks as $index => $callback_data) {

                $callback_data['callback']($property, $this);

                if ($callback_data['once']) {
                    unset($this->on_after_property_add_callbacks[$index]);
                }
            }
        }
    }


    // Returns the property collection object.

    public function getPropertyCollection(): AbstractPropertyCollection
    {

        return $this->property_collection;
    }


    // Replaces properties in the given property collection by this object's properties.

    public function rebuildPropertyCollection(AbstractPropertyCollection $property_collection): void
    {

        foreach ($property_collection as $property_name => $property) {

            if ($this->propertyExists($property_name)) {
                $property_collection->offsetSet($property_name, $this->getPropertyByName($property_name));
            }
        }
    }


    // Filters out properties that intersect with properties in a given collection and creates a fresh collection.

    public function createNewPropertyCollectionBasedOn(
        AbstractPropertyCollection $property_collection,
        bool $truncate_callbacks = false
    ): AbstractPropertyCollection {

        $new_property_collection = new $this->property_collection();

        foreach ($property_collection as $property_name => $property) {

            if ($this->propertyExists($property_name)) {

                $new_property = $this->getPropertyByName($property_name);

                if ($truncate_callbacks) {
                    $new_property->truncateAllHooks();
                }

                $new_property_collection->add($new_property);
            }
        }

        return $new_property_collection;
    }


    // Sets value for a given property.

    public function __set(string $property_name, mixed $property_value): void
    {

        $this->setPropertyValueInternal($property_name, $property_value);
    }


    // Sets property value using params that can be accessed publicly.

    public function setPropertyValue(
        string $property_name,
        mixed $property_value,
        ValueOriginEnum $value_origin = ValueOriginEnum::MAIN
    ): mixed {

        return static::setPropertyValueInternal(
            $property_name,
            $property_value,
            $value_origin
        );
    }


    // Sets property value using params that can be accessed internally only.
    /* This function exists to return the accepted value, which is not possible with "__set", where the return type must be "void". */

    protected function setPropertyValueInternal(
        string $property_name,
        mixed $property_value,
        ValueOriginEnum $value_origin = ValueOriginEnum::MAIN,
        array $options = []
    ): mixed {

        $property = $this->getPropertyByName($property_name);

        $property_value = $this->fireOnBeforeSetValueCallbacks($property_value, [
            $property_name,
        ], $property_name);

        $supports_value_origin = method_exists($property, 'getValueOrigin');

        if ($options) {

            $method_params = [
                $this,
                $property_value,
                $options
            ];

            if ($supports_value_origin) {
                $method_params['value_origin'] = $value_origin;
            }

            $accepted_value = $property->setValuePrivate(...$method_params);

            // Checks if property supports value origin.
        } elseif ($supports_value_origin) {

            $accepted_value = $property->setValue($property_value, value_origin: $value_origin);

        } else {

            $accepted_value = $property->setValue($property_value);
        }

        // Class property instead of method param, because this method must be compatible to the same method in sub-classes.
        $accepted_value = $this->fireOnAfterSetValueCallbacks($accepted_value, [
            $property_name,
        ], $property_name);

        return $accepted_value;
    }


    // Sets mutiple property values at once.
    /* This method recognizes the "error_handling_mode" parameter, therefore it won't return anything and will be inline with "__set" behavior, where errors can be either thrown immediatelly or collected. */

    public function setMass(array $property_values, bool $order_by_definition_set_keys = true): void
    {

        $property_keys = array_keys($property_values);
        $property_collection = $this->property_collection->getKeys();
        $unrecognized_property_names = array_diff($property_keys, $property_collection);

        if (!empty($unrecognized_property_names)) {

            foreach ($unrecognized_property_names as $unrecognized_property_name) {
                $this->assertPropertyName($unrecognized_property_name);
            }
        }

        if ($order_by_definition_set_keys) {

            $order = array_intersect($property_collection, $property_keys);
            $updated_property_values = [];

            foreach ($order as $property_name) {
                $updated_property_values[$property_name] = $property_values[$property_name];
            }

            // Swap with ordered data array.
            $property_values = $updated_property_values;
            unset($updated_property_values);
        }

        foreach ($property_values as $property_name => $property_value) {
            // This method obeys the "error_handling_mode" parameter, hence "__set" instead of "setPropertyValue".
            $this->__set($property_name, $property_value);
        }
    }


    // Adds a new property and its value into an internal queue of properties to be set.

    public function addToSetQueue(string $property_name, mixed $property_value): void
    {

        $this->assertPropertyName($property_name);

        $this->set_queue[$property_name] = $property_value;
    }


    // Adds multiple properties and their values into an internal queue of properties to be set.

    public function addToSetQueueMass(array $property_data_array): void
    {

        foreach ($property_data_array as $property_name => $property_value) {
            $this->addToSetQueue($property_name, $property_value);
        }
    }


    // Flushes the internal queue of properties to be set.

    public function flushSetQueue(): void
    {

        $is_valid_callback_array = !empty($this->on_flush_set_queue_callbacks);

        if ($is_valid_callback_array) {
            $filtered_property_collection = new $this->property_collection();
        }

        foreach ($this->set_queue as $property_name => $property_value) {

            // The only callbacks that are required are "on flush set queue" callbacks.
            $this->throttleHooks(HookNamesEnum::BEFORE_SET_VALUE, $property_name);
            $this->throttleHooks(HookNamesEnum::AFTER_SET_VALUE, $property_name);
            $this->setPropertyValueInternal($property_name, $property_value);
            $this->unthrottleHooks(HookNamesEnum::BEFORE_SET_VALUE, $property_name);
            $this->unthrottleHooks(HookNamesEnum::AFTER_SET_VALUE, $property_name);

            if (isset($filtered_property_collection)) {
                $filtered_property_collection->add($this->getPropertyByName($property_name));
            }
        }

        // Fully clear queue.
        $this->set_queue = [];

        if ($is_valid_callback_array) {

            $this->fireOnFlushSetQueueCallbacks($filtered_property_collection);
        }
    }


    // Registers a callback that will be run once the internal queue of properties to be set is flushed.

    public function onFlushQueue(\Closure $callback): int
    {

        $this->on_flush_set_queue_callbacks[] = $callback;

        return array_key_last($this->on_flush_set_queue_callbacks);
    }


    // Gets all registered "on flush queue" hook callbacks.

    public function getOnFlushQueueCallbacks(): array
    {

        return $this->on_flush_set_queue_callbacks;
    }


    // Fires all registered "on flush set queue" hook callbacks.

    protected function fireOnFlushSetQueueCallbacks(AbstractPropertyCollection $property_collection): void
    {

        foreach ($this->on_flush_set_queue_callbacks as $callback) {

            $callback($property_collection, $this);
        }
    }


    // Checks if property exists in the collection, and if its value is set (similarly to how "isset" works with class properties).

    public function __isset(string $property_name): bool
    {

        // Verify that property is in the collection.
        try {
            $property = $this->getPropertyByName($property_name);
        } catch (PropertyNotFoundException) {
            return false;
        }

        // Check if property value is set.
        return $property->hasValue();
    }


    // An alias of "unsetValue" method.

    public function __unset(string $property_name): void
    {

        $this->unsetValue($property_name);
    }


    // Unsets value for the given property.

    public function unsetValue(string $property_name): void
    {

        $this->getPropertyByName($property_name)->unsetValue();
    }


    // Unsets values for all properties in this model.

    public function unsetAllValues(): void
    {

        foreach ($this->property_collection as $property) {
            $property->unsetValue();
        }
    }


    // An alias of the "getPropertyValue" method.

    public function __get(string $property_name): mixed
    {

        return $this->getPropertyValue($property_name);
    }


    // Gets given property's value.

    public function getPropertyValue(string $property_name): mixed
    {

        return $this->fireOnAfterGetValueCallbacks(
            $this->getPropertyByName($property_name)->getValue(),
            [
                $property_name,
            ],
            $property_name
        );
    }


    // Gets the base unscrambled value that was set.
    /* No "get" callbacks will be fired. Will receive the base value from the property as well. */

    public function getBaseValue(string $property_name): mixed
    {

        return $this->getPropertyByName($property_name)->getBaseValue();
    }


    // Gathers all values into an a single one-dimentional array and returns it.

    public function getValues(?array $filter_names = null): array
    {

        $result = [];

        foreach ($this->property_collection as $property_name => $property) {

            if (!$filter_names || in_array($property_name, $filter_names)) {

                try {
                    $result[$property_name] = $this->__get($property_name);
                } catch (ReadOnlyException|PropertyValueNotAvailableException) {
                    // Pass through
                }
            }
        }

        return $result;
    }


    //

    public function getValuesIterator(?array $filter_names = null): ModelValuesIterator
    {

        return new ModelValuesIterator($this, $filter_names);
    }


    // Gathers all default values into a single array and returns it.

    public function getDefaultValues(): array
    {

        $result = [];

        foreach ($this->property_collection as $property_name => $property) {

            $property = $this->getPropertyByName($property_name);

            if ($property->hasDefaultValue()) {

                // Closures will be resolved inside.
                $result[$property_name] = $property->getDefaultValue();
            }
        }

        return $result;
    }


    // Assigns a callback that will track changes for a given property.

    public function assignTrackingCallbackToProperty(BaseProperty $property): void
    {

        $property->onAfterSetValue(
            function (
                mixed $property_value,
                BaseProperty $property
            ): mixed {

                if (method_exists($property, 'getModel')) {

                    $active_model = $property->getModel();

                    if ($active_model->isTrackingChanges() === true) {
                        $active_model->tracking_changes[$property->property_name] = $property_value;
                    }

                } elseif ($this->is_tracking_changes === true) {

                    $this->tracking_changes[$property->property_name] = $property_value;
                }

                return $property_value;
            },
            identifier: 'track_changes'
        );
    }


    // Starts a change tracking session.

    public function startTrackingChanges(): ?bool
    {

        if ($this->is_tracking_changes !== false) {
            return null;
        }

        $this->is_tracking_changes = true;

        return true;
    }


    // Ends the change tracking session.

    public function stopTrackingChanges(): ?array
    {

        if ($this->is_tracking_changes === false) {
            return null;
        }

        $changed_properties = $this->tracking_changes;
        $this->tracking_changes = [];
        $this->is_tracking_changes = false;

        return $changed_properties;
    }


    // Tells if value changes are being tracked, eg. tracking session is running.

    public function isTrackingChanges(): bool
    {

        return $this->is_tracking_changes;
    }


    // Returns data storage which contains properties changed during the running tracking session.

    public function getChangedPropertyDataFromTracking(): array
    {

        return $this->tracking_changes;
    }


    // Transforms to condition group

    public function toConditionGroup(): ConditionGroup
    {

        return ConditionGroup::fromArray($this->getValues());
    }


    // Creates a new collection object from a given definition collection set.

    public static function fromDefinitionCollectionSet(
        DefinitionCollectionSet $definition_collection_set,
        array $params = []
    ): static {

        $static_instance = new static(...$params);

        foreach ($definition_collection_set as $property_name => $definition_collection) {

            try {
                $static_instance->addProperty(
                    static::createPropertyFromDefinitionCollection($property_name, $definition_collection, $static_instance)
                );
                // Eg. "group" type is not supported in "BaseProperty" as well as "EnhancedProperty".
            } catch (UnsupportedDefinitionCollectionException) {
                // Continue.
            }
        }

        self::$definition_collection_set_created_from = $definition_collection_set;

        return $static_instance;
    }


    // Gets the definition collection set that this collection was created from.

    public static function getDefinitionCollectionSetLastCreatedFrom(): ?DefinitionCollectionSet
    {

        return (self::$definition_collection_set_created_from ?? null);
    }
}

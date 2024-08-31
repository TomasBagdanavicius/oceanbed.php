<?php

declare(strict_types=1);

namespace LWP\Components\Model;

use LWP\Common\Common;
use LWP\Common\Enums\ValidityEnum;
use LWP\Components\Properties\BaseProperty;
use LWP\Components\Properties\RelationalProperty;
use LWP\Components\Properties\RelationalPropertyCollection;
use LWP\Common\Exceptions\DuplicateException;
use LWP\Common\Exceptions\InvalidStateException;
use LWP\Components\Definitions\DefinitionCollection;
use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Model\SharedAmounts\SharedAmountCollection;
use LWP\Components\Properties\Exceptions\PropertyStateException;
use LWP\Components\DataTypes\ValueOriginEnum;
use LWP\Components\Properties\Exceptions\PropertyDependencyException;
use LWP\Common\Exceptions\NotFoundException;

class RelationalPropertyModel extends EnhancedPropertyModel
{
    // An associative array containing assigned shared amount groups.
    // [group name] => shared amount group object
    protected array $shared_amount_groups = [];
    protected array $on_set_shared_amount_group_callbacks = [];
    // A multidimensional array that is a map of all property states.
    // [validity state][property name] => property value
    protected array $property_state_map = [];
    // Stores property whose value is currently being set.
    protected ?RelationalProperty $currently_setting = null;
    protected array $parked_value_options = [];


    /* Passing in a property collection is not supported, because relational
    properties are dependant upon this model. */
    public function __construct()
    {

        parent::__construct(
            new RelationalPropertyCollection()
        );
    }


    // Runs procedures on model clone.
    /* As per model cloning policy. */

    public function __clone(): void
    {

        parent::__clone();

        foreach ($this->shared_amount_groups as $index => $shared_amount_group) {
            $this->shared_amount_groups[$index] = clone $shared_amount_group;
        }

        foreach ($this->property_collection as $relational_property) {
            // Update model to the new one, which is this.
            $relational_property->setModel($this);
        }
    }


    // Adds a new relational property into the model.

    public function addProperty(BaseProperty $property): null|int|string
    {

        if (!($property instanceof RelationalProperty)) {
            Common::throwTypeError(1, __FUNCTION__, RelationalProperty::class, $property::class);
        }

        if ($property->getModel() !== $this) {
            throw new \RuntimeException(
                "Failed to add relational property to relational model: property's model must match the model it's being added to.",
            );
        }

        $property_index = parent::addProperty($property);

        $this->mapPropertyState(
            $property->property_name,
            $property->getState()
        );

        $property->onStateChange(
            $this->mapPropertyState(...)
        );

        return $property_index;
    }


    // Gets property whose value is currently being set.

    public function getCurrentlySetting(): ?RelationalProperty
    {

        return $this->currently_setting;
    }


    // Sets property value using params that can be accessed publicly.

    public function setPropertyValue(
        string $property_name,
        mixed $property_value,
        ValueOriginEnum $value_origin = ValueOriginEnum::MAIN
    ): mixed {

        $options = [];
        $occupied_set_access = false;

        // Parked value is being released.
        if (
            $value_origin === ValueOriginEnum::PARKED
            && isset($this->parked_value_options[$property_name])
        ) {

            [
                'options' => $options,
                'violation_id' => $violation_id
            ] = $this->parked_value_options[$property_name];

            // Get rid of the dependency violation.
            $this->getPropertyByName($property_name)
                ->unsetViolation($violation_id);

            unset($this->parked_value_options[$property_name]);

            // Simulate adding to the set access control stack.
            if (
                isset($options['set_access'])
                && $options['set_access'] === false
            ) {

                $this->occupySetAccessControlStack();
                $occupied_set_access = true;
            }
        }

        $accepted_value = static::setPropertyValueInternal(
            $property_name,
            $property_value,
            $value_origin,
            $options
        );

        if ($occupied_set_access) {
            $this->deoccupySetAccessControlStack();
        }

        return $accepted_value;
    }


    // Attempts to set property value using params that can be accessed internally only.

    protected function setPropertyValueInternal(
        string $property_name,
        mixed $property_value,
        ValueOriginEnum $value_origin = ValueOriginEnum::MAIN,
        array $options = []
    ): mixed {

        $property = $this->getPropertyByName($property_name);

        if ($property->isInInvalidState()) {
            throw new PropertyStateException(
                "Failed to set property ($property->property_name) value: property is in invalid state."
            );
        }

        try {

            // Check if there are any unsolved dependencies.
            $property->attemptValue($property_value);

        } catch (PropertyDependencyException $exception) {

            if ($property->hasParkedValue()) {

                $options = [];
                $this->adjustSetPropertyValueInternalOptions($options);

                $this->parked_value_options[$property_name] = [
                    'options' => $options,
                    'violation_id' => spl_object_id($exception->violation),
                ];
            }

            throw $exception;
        }

        $this->currently_setting = $property;
        $property_value = parent::setPropertyValueInternal($property_name, $property_value, $value_origin, $options);
        $this->currently_setting = null;

        /* Property's value has been fully set. All "after set value" hook
        callbacks have been run. Inform properties that are dependent on this,
        to attempt to solve the dependencies. */
        $property->checkDependentProperties();

        return $property_value;
    }


    // Gets value data for a given property.

    public function getValue(
        BaseProperty $property,
        bool $include_messages = false,
        bool $alt_values = false
    ): mixed {

        $value_result = parent::getValue($property, $include_messages, $alt_values);

        // Push in parked value.
        if ($alt_values && $property->hasParkedValue()) {
            $value_result['parked_value'] = $property->getParkedValue();
        }

        return $value_result;
    }


    // Refreshed property state map data by the given property and its current state.

    private function mapPropertyState(string $property_name, ?ValidityEnum $state): void
    {

        if ($state === ValidityEnum::VALID) {

            unset($this->property_state_map[ValidityEnum::INVALID->name][$property_name]);
            $this->property_state_map[ValidityEnum::VALID->name][$property_name] = $property_name;

        } elseif ($state === ValidityEnum::INVALID) {

            unset($this->property_state_map[ValidityEnum::VALID->name][$property_name]);
            $this->property_state_map[ValidityEnum::INVALID->name][$property_name] = $property_name;

            // Property removed.
        } else {

            unset(
                $this->property_state_map[ValidityEnum::VALID->name][$property_name],
                $this->property_state_map[ValidityEnum::INVALID->name][$property_name]
            );
        }
    }


    // Tells if model is in valid state.

    public function isInValidState(): bool
    {

        return empty($this->property_state_map[ValidityEnum::INVALID->name]);
    }


    // Tells if model is in invalid state.

    public function isInInvalidState(): bool
    {

        return !$this->isInValidState();
    }


    // Validates the state of the model.

    public function assertState(): void
    {

        if ($this->isInInvalidState()) {

            throw new InvalidStateException(sprintf(
                "Relational model (object ID %u) is in invalid state.",
                spl_object_id($this)
            ));
        }
    }


    // Assigns a given shared amount group.

    public function setSharedAmountGroup(SharedAmountCollection $shared_amount_group): void
    {

        $this->assertSharedAmountGroup($shared_amount_group->collection_name);

        $this->fireOnSetSharedAmountGroupCallbacks($shared_amount_group);

        $this->shared_amount_groups[$shared_amount_group->collection_name] = $shared_amount_group;
    }


    // Returns assigned shared amount groups.

    public function getSharedAmountGroups(): array
    {

        return $this->shared_amount_groups;
    }


    // Gives a count of all assigned shared amounts.

    public function getSharedAmountGroupCount(): int
    {

        return count($this->shared_amount_groups);
    }


    // Tells if a given shared amount group is assigned.

    public function isSharedAmountGroupSet(string $shared_amount_group_name): bool
    {

        return isset($this->shared_amount_groups[$shared_amount_group_name]);
    }


    // Validates if a given shared amount is assigned.

    public function assertSharedAmountGroup(string $shared_amount_group_name): void
    {

        if ($this->isSharedAmountGroupSet($shared_amount_group_name)) {
            throw new DuplicateException(
                "Shared amount with name \"$shared_amount_group_name\" is already registered."
            );
        }
    }


    // Returns assigned shared amount collection object by a given shared amount group name.

    public function getSharedAmountGroup(string $shared_amount_group_name): ?SharedAmountCollection
    {

        return ($this->shared_amount_groups[$shared_amount_group_name] ?? null);
    }


    // Removes an assigned shared amount group.

    public function unsetOnSetSharedAmountCallback(int $callback_identifier): void
    {

        unset($this->on_set_shared_amount_group_callbacks[$callback_identifier]);
    }


    // Sets up a hook that handles callbacks when a shared amount group is assigned.

    public function onSetSharedAmountGroup(\Closure $callback, bool $once = false): int
    {

        $this->on_set_shared_amount_group_callbacks[] = [
            'callback' => $callback,
            'once' => $once,
        ];

        return array_key_last($this->on_set_shared_amount_group_callbacks);
    }


    // Fires all "on set shared amount group" hook callbacks.

    protected function fireOnSetSharedAmountGroupCallbacks(SharedAmountCollection $shared_amount_group): void
    {

        if ($this->on_set_shared_amount_group_callbacks) {

            foreach ($this->on_set_shared_amount_group_callbacks as $index => $callback_data) {

                $callback_data['callback']($shared_amount_group, $this);

                if ($callback_data['once']) {
                    unset($this->on_set_shared_amount_group_callbacks[$index]);
                }
            }
        }
    }


    // Creates a new relational property object from a given definition collection.

    public static function createPropertyFromDefinitionCollection(
        string $property_name,
        DefinitionCollection $definition_collection,
        AbstractModel $model
    ): RelationalProperty {

        return RelationalProperty::fromDefinitionCollectionWithModel($model, $property_name, $definition_collection);
    }


    // Creates a new model instance object from a given definition collection set.

    public static function fromDefinitionCollectionSet(
        DefinitionCollectionSet $definition_collection_set,
        array $params = []
    ): static {

        $static_instance = new static(...$params);

        foreach ($definition_collection_set as $property_name => $definition_collection) {

            if (!$definition_collection->containsKey('type')) {
                throw new NotFoundException(sprintf(
                    "Property \"%s\" is missing the \"type\" definition in the definition collection",
                    $property_name
                ));
            }

            // Other than "group" type definition collection.
            if ($definition_collection->getTypeValue() !== 'group') {

                // Property will be auto added into the model.
                static::createPropertyFromDefinitionCollection($property_name, $definition_collection, $static_instance);

                // "group" type definition collection.
            } else {

                if (!$static_instance->isSharedAmountGroupSet($property_name)) {

                    $static_instance->setSharedAmountGroup(
                        SharedAmountCollection::fromDefinitionCollection($property_name, $definition_collection)
                    );
                }
            }
        }

        AbstractModel::$definition_collection_set_created_from = $definition_collection_set;

        return $static_instance;
    }
}

<?php

declare(strict_types=1);

namespace LWP\Components\Properties;

use LWP\Components\Model\AbstractModel;
use LWP\Common\Exceptions\NotFoundException;
use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\DataTypes\DataTypeValueContainer;
use LWP\Components\Attributes\NoDefaultValueAttribute;
use LWP\Components\Properties\Exceptions\PropertyValueNotAvailableException;
use LWP\Common\Enums\AccessLevelsEnum;
use LWP\Components\Definitions\DefinitionCollection;
use LWP\Components\Model\SharedAmounts\SharedAmountCollection;
use LWP\Components\Model\SharedAmounts\Exceptions\SharedAmountOutOfBoundsException;
use LWP\Components\Properties\Exceptions\PropertyValueContainsErrorsException;
use LWP\Common\Enums\ValidityEnum;
use LWP\Common\Exceptions\ApplicationAbortException;
use LWP\Components\Model\SharedAmounts\RequiredCountSharedAmount;
use LWP\Components\DataTypes\ValueOriginEnum;
use LWP\Components\Properties\Exceptions\PropertyDependencyException;
use LWP\Components\Violations\DependencyViolation;
use LWP\Components\Properties\Enums\HookNamesEnum;
use LWP\Components\Properties\Exceptions\PropertyStateException;
use LWP\Components\Violations\Violation;
use LWP\Components\Model\SharedAmounts\Exceptions\SharedAmountIdentifierNotFoundException;
use LWP\Components\Properties\Exceptions\PropertyNotFoundException;

class RelationalProperty extends EnhancedProperty
{
    // Resolved value of the "required" state.
    protected null|bool|\WeakReference $relational_required;
    /* Saves the last used required group name. A weak reference of `$relational_required` will not store the name, just the reference to the shared group. */
    protected string $relational_required_group_name;
    // A list of assigned shared amount group names.
    protected array $shared_amount_groups = [];
    protected ValidityEnum $state = ValidityEnum::VALID;
    protected array $on_state_change_callbacks = [];
    // A list of throwables that describe issues of the current invalid state.
    protected array $state_issues = [];
    // A list of property names that represent properties that this property is dependent upon.
    protected array $dependencies = [];
    // Properties from the `$dependencies` storage that don't yet have values.
    protected array $dependencies_cache = [];
    // A group of property names that represent properties that this property is instrumental to in dependency relationships.
    protected array $instrumental_to = [];
    // Properties from the `$instrumental_to` storage that don't yet have values.
    protected array $instrumental_to_cache = [];


    public function __construct(
        private RelationalPropertyModel $model,
        string $property_name,
        string $data_type_name,
        DataTypeValueContainer|\Closure|NoDefaultValueAttribute $default_value_obligation = new NoDefaultValueAttribute(),
        /* String is used when assigning a shared amount group name. Using individual class property name (not `$required` as in EnhancedProperty) to store an individual value, since it will be mangled when submitted into "EnhancedProperty". */
        null|bool|string|\Closure $relational_required = null,
        bool $readonly = false,
        ?string $description = null,
        ?string $title = null,
        bool $allow_empty = true,
        bool $nullable = false,
        protected AccessLevelsEnum $set_access = AccessLevelsEnum::PUBLIC,
        ?array $dependencies = null
    ) {

        $required_resolved = $this->setRelationalRequired($relational_required);
        $is_required = $this->isRequired();

        parent::__construct(
            $property_name,
            $data_type_name,
            $default_value_obligation,
            (($is_required === null || is_bool($is_required))
                ? $is_required
                : !!$is_required),
            $readonly,
            $description,
            $title,
            $allow_empty,
            $nullable,
            $set_access
        );

        // Property is added automatically, because it's interoperable.
        $model->addProperty($this);

        if ($dependencies) {

            foreach ($dependencies as $dependency_property_name) {
                $this->setDependency($dependency_property_name);
            }
        }
    }


    // Gets the model object.
    /* Model property is updateable. */

    public function getModel(): RelationalPropertyModel
    {

        return $this->model;
    }


    // Updates relationship model.
    /* Primarily, to be able to change to a new model on model clone. */

    public function setModel(RelationalPropertyModel $model): void
    {

        $this->model = $model;

        if (
            isset($this->relational_required_group_name, $this->relational_required)
            && ($this->relational_required instanceof \WeakReference)
        ) {

            $this->relational_required = \WeakReference::create(
                $this->model->getSharedAmountGroup($this->relational_required_group_name)->get('required_count')
            );
        }
    }


    // Resolves the relational property specific required state.

    public function setRelationalRequired(null|bool|string|\Closure $required): void
    {

        // Required state was defined explicitly as a boolean.
        if ($required === null || is_bool($required)) {

            $this->relational_required = $required;

            // Unravel required state by running a closure.
        } elseif ($required instanceof \Closure) {

            try {

                $application_return_value = ($required)($this);

            } catch (\Throwable $exception) {

                throw new ApplicationAbortException(
                    "Closure used with required definition contains code errors.",
                    previous: $exception
                );
            }

            if (!is_bool($application_return_value)) {
                throw new \UnexpectedValueException(
                    "Closure used with required definition must return a value of boolean type."
                );
            }

            // Okay to resolve.
            $this->relational_required = $application_return_value;

            // Shared amount group name.
        } elseif (is_string($required)) {

            $shared_amount_group_name = $required;
            unset($required);

            // Save the last used required group name.
            $this->relational_required_group_name = $shared_amount_group_name;

            $closure_get_ref_to_required_count = function (
                SharedAmountCollection $shared_amount_group
            ) use (
                $shared_amount_group_name,
            ): \WeakReference {

                if (!$shared_amount_group->containsKey('required_count')) {

                    throw new \Exception(sprintf(
                        "Shared amount collection \"%s\" must contain %s shared amount.",
                        $shared_amount_group_name,
                        RequiredCountSharedAmount::class
                    ));
                }

                return \WeakReference::create(
                    $shared_amount_group->get('required_count')
                );
            };

            if (!$this->model->isSharedAmountGroupSet($shared_amount_group_name)) {

                $this->assignSharedAmountGroup($shared_amount_group_name);

                $state_issue = $this->registerStateIssue(
                    new NotFoundException("Shared amount $shared_amount_group_name was not found.")
                );

                // Hoisted variables.
                $callback_identifier;

                $callback_identifier = $this->model->onSetSharedAmountGroup(
                    function (
                        SharedAmountCollection $shared_amount_group
                    ) use (
                        $shared_amount_group_name,
                        &$callback_identifier,
                        $closure_get_ref_to_required_count,
                        $state_issue,
                    ): void {

                        if ($shared_amount_group->collection_name === $shared_amount_group_name) {

                            $this->model->unsetOnSetSharedAmountCallback($callback_identifier);
                            $this->relational_required = ($closure_get_ref_to_required_count)($shared_amount_group);

                            $this->releaseStateIssue($state_issue);
                        }
                    }
                );

                // Shared amount group was found in model.
            } else {

                $shared_amount_collection = $this->model->getSharedAmountGroup($shared_amount_group_name);

                $this->relational_required = ($closure_get_ref_to_required_count)(
                    $shared_amount_collection
                );

                $this->assignSharedAmountGroup($shared_amount_collection);
            }
        }
    }


    // Determines what violation should be given in the late assessment stage of the required state.

    public function getRequiredLateIssue(): ?Violation
    {

        if ($this->relational_required instanceof \WeakReference) {

            $required_count_shared_amount = $this->relational_required->get();

            // Check if it's in invalid state.
            if (!$required_count_shared_amount->isInExplicitValidState()) {
                return $required_count_shared_amount->getLastViolation();
            }
        }

        return parent::getRequiredLateIssue();
    }


    // Tells if this property is required.

    public function isRequired(): ?bool
    {

        // Undetermined.
        if (!isset($this->relational_required)) {

            return null;

            // Reference to "required count" shared amount.
        } elseif ($this->relational_required instanceof \WeakReference) {

            $required_count_shared_amount = $this->relational_required->get();

            // Check if it's in invalid state.
            if (!$required_count_shared_amount->isInExplicitValidState()) {

                $violation = $required_count_shared_amount->getLastViolation();

                // Violation is telling that it's not required.
                if ($violation->getConstraintValue() === false) {
                    return false;
                }

                return null;
            }

            if (
                // The one that received a value - can change or unset its value.
                $this->hasValue()
                // Any member in group can receive a value.
                || $required_count_shared_amount->max_count === RequiredCountSharedAmount::AT_LEAST_ONE
            ) {
                return null;
            } else {
                return false;
            }

            // Determined explicitly.
        } else {

            return $this->relational_required;
        }
    }


    // Sets the "required" state.

    public function setRequired(?bool $required_state, bool $release_parked_value = true): void
    {

        $this->relational_required = $required_state;

        parent::setRequired($required_state, $release_parked_value);
    }


    // Returns an associative array of supported relations.

    public static function getSupportedRelations(): array
    {

        return [
            'alias' => 'LWP\Components\Properties\Relations\AliasRelation',
            'match' => 'LWP\Components\Properties\Relations\MatchRelation',
            'mismatch' => 'LWP\Components\Properties\Relations\MismatchRelation',
            'join' => 'LWP\Components\Properties\Relations\JoinRelation',
        ];
    }


    // Returns an associative array that maps relation names to definition names.

    public static function mapRelationNamesToDefinitionNames(): array
    {

        return [
            // "relation name => definition name"
            'alias' => 'alias',
            'match' => 'match',
            'mismatch' => 'mismatch',
            'join' => 'join',
        ];
    }


    // Sets up a new given relation between this property and given properties.

    public function setupRelation(string $relation_name, string|array $property_names, array $options = []): void
    {

        $supported_relations = self::getSupportedRelations();

        if (!isset($supported_relations[$relation_name])) {
            throw new NotFoundException(
                sprintf(
                    "Unrecognized relation \"%s\"",
                    $relation_name
                )
            );
        }

        $property_names = (array)$property_names;
        $relation_class_name = $supported_relations[$relation_name];

        $build_relation_object_closure = (static function (
            $primary_property,
            $relational_property_collection,
        ) use (
            $relation_class_name,
            $options,
        ): void {

            $params = [
                $primary_property
            ];

            if ($relational_property_collection->count() === 1) {
                $params[] = $relational_property_collection->getFirst();
            } else {
                $params[] = $relational_property_collection;
            }

            $params['options'] = $options;

            // In case setting dependency values fail.
            try {
                $relation = new ($relation_class_name)(...$params);
            } catch (PropertyValueContainsErrorsException) {
                // Continue.
            }

        });

        $relational_property_collection = new RelationalPropertyCollection();

        $this->model->onClone(function (
            RelationalPropertyModel $new_model
        ) use (
            $relational_property_collection,
            $build_relation_object_closure,
            $relation_class_name,
            $property_names,
        ): void {

            // In case property was removed from the new model
            if ($new_model->getPropertyCollection()->containsKey($this->property_name)) {

                $new_primary_property = $new_model->getPropertyByName($this->property_name);
                $relation_class_name::removeAssociatedPrimaryPropertyCallbacks($new_primary_property);
                $new_related_property_collection = $new_model->createNewPropertyCollectionBasedOn($relational_property_collection);

                foreach ($new_related_property_collection as $property) {
                    $relation_class_name::removeAssociatedRelatedPropertyCallbacks($property, $this);
                }

                /* Checks if the order of given properties matches the order in collection. Important is such relations as "join". */
                if ($property_names !== $new_related_property_collection->getKeys()) {
                    // Will rebuild collection by the order of given keys
                    $new_related_property_collection = $new_related_property_collection->filterByKeys($property_names);
                }

                $build_relation_object_closure($new_primary_property, $new_related_property_collection);
            }
        });

        $pending_property_list = [];

        foreach ($property_names as $property_name) {

            if (!$this->model->getPropertyCollection()->containsKey($property_name)) {
                $pending_property_list[$property_name] = $property_name;
            } else {
                $relational_property_collection->add($this->model->getPropertyByName($property_name));
            }
        }

        if (!$pending_property_list) {

            $build_relation_object_closure($this, $relational_property_collection);

        } else {

            $this->model->onAfterPropertyAdd(
                function (
                    self $property,
                    RelationalPropertyModel $model
                ) use (
                    $build_relation_object_closure,
                    &$pending_property_list,
                    $relational_property_collection,
                    $property_names,
                ): void {

                    if (isset($pending_property_list[$property->property_name])) {

                        $relational_property_collection->add($property);

                        unset($pending_property_list[$property->property_name]);

                        if (!$pending_property_list) {

                            /* Checks if the order of given properties matches the order in collection. Important is such relations as "join". */
                            if ($property_names !== $relational_property_collection->getKeys()) {
                                // Will rebuild collection by the order of given keys
                                $relational_property_collection = $relational_property_collection->filterByKeys($property_names);
                            }

                            $build_relation_object_closure($this, $relational_property_collection);
                        }
                    }
                }
            );
        }
    }


    // Returns a generator that yields assigned shared amount groups (as shared amount collection objects).

    public function yieldSharedAmountGroups(): ?\Generator
    {

        if (!$this->hasSharedAmountGroups()) {
            return null;
        }

        foreach ($this->shared_amount_groups as $shared_amount_group_name) {

            $shared_amount_group = $this->model->getSharedAmountGroup($shared_amount_group_name);

            foreach ($shared_amount_group as $shared_amount) {
                yield $shared_amount;
            }
        }
    }


    // Supplements all assigned shared amount groups with the given value.

    protected function addToSharedAmounts(mixed $value): void
    {

        $shared_amounts_generator = $this->yieldSharedAmountGroups();

        if ($shared_amounts_generator) {

            foreach ($shared_amounts_generator as $shared_amount) {

                try {

                    /* Property name is the identifier, which makes sure that when value for a property is updated. */
                    $shared_amount->add(
                        $value,
                        identifier: $this->property_name
                    );

                } catch (SharedAmountOutOfBoundsException $exception) {

                    $this->setViolation(
                        $shared_amount->getLastViolation(),
                        new PropertyStateException(
                            $exception->getMessage(),
                            previous: $exception
                        )
                    );
                }
            }
        }
    }


    // Depletes all assigned shared amount groups.

    protected function removeFromSharedAmounts(): void
    {

        if ($shared_amounts_generator = $this->yieldSharedAmountGroups()) {

            foreach ($shared_amounts_generator as $shared_amount) {

                try {

                    /* Property name is the identifier, which makes sure that when value for a property is updated. */
                    $shared_amount->remove(
                        identifier: $this->property_name
                    );

                    // SharedAmountIdentifierNotFoundException - eg. when shared amount was not added due to null value
                } catch (SharedAmountOutOfBoundsException|SharedAmountIdentifierNotFoundException) {

                    // Continue.
                }
            }
        }
    }


    // Checks if there are any unsolved dependencies.

    public function attemptValue(mixed $property_value): mixed
    {

        // There are unsolved dependencies.
        if ($this->dependencies_cache) {

            $this->parked_value = $property_value;

            $error_message_string = sprintf(
                "Value for property \"%s\" cannot be set, because there are some dependencies (%s) that don't yet have values.",
                $this->property_name,
                implode(', ', $this->dependencies_cache)
            );

            $violation = new DependencyViolation($this->dependencies_cache, $this->property_name);
            $violation->setErrorMessageString($error_message_string);

            $this->setViolation(
                $violation,
                new PropertyDependencyException(
                    $error_message_string,
                    violation: $violation
                )
            );
        }

        return $property_value;
    }


    // Sets the value and, optionally, it's origin.

    public function setValue(
        mixed $value,
        ValueOriginEnum $value_origin = ValueOriginEnum::MAIN
    ): mixed {

        $currently_setting = $this->model->getCurrentlySetting();

        // It has looped back and, importantly, to prevent recursion, it needs to be redirected the other value handling tasks.
        if ($currently_setting && $currently_setting === $this) {
            return self::setValueInternal($value, value_origin: $value_origin);
        }

        // As per interoperability policy: walk the stack starting from model level.
        return $this->model->setPropertyValue(
            $this->property_name,
            $value,
            value_origin: $value_origin
        );
    }


    // Sets the value internally.

    protected function setValueInternal(
        mixed $value,
        array $options = [],
        ValueOriginEnum $value_origin = ValueOriginEnum::MAIN
    ): mixed {

        $value = parent::setValueInternal($value, $options, $value_origin);

        if ($value !== null) {
            $this->addToSharedAmounts($value);
        }

        return $value;
    }


    // Dissolves the value entity.

    public function unsetValue(bool $keep_default = false): void
    {

        $this->throttleHooks(HookNamesEnum::UNSET_VALUE);
        parent::unsetValue($keep_default);
        $this->unthrottleHooks(HookNamesEnum::UNSET_VALUE);

        $this->removeFromSharedAmounts();

        $this->fireOnUnsetValueCallbacks();
    }


    // Resolves the value.

    public function getValue(): mixed
    {

        try {

            $this->throttleHooks(HookNamesEnum::AFTER_GET_VALUE);
            $value = parent::getValue();
            $this->unthrottleHooks(HookNamesEnum::AFTER_GET_VALUE);

            // Populate $exception to other blocks below.
        } catch (PropertyValueNotAvailableException $exception) {

            // Save exception and continue.

        } catch (\Throwable $exception) {

            throw $exception;

        } finally {

            $this->fireOnAfterGetValueCallbacks($exception ?? $value);
        }

        if (!isset($value) && isset($exception)) {

            throw $exception;
        }

        return $value;
    }


    // Store given shared amount group name into the list of assigned shared amount groups.

    protected function storeSharedAmountGroup(string $shared_amount_group_name): ?int
    {

        if (!in_array($shared_amount_group_name, $this->shared_amount_groups)) {

            $this->shared_amount_groups[] = $shared_amount_group_name;

            return array_key_last($this->shared_amount_groups);

        } else {

            return null;
        }
    }


    // Assigns a given shared amount group.

    public function assignSharedAmountGroup(
        string|SharedAmountCollection $shared_amount_group_name,
        // Whether to defer assignment, when the given shared amount group is not yet found in model.
        bool $strict = false
    ): void {

        if (($shared_amount_group_name instanceof SharedAmountCollection)) {
            $shared_amount_group_name = $shared_amount_group_name->collection_name;
        }

        if (!$this->model->isSharedAmountGroupSet($shared_amount_group_name)) {

            if (!$strict) {

                $state_issue = $this->registerStateIssue(
                    new NotFoundException("Shared amount $shared_amount_group_name was not found.")
                );

                /* Hoisted variables */
                $callback_identifier;

                $callback_identifier = $this->model->onSetSharedAmountGroup(
                    function (
                        SharedAmountCollection $shared_amount_group
                    ) use (
                        $shared_amount_group_name,
                        $state_issue,
                        &$callback_identifier,
                    ): void {

                        if ($shared_amount_group->collection_name === $shared_amount_group_name) {

                            $this->storeSharedAmountGroup($shared_amount_group_name);
                            $this->releaseStateIssue($state_issue);

                            $this->model->unsetOnSetSharedAmountCallback($callback_identifier);
                        }
                    }
                );

            } else {

                throw new \RuntimeException(
                    "Cannot assign shared amount group \"$shared_amount_group_name\": group has not been registered in model."
                );
            }

        } else {

            $this->storeSharedAmountGroup($shared_amount_group_name);
        }
    }


    // Tells if it has any shared amount groups assigned.

    public function hasSharedAmountGroups(): bool
    {

        return !!count($this->shared_amount_groups);
    }


    // Adds a new state issue.

    protected function registerStateIssue(\Throwable $exception): int
    {

        $this->state_issues[] = $exception;
        $was_invalid = ($this->state === ValidityEnum::INVALID);
        $this->state = ValidityEnum::INVALID;

        if (!$was_invalid) {
            $this->fireOnStateChangeCallbacks($this->state);
        }

        return array_key_last($this->state_issues);
    }


    // Gets a list of all state issues (as throwable objects).

    public function getStateIssues(): array
    {

        return $this->state_issues;
    }


    // Release a state issue by a given state issue identifier.

    protected function releaseStateIssue(int $index): ?bool
    {

        if (isset($this->state_issues[$index])) {

            unset($this->state_issues[$index]);

            if (!$this->state_issues) {

                $this->state = ValidityEnum::VALID;
                $this->fireOnStateChangeCallbacks($this->state);
            }

            return true;

            // State issue with a given index was not found.
        } else {

            return null;
        }
    }


    // Gets the object representing the current state.

    public function getState(): ValidityEnum
    {

        return $this->state;
    }


    // Tells if property is currently in valid state.

    public function isInValidState(): bool
    {

        return empty($this->state_issues);
    }


    // Tells if property is currently in invalid state.

    public function isInInvalidState(): bool
    {

        return !$this->isInValidState();
    }


    // Registers a callback that will be fired on state change.

    public function onStateChange(\Closure $callback, bool $once = false): int
    {

        $this->on_state_change_callbacks[] = [
            'callback' => $callback,
            'once' => $once,
        ];

        return array_key_last($this->on_state_change_callbacks);
    }


    // Fires all "on state change" hook callbacks.

    private function fireOnStateChangeCallbacks(?ValidityEnum $state): void
    {

        if ($this->on_state_change_callbacks) {

            foreach ($this->on_state_change_callbacks as $index => $callback_data) {

                $callback_data['callback']($this->property_name, $state);

                if ($callback_data['once']) {
                    unset($this->on_state_change_callbacks[$index]);
                }
            }
        }
    }


    // Sets a new dependency link to the given property.

    public function setDependency(string $property_name): ?bool
    {

        // Can't reference self in the dependency list.
        if ($property_name === $this->property_name) {

            throw new \LogicException(
                "Property \"$property_name\" cannot be self-referenced in the dependency list."
            );
        }

        if ($this->model->propertyExists($property_name)) {

            if (!$this->hasDependency($property_name)) {

                $this->dependencies[] = $property_name;
                $this->setupInstrumentalProperty($property_name);

                return true;

                // Dependency already exists.
            } else {

                return null;
            }

            /* There is no property with the given property name. The policy is to try and wait for the property to be registered. */
        } else {

            $state_issue_index = $this->registerStateIssue(
                new NotFoundException(
                    "Dependency property \"$property_name\" was not found."
                )
            );

            // Hoisted variables.
            $callback_identifier;

            $callback_identifier = $this->model->onAfterPropertyAdd(
                function (
                    self $property,
                    RelationalPropertyModel $model
                ) use (
                    $property_name,
                    $state_issue_index,
                    &$callback_identifier,
                ): void {

                    if ($property->property_name === $property_name) {

                        $this->dependencies[] = $property->property_name;
                        $this->setupInstrumentalProperty($property->property_name);

                        $this->releaseStateIssue($state_issue_index);
                        $this->model->unsetOnAfterSetValueCallback($callback_identifier);
                    }
                }
            );

            return false;
        }
    }


    // Sets up property that will be instrumental to this property.

    public function setupInstrumentalProperty(string $instrumental_property_name): void
    {

        $instrumental_property = $this->model->getPropertyByName($instrumental_property_name);
        $instrumental_property->setInstrumentalTo($this->property_name);

        if (!$instrumental_property->hasValue()) {
            $this->dependencies_cache[$instrumental_property_name] = $instrumental_property_name;
        }
    }


    // Make this property instrumental to the given property.

    private function setInstrumentalTo(string $dependent_property_name): ?true
    {

        if ($this->isInstrumentalTo($dependent_property_name)) {
            return null;
        }

        $this->model->assertPropertyName($dependent_property_name);

        $dependent_property = $this->model->getPropertyByName($dependent_property_name);

        // Dependency should have already been set up.
        if (!$dependent_property->hasDependency($this->property_name)) {
            throw new \LogicException(
                "Property $this->property_name cannot be instrumental to $dependent_property_name, because the latter doesn't have the former in its dependencies."
            );
        }

        $this->instrumental_to[] = $dependent_property_name;

        if (!$this->hasValue()) {
            $this->instrumental_to_cache[$dependent_property_name] = $dependent_property_name;
        }

        return true;
    }


    // Instructs all dependent properties to check whether dependency links with them can be solved.

    public function checkDependentProperties(): ?bool
    {

        // This property is not instrumental to any property at all.
        if (!$this->instrumental_to) {
            return null;
        }

        foreach ($this->instrumental_to as $dependent_property_name) {

            $dependent_property = $this->model->getPropertyByName($dependent_property_name);
            $dependent_property->attemptToSolveDependency($this->property_name);
        }

        return true;
    }


    // Checks if dependency to the given instrumental property has been solved.

    public function attemptToSolveDependency(string $instrumental_property_name): ?bool
    {

        // Dependency does not exist.
        if (!$this->hasDependency($instrumental_property_name)) {
            return null;
        }

        $instrumental_property = $this->model->getPropertyByName($instrumental_property_name);

        if (!$instrumental_property->hasValue()) {

            throw new \RuntimeException(
                sprintf(
                    "Instrumental property \"%s\" does not yet have a value.",
                    $instrumental_property_name
                )
            );
        }

        // Dependency solved.
        unset($this->dependencies_cache[$instrumental_property_name]);

        // Check if there are any other unsolved dependencies and if it's good to release the parked value.
        if (!$this->dependencies_cache && $this->hasParkedValue()) {

            $parked_value = $this->parked_value;
            unset($this->parked_value);

            $this->model->setPropertyValue(
                $this->property_name,
                $parked_value,
                ValueOriginEnum::PARKED
            );
        }

        return true;
    }


    // Checks whether the given property is a dependency of this property.

    public function hasDependency(string $property_name): bool
    {

        return in_array($property_name, $this->dependencies);
    }


    // Checks whether this property is instrumental to the given property.

    public function isInstrumentalTo(string $property_name): bool
    {

        return in_array($property_name, $this->instrumental_to);
    }


    // Gets the dependency list.

    public function getDependencyList(): array
    {

        return $this->dependencies;
    }


    // Gets property list to whom dependencies haven't been solved yet.

    public function getDependencyCache(): array
    {

        return $this->dependencies_cache;
    }


    // Gets property list to whom this property is instrumental to in a dependency relationship.

    public function getInstrumentalToList(): array
    {

        return $this->instrumental_to;
    }


    // Gathers params from a definition collection that can be used to construct a new instance of this property.

    final public static function setupParamsFromDefinitionCollectionWithModel(
        RelationalPropertyModel $model,
        string $property_name,
        DefinitionCollection $definition_collection
    ): array {

        $params = parent::setupParamsFromDefinitionCollection($property_name, $definition_collection);

        // Will rename "required" param to "relational_required".
        // Param "required" can be nullable.
        if (array_key_exists('required', $params)) {

            $params['relational_required'] = $params['required'];
            unset($params['required']);
        }

        // Prepends model.
        array_unshift($params, $model);

        // Port over dependencies.
        if ($definition_collection->containsKey('dependencies')) {
            $params['dependencies'] = (array)$definition_collection->get('dependencies')->getValue();
        }

        return $params;
    }


    // Imports relations from a given definition collection into a given property.

    public static function setRelationsFromDefinitionCollection(
        DefinitionCollection $definition_collection,
        self &$relational_property
    ): void {

        $relation_name_map = self::mapRelationNamesToDefinitionNames();

        foreach ($relation_name_map as $relation_name => $definition_name) {

            if ($definition_collection->containsKey($definition_name)) {

                $definition_value = $definition_collection->get($definition_name)->getValue();

                $relational_property->setupRelation(
                    $relation_name,
                    ($definition_value['properties'] ?? $definition_value),
                    ($definition_value['options'] ?? []),
                );
            }
        }
    }


    // Creates a new instance from a given definition collection.

    final public static function fromDefinitionCollectionWithModel(
        RelationalPropertyModel $model,
        string $property_name,
        DefinitionCollection $definition_collection
    ): self {

        $params = self::setupParamsFromDefinitionCollectionWithModel($model, $property_name, $definition_collection);

        $relational_property = new self(...$params);

        // Set constraints and formatting rules. Variable "relational_property" is modified by reference.
        parent::setConstraintsFromDefinitionCollection($definition_collection, $relational_property);
        parent::setFormattingRulesFromDefinitionCollection($definition_collection, $relational_property);
        // Set relations.
        self::setRelationsFromDefinitionCollection($definition_collection, $relational_property);

        /* Setup Shared Amounts */

        // Shared amount groups this property is associated with (either through "groups" or "required" definitions).
        $relevant_shared_amount_group_names
            = self::prepareSharedAmountGroupsFromDefinitionCollection($definition_collection, $model);

        if ($relevant_shared_amount_group_names) {

            foreach ($relevant_shared_amount_group_names as $relevant_shared_amount_group_name) {
                $relational_property->assignSharedAmountGroup($relevant_shared_amount_group_name);
            }
        }

        return $relational_property;
    }


    // Extracts shared amount group name list from a given definition collection.

    public static function makeSharedAmountGroupNameListFromDefinitionCollection(
        DefinitionCollection $definition_collection,
        bool $exclude_required = false
    ): array {

        // Gather shared groups property is participating in.
        $list = [];

        if ($definition_collection->containsKey('groups')) {

            $list = $definition_collection->get('groups')->getValue();
        }

        if (!$exclude_required && $definition_collection->containsKey('required')) {

            $required_value = $definition_collection->get('required')->getValue();

            if (is_string($required_value)) {
                $list[] = $required_value;
            }
        }

        return $list;
    }


    // Gathers a relevant list of shared amount groups and attempts to register them in the given associated model.

    public static function prepareSharedAmountGroupsFromDefinitionCollection(
        DefinitionCollection $definition_collection,
        RelationalPropertyModel $model
    ): array {

        $groups_found = [];

        $group_list = self::makeSharedAmountGroupNameListFromDefinitionCollection(
            $definition_collection,
            exclude_required: true
        );

        // Required param will be handled in "setupParamsFromDefinitionCollectionWithModel", hence exclude.
        if ($group_list) {

            foreach ($group_list as $group_name) {

                // Shared amount group is NOT yet registered in model.
                if (!$model->isSharedAmountGroupSet($group_name)) {

                    // If definition collection is available, attempt to find the group without having to defer it.
                    if ($definition_collection->parent->containsKey($group_name)) {

                        $definition_collection = $definition_collection->parent->get($group_name);

                        $model->setSharedAmountGroup(
                            SharedAmountCollection::fromDefinitionCollection($group_name, $definition_collection)
                        );
                    }
                }

                $groups_found[] = $group_name;
            }
        }

        return $groups_found;
    }
}

<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Components\Datasets\Relationships\Relationship;
use LWP\Components\Datasets\Relationships\RelationshipCollection;
use LWP\Components\Datasets\Relationships\Exceptions\RelationshipNotFoundException;

abstract class AbstractDatabase
{
    /* Collection and cache storage for relationships */
    protected RelationshipCollection $relationships;
    /* Collection and cache storage for containers from any dataset associated with this database */
    protected ContainerCollection $containers;


    public function __construct()
    {

        $this->containers = new ContainerCollection();
    }


    // Validates identifier
    /* Not static in order to allow classes extending this one to use `$this` (eg. in MySQL table's variant of "validateDatasetName" `$this` is used to fetch a list of reserved words). */

    public function validateIdentifier(string $identifier, int $char_limit = 30): true
    {

        if ($identifier === '') {
            throw new EmptyStringException("Identifier must not be empty");
        }

        if (!ctype_alnum(str_replace('_', '', $identifier))) {
            throw new \InvalidArgumentException("Identifier can contain only letters, digits, and underscore \"_\" characters");
        }

        if (!ctype_alpha($identifier[0])) {
            throw new \InvalidArgumentException("Identifier must start with a letter");
        }

        $identifier_length = strlen($identifier);

        if ($identifier_length > $char_limit) {
            throw new \LengthException(sprintf(
                "Identifier cannot have more than %d characters, got %d",
                $char_limit,
                $identifier_length
            ));
        }

        return true;
    }


    // Validates dataset name

    public function validateDatasetName(string $dataset_name, int $char_limit = 30): true
    {

        return $this->validateIdentifier($dataset_name, $char_limit);
    }


    // Sets up the relationship collection

    public function setupRelationships(): void
    {

        if (!isset($this->relationships)) {
            $this->relationships = new RelationshipCollection();
        }
    }


    // Registers a given collection

    public function setRelationship(Relationship $relationship): void
    {

        $this->setupRelationships();
        $this->relationships->add($relationship);
    }


    // Returns the number of relationships

    public function relationshipCount(): int
    {

        return (!isset($this->relationships))
            ? 0
            : $this->relationships->count();
    }


    // Returns collection of all relationships that have been registered

    public function relationships(): ?RelationshipCollection
    {

        return ($this->relationships ?? null);
    }


    // Fetches all relationships that are available in storage
    /* Optimized for implementations that have external storage for relationships. */

    public function allRelationships(): ?RelationshipCollection
    {

        return $this->relationships();
    }


    // Tells if given relationship exists

    public function hasRelationship(string $relationship_name): bool
    {

        if (!isset($this->relationships)) {
            return false;
        }

        return $this->relationships->containsKey($relationship_name);
    }


    //

    public function hasRelationshipById(int $id): bool
    {

        if (!isset($this->relationships)) {
            return false;
        }

        $filtered_collection = $this->relationships->matchBySingleCondition('id', $id);

        return ($filtered_collection->count() !== 0);
    }


    //

    protected function validateFieldName(string $name): void
    {

        if ($name !== 'id' && $name !== 'name') {
            throw new \ValueError("Filter field must be either \"name\" or \"id\"");
        }
    }


    //

    public function hasRelationshipBy(int|string $identifier, string $field = 'name'): bool
    {

        $this->validateFieldName($field);

        return ($field === 'name')
            // Self is used on purpose
            ? self::hasRelationship($identifier)
            : self::hasRelationshipById($identifier);
    }


    //
    /* Returns "true" when all relationships are found, otherwise an array of relationship names that were not found. */

    public function hasRelationships(array $relationship_names): true|array
    {

        if (!isset($this->relationships)) {
            // All relationships are missing
            return $relationship_names;
        }

        $collection_names = $this->relationships->getKeys();
        $diff = array_diff($relationship_names, $collection_names);

        return ($diff ?: true);
    }


    //
    /* Returns "true" when all relationships are found, otherwise an array of relationship IDs that were not found. */

    public function hasRelationshipsById(array $relationship_ids): true|array
    {

        if (!isset($this->relationships)) {
            return $relationship_ids;
        }

        $collection_ids_and_names = $this->relationships->getIndexableArrayCollection()->matchKeyValues('id', $relationship_ids, group_as_unique: true);

        if (!$collection_ids_and_names) {
            return $relationship_ids;
        }

        $diff = array_diff($relationship_ids, array_keys($collection_ids_and_names));

        return ($diff ?: true);
    }


    //

    public function hasRelationshipsBy(array $identifiers, string $field = 'name'): true|array
    {

        $this->validateFieldName($field);

        return ($field === 'name')
            // Self is used on purpose
            ? self::hasRelationships($identifiers)
            : self::hasRelationshipsById($identifiers);
    }


    //

    public function getRelationship(string $relationship_name, bool $throw = false): ?Relationship
    {

        if (!isset($this->relationships) || !$this->relationships->containsKey($relationship_name)) {

            if (!$throw) {
                return null;
            } else {
                throw new RelationshipNotFoundException(sprintf(
                    "Relationship \"%s\" was not found",
                    $relationship_name
                ));
            }
        }

        return $this->relationships->get($relationship_name);
    }


    //

    public function getRelationshipById(int $id, bool $throw = false): ?Relationship
    {

        if (
            !isset($this->relationships)
            || !($filtered_collection = $this->relationships->matchBySingleCondition('id', $id))
            || $filtered_collection->count() === 0
        ) {

            if (!$throw) {
                return null;
            } else {
                throw new RelationshipNotFoundException(sprintf(
                    "Relationship with ID number \"%d\" was not found",
                    $id
                ));
            }
        }

        return $filtered_collection->getFirst();
    }


    //

    public function getRelationshipBy(int|string $identifier, string $field = 'name', bool $throw = false): ?Relationship
    {

        $this->validateFieldName($field);

        return ($field === 'name')
            // Self is used on purpose
            ? self::getRelationship($identifier, $throw)
            : self::getRelationshipById($identifier, $throw);
    }


    //

    public function getRelationshipsBy(array $identifiers, string $field = 'name', bool $throw = false): ?RelationshipCollection
    {

        $this->validateFieldName($field);

        if (!isset($this->relationships)) {
            return null;
        }

        $is_id = ($field === 'id');
        $relationship_names = (!$is_id)
            ? $identifiers
            : $this->relationships->getIndexableArrayCollection()->matchKeyValues($field, $identifiers, group_as_unique: true);

        if ($throw) {

            if (!$is_id) {
                $collection_keys = $this->relationships->getKeys();
                $diff = array_diff($relationship_names, $collection_keys);
            } else {
                $found_ids = array_keys($relationship_names);
                $diff = array_diff($identifiers, $found_ids);
            }

            if ($diff) {
                throw new RelationshipNotFoundException(sprintf(
                    "Some relationships were not found: %s",
                    ('"' . implode('", "', $diff) . '"')
                ));
            }
        }

        $filtered_collection = $this->relationships->filterByKeys($relationship_names);

        if ($filtered_collection->count() === 0) {
            return null;
        }

        return $filtered_collection;
    }


    //

    public function getRelationships(array $relationship_names, bool $throw = false): ?RelationshipCollection
    {

        return $this->getRelationshipsBy($relationship_names, 'name', $throw);
    }


    //

    public function getRelationshipsById(array $relationship_ids, bool $throw = false): ?RelationshipCollection
    {

        return $this->getRelationshipsBy($relationship_ids, 'id', $throw);
    }


    //

    public function isMember(DatasetInterface $dataset): bool
    {

        return ($dataset->database === $this);
    }


    //

    public function assertDatasetMembership(DatasetInterface $dataset): void
    {

        if (!$this->isMember($dataset)) {
            throw new \Exception(sprintf(
                "Dataset \"%s\" is not a rightful member of the database",
                $dataset->getDatasetName()
            ));
        }
    }


    // Makes a simple transaction and runs custom callback inside it

    public function makeTransaction(callable $callback): void
    {

        // The default behavior is to just run the callback
        $callback();
    }


    //

    public function findOrAddContainer(string $container_name, DatasetInterface $dataset): Container
    {

        $this->assertDatasetMembership($dataset);

        $condition_group = new ConditionGroup();
        $condition_group->add(new Condition('dataset_name', $dataset->getDatasetName()));
        $condition_group->add(new Condition('container_name', $container_name));
        $filtered_collection = $this->containers->matchConditionGroup($condition_group);

        if ($filtered_collection->count() !== 0) {

            // There can be only one container by given criteria
            return $filtered_collection->getFirst();

        } else {

            $container = new Container($container_name, $dataset);
            $this->containers->add($container);

            return $container;
        }
    }
}

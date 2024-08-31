<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\Relationships;

use LWP\Common\Array\IndexableArrayCollection;

class RelationshipNodeArrayStorage extends IndexableArrayCollection implements RelationshipNodeStorageInterface
{
    public function __construct(
        array $data,
    ) {

        parent::__construct($data);
    }


    //

    public function containsNode(Relationship $relationship, RelationshipNodeKey $relationship_node_key): bool
    {

        return true;
    }


    //

    public function addNode(Relationship $relationship, RelationshipNodeKey $relationship_node_key): int
    {

        return 1;
    }


    //

    public static function getRelationshipFieldName(): string
    {

        return 'relationship';
    }


    //

    public static function getRelationshipLengthFieldName(): string
    {

        return 'length';
    }


    //

    public static function getKeyContainerNameByPosition(int $position): string
    {

        return ('dataset' . $position . '_key');
    }


    //

    public static function getAnyContainerNameByPosition(int $position): string
    {

        return ('any_module' . $position);
    }
}

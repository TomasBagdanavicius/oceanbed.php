<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\Relationships;

interface RelationshipNodeStorageInterface
{
    //

    public function containsNode(Relationship $relationship, RelationshipNodeKey $relationship_node_key): bool;


    //

    public function addNode(Relationship $relationship, RelationshipNodeKey $relationship_node_key): int;


    //

    public static function getRelationshipFieldName(): string;


    //

    public static function getRelationshipLengthFieldName(): string;


    //

    public static function getKeyContainerNameByPosition(int $position): string;


    //

    public static function getAnyContainerNameByPosition(int $position): string;
}

<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Common\Exceptions\NotFoundException;
use LWP\Components\Model\EnhancedPropertyModel;
use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Common\Enums\CrudOperationEnum;
use LWP\Components\Datasets\Interfaces\DatasetManagementProcessInterface;

trait DatasetManagerTrait
{
    //

    public static function getActionTypeList(): array
    {

        return array_keys(self::getActionTypeDefinitionDataArrays());
    }


    //

    public static function getDefinitionDataArrayForActionType(string|CrudOperationEnum $action_type): array
    {

        if ($action_type instanceof CrudOperationEnum) {
            $action_type = $action_type->value;
        }

        $definitions_by_action_type = static::getActionTypeDefinitionDataArrays();

        if (!isset($definitions_by_action_type[$action_type])) {
            throw new NotFoundException(sprintf(
                "Action type \"%s\" was not found",
                $action_type
            ));
        }

        return $definitions_by_action_type[$action_type];
    }


    //

    public static function getDefinitionCollectionSetForActionType(string $action_type): DefinitionCollectionSet
    {

        $definition_data_array = self::getDefinitionDataArrayForActionType($action_type);

        return DefinitionCollectionSet::fromArray($definition_data_array);
    }


    //

    public static function getModelForActionType(string $action_type): EnhancedPropertyModel
    {

        return EnhancedPropertyModel::fromDefinitionCollectionSet(
            self::getDefinitionCollectionSetForActionType($action_type)
        );
    }
}

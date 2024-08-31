<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\Interfaces;

interface DatasetManagerInterface
{
    //

    public static function getActionTypeList(): array;

    //

    public static function getActionTypeDefinitionDataArrays(): array;

    //

    public static function getDefinitionDataArrayForActionType(string $action_type): array;
}

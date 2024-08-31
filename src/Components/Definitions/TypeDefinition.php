<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Components\DataTypes\DataTypeFactory;
use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;

class TypeDefinition extends Definition
{
    public const DEFINITION_NAME = 'type';
    public const DEFINITION_CATEGORY = DefinitionCategoryEnum::MAIN;
    public const IS_PRIMARY = true;


    public function __construct(
        string $value,
    ) {

        parent::__construct($value);
    }


    //

    public function getClassObjectClassName(): string
    {

        return DataTypeFactory::getDataTypeValueClassNameByTypeName($this->value);
    }


    //

    public function setValue(string $value): void
    {

        $this->value = $value;
    }
}

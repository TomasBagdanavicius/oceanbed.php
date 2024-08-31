<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;

class DescriptionDefinition extends Definition
{
    public const DEFINITION_NAME = 'description';
    public const DEFINITION_CATEGORY = DefinitionCategoryEnum::MISC;
    public const IS_PRIMARY = false;
    public const CAN_PRODUCE_CLASS_OBJECT = false;


    public function __construct(
        string $value,
    ) {

        parent::__construct($value);
    }


    //

    public function setValue(string $value): void
    {

        $this->value = $value;
    }
}

<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;

class DefaultDefinition extends Definition
{
    public const DEFINITION_NAME = 'default';
    public const DEFINITION_CATEGORY = DefinitionCategoryEnum::MAIN;
    public const IS_PRIMARY = false;
    public const CAN_PRODUCE_CLASS_OBJECT = false;


    public function __construct(
        mixed $value,
    ) {

        parent::__construct($value);
    }


    //

    public function setValue(mixed $value): void
    {

        $this->value = $value;
    }
}

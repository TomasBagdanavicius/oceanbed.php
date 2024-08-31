<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;

class NullableDefinition extends Definition
{
    public const DEFINITION_NAME = 'nullable';
    public const DEFINITION_CATEGORY = DefinitionCategoryEnum::MAIN;
    public const IS_PRIMARY = false;
    public const CAN_PRODUCE_CLASS_OBJECT = false;


    public function __construct(
        bool $value,
    ) {

        parent::__construct($value);
    }


    // Set accepted value type.

    public function setValue(bool $value): void
    {

        $this->value = $value;
    }
}

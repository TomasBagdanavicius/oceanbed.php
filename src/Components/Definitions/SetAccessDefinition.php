<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;

class SetAccessDefinition extends Definition
{
    public const DEFINITION_NAME = 'set_access';
    public const DEFINITION_CATEGORY = DefinitionCategoryEnum::CONSTRAINT;
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

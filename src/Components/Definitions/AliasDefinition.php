<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;

class AliasDefinition extends Definition
{
    public const DEFINITION_NAME = 'alias';
    public const DEFINITION_CATEGORY = DefinitionCategoryEnum::MODEL;
    public const IS_PRIMARY = true;
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

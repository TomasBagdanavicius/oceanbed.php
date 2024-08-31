<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;

class MismatchDefinition extends Definition
{
    public const DEFINITION_NAME = 'mismatch';
    public const DEFINITION_CATEGORY = DefinitionCategoryEnum::MODEL;
    public const IS_PRIMARY = false;
    public const CAN_PRODUCE_CLASS_OBJECT = false;


    public function __construct(
        string|array $value,
    ) {

        parent::__construct($value);
    }


    //

    public function setValue(string|array $value): void
    {

        $this->value = $value;
    }
}

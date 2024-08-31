<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;

class VirtualDefinition extends Definition
{
    public const DEFINITION_NAME = 'virtual';
    public const DEFINITION_CATEGORY = DefinitionCategoryEnum::DATASET;
    public const IS_PRIMARY = false;
    public const CAN_PRODUCE_CLASS_OBJECT = false;


    public function __construct(
        bool $value,
    ) {

        parent::__construct($value);
    }


    //

    public function setValue(bool $value): void
    {

        $this->value = $value;
    }
}

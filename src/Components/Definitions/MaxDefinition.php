<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Components\Constraints\MaxSizeConstraint;
use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;

class MaxDefinition extends Definition
{
    public const DEFINITION_NAME = 'max';
    public const DEFINITION_CATEGORY = DefinitionCategoryEnum::CONSTRAINT;
    public const IS_PRIMARY = false;
    public const CAN_PRODUCE_CLASS_OBJECT = true;


    public function __construct(
        int|float $value,
    ) {

        parent::__construct($value);
    }


    //

    public function setValue(int|float $value): void
    {

        $this->value = $value;
    }


    //

    public static function getClassObjectClassName(): string
    {

        return MaxSizeConstraint::class;
    }


    //

    public function produceClassObject(): MaxSizeConstraint
    {

        return new MaxSizeConstraint($this->value);
    }
}

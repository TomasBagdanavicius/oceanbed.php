<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Components\Constraints\MinSizeConstraint;
use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;

class MinDefinition extends Definition
{
    public const DEFINITION_NAME = 'min';
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

        return MinSizeConstraint::class;
    }


    //

    public function produceClassObject(): MinSizeConstraint
    {

        return new MinSizeConstraint($this->value);
    }
}

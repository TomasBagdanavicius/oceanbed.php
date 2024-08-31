<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Components\Constraints\CharsetConstraint;
use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;

class CharsetDefinition extends Definition
{
    public const DEFINITION_NAME = 'charset';
    public const DEFINITION_CATEGORY = DefinitionCategoryEnum::CONSTRAINT;
    public const IS_PRIMARY = false;
    public const CAN_PRODUCE_CLASS_OBJECT = true;


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


    //

    public static function getClassObjectClassName(): string
    {

        return CharsetConstraint::class;
    }


    //

    public function produceClassObject(): CharsetConstraint
    {

        return new CharsetConstraint($this->value);
    }
}

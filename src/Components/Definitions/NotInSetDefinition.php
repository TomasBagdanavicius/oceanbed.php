<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Components\Constraints\NotInSetConstraint;
use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;

class NotInSetDefinition extends Definition
{
    public const DEFINITION_NAME = 'not_in_set';
    public const DEFINITION_CATEGORY = DefinitionCategoryEnum::CONSTRAINT;
    public const IS_PRIMARY = true;
    public const CAN_PRODUCE_CLASS_OBJECT = true;


    public function __construct(
        array $value,
    ) {

        parent::__construct($value);
    }


    //

    public function setValue(array $value): void
    {

        $this->value = $value;
    }


    //

    public static function getClassObjectClassName(): string
    {

        return NotInSetConstraint::class;
    }


    //

    public function produceClassObject(): NotInSetConstraint
    {

        return new NotInSetConstraint($this->value);
    }
}

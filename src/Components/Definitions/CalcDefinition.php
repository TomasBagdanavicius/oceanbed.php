<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Components\Constraints\CharsetConstraint;
use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;
use LWP\Components\Rules\CalcFormattingRule;

class CalcDefinition extends Definition
{
    public const DEFINITION_NAME = 'calc';
    public const DEFINITION_CATEGORY = DefinitionCategoryEnum::FORMATTING;
    public const IS_PRIMARY = false;
    public const CAN_PRODUCE_CLASS_OBJECT = true;


    public function __construct(
        string $value
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

        return CalcFormattingRule::class;
    }


    //

    public function produceClassObject(): CalcFormattingRule
    {

        $class_name = self::getClassObjectClassName();

        return new $class_name([
            'subject' => $this->value
        ]);
    }
}

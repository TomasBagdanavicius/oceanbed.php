<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Components\Rules\NumberFormattingRule;
use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;

class NumberFormatDefinition extends Definition
{
    public const DEFINITION_NAME = 'number_format';
    public const DEFINITION_CATEGORY = DefinitionCategoryEnum::FORMATTING;
    public const IS_PRIMARY = false;
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

        return NumberFormattingRule::class;
    }


    //

    public function produceClassObject(): NumberFormattingRule
    {

        return new NumberFormattingRule($this->value);
    }
}

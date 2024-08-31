<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;
use LWP\Components\Rules\ConcatFormattingRule;

class JoinDefinition extends Definition
{
    public const DEFINITION_NAME = 'join';
    public const DEFINITION_CATEGORY = DefinitionCategoryEnum::FORMATTING;
    public const IS_PRIMARY = true;
    public const CAN_PRODUCE_CLASS_OBJECT = true;


    public function __construct(
        array $value
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

        return ConcatFormattingRule::class;
    }


    //

    public function produceClassObject(): ConcatFormattingRule
    {

        return new ConcatFormattingRule($this->value['options']);
    }
}

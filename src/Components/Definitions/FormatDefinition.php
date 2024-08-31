<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Components\Rules\DateTimeFormattingRule;
use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;

class FormatDefinition extends Definition
{
    public const DEFINITION_NAME = 'format';
    public const DEFINITION_CATEGORY = DefinitionCategoryEnum::FORMATTING;
    public const IS_PRIMARY = false;
    public const CAN_PRODUCE_CLASS_OBJECT = true;


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


    //

    public static function getClassObjectClassName(): string
    {

        return DateTimeFormattingRule::class;
    }


    //

    public function produceClassObject(): DateTimeFormattingRule
    {

        $class_name = self::getClassObjectClassName();

        return new $class_name([
            'format' => $this->value
        ]);
    }
}

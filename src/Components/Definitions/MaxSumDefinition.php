<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Components\Model\SharedAmounts\MaxSumSharedAmount;
use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;

class MaxSumDefinition extends Definition
{
    public const DEFINITION_NAME = 'max_sum';
    public const DEFINITION_CATEGORY = DefinitionCategoryEnum::SHARED_AMOUNT;
    public const IS_PRIMARY = false;
    public const CAN_PRODUCE_CLASS_OBJECT = true;


    public function __construct(
        int|float $value
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

        return MaxSumSharedAmount::class;
    }


    //

    public function produceClassObject(): MaxSumSharedAmount
    {

        $class_name = self::getClassObjectClassName();

        return new ($class_name)($this->value);
    }
}

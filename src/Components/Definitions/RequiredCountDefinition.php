<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Components\Model\SharedAmounts\RequiredCountSharedAmount;
use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;

class RequiredCountDefinition extends Definition
{
    public const DEFINITION_NAME = 'required_count';
    public const DEFINITION_CATEGORY = DefinitionCategoryEnum::SHARED_AMOUNT;
    public const IS_PRIMARY = false;
    public const CAN_PRODUCE_CLASS_OBJECT = true;


    public function __construct(
        int $value
    ) {

        parent::__construct($value);
    }


    //

    public function setValue(int $value): void
    {

        $this->value = $value;
    }


    //

    public static function getClassObjectClassName(): string
    {

        return RequiredCountSharedAmount::class;
    }


    //

    public function produceClassObject(): RequiredCountSharedAmount
    {

        return new RequiredCountSharedAmount($this->value);
    }
}

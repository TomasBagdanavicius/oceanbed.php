<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Components\Constraints\SizeRangeConstraint;
use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;

class RangeDefinition extends Definition
{
    public const DEFINITION_NAME = 'range';
    public const DEFINITION_CATEGORY = DefinitionCategoryEnum::CONSTRAINT;
    public const IS_PRIMARY = false;
    public const CAN_PRODUCE_CLASS_OBJECT = true;


    public function __construct(
        array $value,
    ) {

        parent::__construct(
            self::normalizeValue($value)
        );
    }


    //

    public static function normalizeValue(array $value): array
    {

        if (($count_elements = count($value)) !== 2) {
            throw new \ValueError(
                "Range value must contain exactly 2 elements, found $count_elements."
            );
        }

        return array_combine(['min', 'max'], $value);
    }


    //

    public function setValue(array $value): void
    {

        $this->value = self::normalizeValue($value);
    }


    //

    public static function getClassObjectClassName(): string
    {

        return SizeRangeConstraint::class;
    }


    //

    public function produceClassObject(): SizeRangeConstraint
    {

        return new SizeRangeConstraint(
            $this->value['min'],
            $this->value['max']
        );
    }
}

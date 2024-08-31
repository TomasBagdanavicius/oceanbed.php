<?php

declare(strict_types=1);

namespace LWP\Components\Constraints;

use LWP\Components\Definitions\CharsetDefinition;

class CharsetConstraint extends Constraint
{
    public const ACCEPTED_CHARSETS = [
        'any_char',
        'ascii'
    ];


    public function __construct(
        string $value
    ) {

        $value = strtolower($value);

        if (!in_array($value, self::ACCEPTED_CHARSETS)) {
            throw new \ValueError(sprintf(
                "Character set \"%s\" was not recognized",
                $value
            ));
        }

        parent::__construct($value);
    }


    // Gets the compact definition array.

    public function getDefinition(): array
    {

        return [
            CharsetDefinition::DEFINITION_NAME => $this->getValue()
        ];
    }
}

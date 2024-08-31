<?php

declare(strict_types=1);

namespace LWP\Components\Constraints;

use LWP\Common\Interfaces\Sizeable;

class SizeRangeConstraint extends Constraint
{
    public function __construct(
        private int|float|Sizeable $min_size,
        private int|float|Sizeable $max_size,
    ) {

        parent::__construct([
            'min' => $min_size,
            'max' => $max_size,
        ]);
    }


    // Gets the minimum allowed size.

    public function getMinSize(): int|float
    {

        return $this->min_size;
    }


    // Gets the maximum allowed size.

    public function getMaxSize(): int|float
    {

        return $this->max_size;
    }


    // Gets the compact definition array.

    public function getDefinition(): array
    {

        return [
            RangeDefinition::DEFINITION_NAME => $this->getValue(),
        ];
    }
}

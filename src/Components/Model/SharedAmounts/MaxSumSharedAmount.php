<?php

declare(strict_types=1);

namespace LWP\Components\Model\SharedAmounts;

use LWP\Common\Exceptions\Math\IsNotUnsignedIntegerException;

class MaxSumSharedAmount extends RangeCountSharedAmount
{
    public const REPRESENTED_DEFINITION_NAME = 'max_sum';


    public function __construct(
        int|float $max_sum
    ) {

        if ($max_sum < 0) {
            throw new IsNotUnsignedIntegerException(
                "Integer for maximum value must not be below 0, got $max_sum."
            );
        }

        parent::__construct(min_sum: null, max_sum: $max_sum);
    }


    //

    public function getAcceptedTypes(): array
    {

        return [
            'integer',
            'float',
            'number',
        ];
    }
}

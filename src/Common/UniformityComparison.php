<?php

declare(strict_types=1);

namespace LWP\Common;

class UniformityComparison
{
    public function __construct(
        protected int $primary_value,
    ) {

    }


    // Gets comparison result.

    public function getResult(int $compare_value): UniformityComparisonResult
    {

        if ($compare_value < $this->primary_value) {
            return new UniformityComparisonResult(UniformityComparisonResult::LOWER);
        } elseif ($compare_value == $this->primary_value) {
            return new UniformityComparisonResult(UniformityComparisonResult::EQUAL);
        } elseif ($compare_value > $this->primary_value) {
            return new UniformityComparisonResult(UniformityComparisonResult::HIGHER);
        }
    }
}

<?php

declare(strict_types=1);

namespace LWP\Common\String\VersionScheme;

use LWP\Common\UniformityComparisonResult;

class VersionSchemeUniformityComparison
{
    public function __construct(
        private string $primary_value,
    ) {

    }


    // Gets comparison result.

    public function getResult(string $compare_value): UniformityComparisonResult
    {

        $compare_value_parts = explode('.', $compare_value);
        $primary_value_parts = explode('.', $this->primary_value);

        foreach ($compare_value_parts as $key => $compare_value_part) {

            $primary_value_part = $primary_value_parts[$key];

            if ($compare_value_part == $primary_value_part) {
                continue;
            } elseif ($compare_value_part < $primary_value_part) {
                return new UniformityComparisonResult(UniformityComparisonResult::LOWER);
            } elseif ($compare_value_part > $primary_value_part) {
                return new UniformityComparisonResult(UniformityComparisonResult::HIGHER);
            }
        }

        return new UniformityComparisonResult(UniformityComparisonResult::EQUAL);
    }
}

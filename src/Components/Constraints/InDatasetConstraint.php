<?php

declare(strict_types=1);

namespace LWP\Components\Constraints;

use LWP\Common\Conditions\ConditionGroup;
use LWP\Components\Datasets\Interfaces\DatasetInterface;

class InDatasetConstraint extends Constraint
{
    public function __construct(
        public readonly DatasetInterface $dataset,
        public readonly string $container_name,
        public readonly ?ConditionGroup $condition_group = null
    ) {

        parent::__construct($dataset);
    }


    // Gets the compact definition array.

    public function getDefinition(): ?array
    {

        // Is not represented by a definition.
        return null;
    }
}

<?php

declare(strict_types=1);

namespace LWP\Components\Constraints;

class InSetConstraint extends Constraint
{
    public function __construct(
        private array $set,
        private $use_keys = false,
    ) {

        parent::__construct($set);
    }


    //

    public function isUseKeys(): bool
    {

        return $this->use_keys;
    }


    // Gets the given constraint set.

    public function getSet(): array
    {

        return (!$this->use_keys)
            ? $this->set
            : array_keys($this->set);
    }


    //

    public function getDefinition(): array
    {

        if (!$use_keys) {
            return [
                'in_set' => $this->value,
            ];
        } else {
            return [
                'in_set' => [
                    'set' => $this->value,
                    'use_keys' => $use_keys,
                ],
            ];
        }
    }
}

<?php

declare(strict_types=1);

namespace LWP\Components\Constraints;

class VariableTypeConstraint extends Constraint
{
    public function __construct(
        string|array $value,
    ) {

        $this->value = (array)$value;

        parent::__construct($this->value);
    }


    // Gets the compact definition array.

    public function getDefinition(): array
    {

        return [];
    }


    //

    public function getType(): array
    {

        return $this->value;
    }


    //

    public function getMainErrorMessageTemplate(): string
    {

        return (sprintf("Incorrect type \"%%s\": expecting \"%s\"", implode('", "', $this->value)) . ".");
    }
}

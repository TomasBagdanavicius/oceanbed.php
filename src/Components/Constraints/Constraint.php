<?php

declare(strict_types=1);

namespace LWP\Components\Constraints;

use LWP\Components\Constraints\Validators\ConstraintValidator;

abstract class Constraint
{
    public function __construct(
        protected mixed $value
    ) {

    }


    // Gets the compact definition array.

    abstract public function getDefinition(): ?array;


    // Gets the constraint value.

    public function getValue(): mixed
    {

        return $this->value;
    }


    // Gets the constraint validator instance.

    public function getValidator(): ConstraintValidator
    {

        $class_name = (
            __NAMESPACE__
            . '\Validators\\'
            . str_replace(__NAMESPACE__ . '\\', '', static::class)
            . 'Validator'
        );

        return new ($class_name)($this);
    }


    //
    /* Template function to check whether there are collisions with other associated constraints. This method normally should not be executed at runtime, but rather as a means to validate a group of constraints to make sure there are no collisions in relationship to each other. */

    public function collisionAssistance(self $associated_constraint): bool
    {

        return true;
    }
}

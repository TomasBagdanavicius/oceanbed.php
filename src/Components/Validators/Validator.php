<?php

declare(strict_types=1);

namespace LWP\Components\Validators;

abstract class Validator
{
    public function __construct(
        /* The idea is to leave this public for the consumer to amend and affectivelly change the value to be validated. */
        public mixed $value,
    ) {

    }


    //

    abstract public function validate(): bool;
}
